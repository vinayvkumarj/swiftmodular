<?php
require_once '../includes/database.php';
require_once '../includes/image_watermark.php'; // <-- added

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$db = getDB();
$message = '';
$error   = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
        $title      = $_POST['title']      ?? '';
        $subtitle   = $_POST['subtitle']   ?? '';
        $link       = $_POST['link']       ?? '';
        $sort_order = $_POST['sort_order'] ?? 0;
        $status     = $_POST['status']     ?? 'active';

        // Handle image upload
        $imageName = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed  = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $imageName = uniqid('slide_') . '.' . $ext;
                $targetPath = SLIDER_UPLOAD . $imageName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    // add watermark to slider image
                    //addSnaWatermark($targetPath);
                }
            } else {
                $error = "Invalid image format. Allowed: jpg, jpeg, png, gif";
            }
        }

        if (!$error) {
            if ($_POST['action'] === 'add') {

                if (!$imageName) {
                    $error = "Image is required for a new slide.";
                } else {
                    $stmt = $db->prepare("
                        INSERT INTO home_slides (title, subtitle, image, link, sort_order, status)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $title,
                        $subtitle,
                        $imageName,
                        $link,
                        (int)$sort_order,
                        $status
                    ]);
                    $message = "Slide added successfully!";
                }

            } else {
                // Edit
                $id = $_POST['id'];

                // If no new image uploaded, keep existing image
                if (!$imageName) {
                    $stmtOld = $db->prepare("SELECT image FROM home_slides WHERE id = ?");
                    $stmtOld->execute([$id]);
                    $existing = $stmtOld->fetch(PDO::FETCH_ASSOC);
                    $imageName = $existing['image'] ?? '';
                }

                $stmt = $db->prepare("
                    UPDATE home_slides
                    SET title = ?, subtitle = ?, image = ?, link = ?, sort_order = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $title,
                    $subtitle,
                    $imageName,
                    $link,
                    (int)$sort_order,
                    $status,
                    $id
                ]);
                $message = "Slide updated successfully!";
            }
        }
    }

    if ($_POST['action'] === 'delete') {
        $id = $_POST['id'];

        // Optionally delete image file from disk
        $stmtImg = $db->prepare("SELECT image FROM home_slides WHERE id = ?");
        $stmtImg->execute([$id]);
        $rowImg = $stmtImg->fetch(PDO::FETCH_ASSOC);
        if ($rowImg && !empty($rowImg['image'])) {
            $filePath = SLIDER_UPLOAD . $rowImg['image'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        $db->prepare("DELETE FROM home_slides WHERE id = ?")->execute([$id]);
        $message = "Slide deleted successfully!";
    }
}

// Fetch all slides
$slides = $db->query("
    SELECT * FROM home_slides
    ORDER BY sort_order, created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Edit slide if requested
$editSlide = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM home_slides WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editSlide = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Home Slider - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<nav class="navbar navbar-dark bg-success">
    <div class="container-fluid">
        <span class="navbar-brand">Home Slider Management</span>
        <a href="index.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
    </div>
</nav>

<div class="container-fluid p-4">
    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Form column -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><?= $editSlide ? 'Edit Slide' : 'Add New Slide' ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?= $editSlide ? 'edit' : 'add' ?>">
                        <?php if ($editSlide): ?>
                            <input type="hidden" name="id" value="<?= $editSlide['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control"
                                   value="<?= htmlspecialchars($editSlide['title'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subtitle</label>
                            <input type="text" name="subtitle" class="form-control"
                                   value="<?= htmlspecialchars($editSlide['subtitle'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Link (optional)</label>
                            <input type="text" name="link" class="form-control"
                                   value="<?= htmlspecialchars($editSlide['link'] ?? '') ?>"
                                   placeholder="e.g. <?= htmlspecialchars('<?=' . 'BASE_URL . "products.php"' ) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control"
                                   value="<?= htmlspecialchars($editSlide['sort_order'] ?? 0) ?>">
                            <small class="text-muted">Lower numbers show first.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= ($editSlide['status'] ?? '') === 'active' ? 'selected' : '' ?>>
                                    Active
                                </option>
                                <option value="inactive" <?= ($editSlide['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>
                                    Inactive
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= $editSlide ? 'Change Image' : 'Image *' ?></label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <?php if ($editSlide && $editSlide['image']): ?>
                                <img src="uploads/slider/<?= htmlspecialchars($editSlide['image']) ?>"
                                     class="img-thumbnail mt-2" width="120">
                            <?php endif; ?>
                            <small class="text-muted d-block mt-1">
                                Recommended: 1200×400 or similar horizontal ratio.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <?= $editSlide ? 'Update' : 'Add' ?> Slide
                        </button>
                        <?php if ($editSlide): ?>
                            <a href="slider.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- List column -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h5>All Slides</h5></div>
                <div class="card-body">
                    <table class="table table-striped align-middle">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Preview</th>
                            <th>Title</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($slides)): ?>
                            <?php foreach ($slides as $slide): ?>
                                <tr>
                                    <td><?= $slide['id'] ?></td>
                                    <td>
                                        <?php if ($slide['image']): ?>
                                            <img src="uploads/slider/<?= htmlspecialchars($slide['image']) ?>"
                                                 class="img-thumbnail" width="90">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($slide['title']) ?></strong><br>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($slide['subtitle']) ?>
                                        </small>
                                    </td>
                                    <td><?= (int)$slide['sort_order'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $slide['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($slide['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($slide['created_at']) ?></td>
                                    <td>
                                        <a href="?edit=<?= $slide['id'] ?>" class="btn btn-sm btn-primary mb-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" style="display:inline;"
                                              onsubmit="return confirm('Delete this slide?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $slide['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger mb-1">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No slides added yet.</td></tr>
                        <?php endif; ?>
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