<?php
require_once '../includes/database.php';
require_once '../includes/image_watermark.php'; // <-- added

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$db = getDB();
$message = ''; $error = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
        $name          = $_POST['name']          ?? '';
        $description   = $_POST['description']   ?? '';
        $display_order = $_POST['display_order'] ?? 0;
        $status        = $_POST['status']        ?? 'active';
        
        if($name) {
            $slug = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9 ]/', '', $name)));
            
            // Handle image upload
            $imageName = '';

            if(isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $allowed  = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['image']['name'];
                $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if(in_array($ext, $allowed)) {
                    $imageName  = uniqid() . '.' . $ext;
                    $targetPath = CATEGORY_UPLOAD . $imageName;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                        // add SNA watermark to category image
                        //addSnaWatermark($targetPath);
                    }
                }
            }
            
            if($_POST['action'] === 'add') {
                // Check for duplicate slug
                $check = $db->prepare("SELECT id FROM categories WHERE slug = ?");
                $check->execute([$slug]);
                if($check->fetch()) {
                    $slug .= '-' . time();
                }
                
                $stmt = $db->prepare("
                    INSERT INTO categories (name, slug, description, image, display_order, status) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $slug, $description, $imageName, $display_order, $status]);
                $message = "Category added successfully!";
            } else {
                $id = $_POST['id'];

                // If editing and no new image uploaded, keep existing image
                if (!$imageName) {
                    $stmtImg = $db->prepare("SELECT image FROM categories WHERE id = ?");
                    $stmtImg->execute([$id]);
                    $existing = $stmtImg->fetch(PDO::FETCH_ASSOC);
                    $imageName = $existing['image'] ?? '';
                }

                $stmt = $db->prepare("
                    UPDATE categories 
                    SET name = ?, slug = ?, description = ?, image = ?, display_order = ?, status = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$name, $slug, $description, $imageName, $display_order, $status, $id]);
                $message = "Category updated successfully!";
            }
        } else {
            $error = "Category name is required";
        }
    }
    
    if($_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        $message = "Category deleted successfully!";
    }
}

// Get all categories
$categories = $db->query("SELECT * FROM categories ORDER BY display_order, id")->fetchAll(PDO::FETCH_ASSOC);

// Get category for editing
$editCategory = null;
if(isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editCategory = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand">Categories Management</span>
            <a href="index.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
        </div>
    </nav>
    
    <div class="container-fluid p-4">
        <?php if($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Add/Edit Form -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><?= $editCategory ? 'Edit Category' : 'Add New Category' ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?= $editCategory ? 'edit' : 'add' ?>">
                            <?php if($editCategory): ?>
                            <input type="hidden" name="id" value="<?= $editCategory['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Category Name *</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?= htmlspecialchars($editCategory['name'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($editCategory['description'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <?php if($editCategory && $editCategory['image']): ?>
                                <img src="uploads/categories/<?= $editCategory['image'] ?>" 
                                     class="img-thumbnail mt-2" width="100">
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" name="display_order" class="form-control" 
                                       value="<?= htmlspecialchars($editCategory['display_order'] ?? 0) ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active"   <?= ($editCategory['status'] ?? '') === 'active'   ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= ($editCategory['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <?= $editCategory ? 'Update' : 'Add' ?> Category
                            </button>
                            
                            <?php if($editCategory): ?>
                            <a href="categories.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Categories List -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>All Categories</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Image</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($categories as $cat): ?>
                                <tr>
                                    <td><?= $cat['id'] ?></td>
                                    <td><?= htmlspecialchars($cat['name']) ?></td>
                                    <td>
                                        <?php if($cat['image']): ?>
                                        <img src="uploads/categories/<?= $cat['image'] ?>" 
                                             class="img-thumbnail" width="50">
                                        <?php else: ?>
                                        <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $cat['display_order'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $cat['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($cat['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>