<?php
require_once '../includes/database.php';
require_once '../includes/image_watermark.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$db      = getDB();
$message = '';
$error   = '';

function post($key, $default = null) {
    return $_POST[$key] ?? $default;
}

/**
 * Handle form submission
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action = $_POST['action'];

    /**
     * ADD / EDIT PRODUCT
     */
    if ($action === 'add' || $action === 'edit') {

        $category_id       = post('category_id', '');
        $name              = post('name', '');
        $description       = post('description', '');
        $short_description = post('short_description', '');
        $price             = post('price', null);
        $original_price    = post('original_price', null);
        $sku               = post('sku', '');
        $dimensions        = post('dimensions', '');
        $material          = post('material', '');
        $color             = post('color', '');
        $stock_quantity    = post('stock_quantity', 0);
        $featured          = post('featured', 'no');
        $status            = post('status', 'active');

        if ($name && $category_id) {
            $slug = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9 ]/', '', $name)));

            /**
             * Handle main image upload (optional on edit)
             */
            $mainImageName = '';

            if (
                isset($_FILES['main_image']) &&
                !empty($_FILES['main_image']['name']) &&
                $_FILES['main_image']['error'] === 0
            ) {
                $allowed  = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['main_image']['name'];
                $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (in_array($ext, $allowed, true)) {
                    $mainImageName = uniqid() . '.' . $ext;
                    $targetPath    = PRODUCT_UPLOAD . $mainImageName;

                    if (move_uploaded_file($_FILES['main_image']['tmp_name'], $targetPath)) {
                        //addSnaWatermark($targetPath);
                    }
                }
            }

            if ($action === 'add') {
                // Unique slug check
                $check = $db->prepare("SELECT id FROM products WHERE slug = ?");
                $check->execute([$slug]);
                if ($check->fetch()) {
                    $slug .= '-' . time();
                }

                $stmt = $db->prepare("
                    INSERT INTO products (
                        category_id, name, slug, description, short_description,
                        price, original_price, sku, dimensions, material, color,
                        stock_quantity, featured, main_image, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $category_id, $name, $slug, $description, $short_description,
                    $price, $original_price, $sku, $dimensions, $material, $color,
                    $stock_quantity, $featured, $mainImageName, $status
                ]);

                $productId = $db->lastInsertId();
                $message   = "Product added successfully!";
            } else {
                // Edit product
                $id = (int) post('id', 0);

                if ($id <= 0) {
                    $error     = "Invalid product ID.";
                    $productId = null;
                } else {
                    // If no new main image uploaded, keep existing main image
                    if (!$mainImageName) {
                        $stmtImg = $db->prepare("SELECT main_image FROM products WHERE id = ?");
                        $stmtImg->execute([$id]);
                        $existing      = $stmtImg->fetch(PDO::FETCH_ASSOC);
                        $mainImageName = $existing['main_image'] ?? '';
                    }

                    $stmt = $db->prepare("
                        UPDATE products SET 
                            category_id = ?, name = ?, slug = ?, description = ?, short_description = ?,
                            price = ?, original_price = ?, sku = ?, dimensions = ?, material = ?, color = ?,
                            stock_quantity = ?, featured = ?, main_image = ?, status = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $category_id, $name, $slug, $description, $short_description,
                        $price, $original_price, $sku, $dimensions, $material, $color,
                        $stock_quantity, $featured, $mainImageName, $status, $id
                    ]);

                    $productId = $id;
                    $message   = "Product updated successfully!";
                }
            }

            /**
             * Handle gallery images (multiple uploads) for both add and edit
             */
            if (!empty($productId) &&
                isset($_FILES['gallery_images']) &&
                !empty($_FILES['gallery_images']['name'][0])
            ) {
                $files   = $_FILES['gallery_images'];
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                $countFiles = count($files['name']);
                for ($i = 0; $i < $countFiles; $i++) {
                    if (!empty($files['name'][$i]) && $files['error'][$i] === 0) {
                        $filename = $files['name'][$i];
                        $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                        if (in_array($ext, $allowed, true)) {
                            $newName   = uniqid('g_') . '.' . $ext;
                            $targetGal = PRODUCT_UPLOAD . $newName;

                            if (move_uploaded_file($files['tmp_name'][$i], $targetGal)) {
                                //addSnaWatermark($targetGal);

                                $imgStmt = $db->prepare("
                                    INSERT INTO product_images (product_id, image, sort_order) 
                                    VALUES (?, ?, 0)
                                ");
                                $imgStmt->execute([$productId, $newName]);
                            }
                        }
                    }
                }
            }

        } else {
            $error = "Product name and category are required";
        }
    }

    /**
     * DELETE PRODUCT
     */
    if ($action === 'delete') {
        $id = (int) post('id', 0);
        if ($id > 0) {

            // Optionally delete main image and gallery images from disk
            $stmtImg = $db->prepare("SELECT main_image FROM products WHERE id = ?");
            $stmtImg->execute([$id]);
            $prod = $stmtImg->fetch(PDO::FETCH_ASSOC);
            if (!empty($prod['main_image'])) {
                $filePath = PRODUCT_UPLOAD . $prod['main_image'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            $stmtGal = $db->prepare("SELECT image FROM product_images WHERE product_id = ?");
            $stmtGal->execute([$id]);
            $galRows = $stmtGal->fetchAll(PDO::FETCH_ASSOC);
            foreach ($galRows as $row) {
                if (!empty($row['image'])) {
                    $gPath = PRODUCT_UPLOAD . $row['image'];
                    if (file_exists($gPath)) {
                        @unlink($gPath);
                    }
                }
            }

            // Delete gallery records then product
            $db->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);

            $message = "Product deleted successfully!";
        }
    }

    /**
     * DELETE SINGLE GALLERY IMAGE
     */
    if ($action === 'delete_gallery') {
        $imgId = (int) post('image_id', 0);

        if ($imgId > 0) {
            $stmtImg = $db->prepare("SELECT image FROM product_images WHERE id = ?");
            $stmtImg->execute([$imgId]);
            $imgRow = $stmtImg->fetch(PDO::FETCH_ASSOC);

            if ($imgRow && !empty($imgRow['image'])) {
                $filePath = PRODUCT_UPLOAD . $imgRow['image'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            $delStmt = $db->prepare("DELETE FROM product_images WHERE id = ?");
            $delStmt->execute([$imgId]);

            $message = "Gallery image deleted successfully!";
        }
    }
}

/**
 * Fetch categories and products
 */
$categories = $db->query("
    SELECT * FROM categories 
    WHERE status='active' 
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

$products = $db->query("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

/**
 * Product for editing
 */
$editProduct   = null;
$galleryImages = [];
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($editProduct) {
        $imgStmt = $db->prepare("
            SELECT * FROM product_images 
            WHERE product_id = ? 
            ORDER BY sort_order, id
        ");
        $imgStmt->execute([$editProduct['id']]);
        $galleryImages = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<nav class="navbar navbar-dark bg-success">
    <div class="container-fluid">
        <span class="navbar-brand">Products Management</span>
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
                    <h5><?= $editProduct ? 'Edit Product' : 'Add New Product' ?></h5>
                </div>
                <div class="card-body">
                    <form id="product-form" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?= $editProduct ? 'edit' : 'add' ?>">
                        <?php if ($editProduct): ?>
                            <input type="hidden" name="id" value="<?= (int)$editProduct['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"
                                        <?= ($editProduct['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product Name *</label>
                            <input type="text" name="name" class="form-control"
                                   value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Short Description (Below product name)</label>
                            <textarea name="short_description" class="form-control" rows="2"><?= htmlspecialchars($editProduct['short_description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Full Description (Below product images)</label>
                            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" step="0.01" name="price" class="form-control"
                                       value="<?= htmlspecialchars($editProduct['price'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Original Price</label>
                                <input type="number" step="0.01" name="original_price" class="form-control"
                                       value="<?= htmlspecialchars($editProduct['original_price'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <!-- <div class="col-md-4 mb-3">
                                <label class="form-label">SKU</label>
                                <input type="text" name="sku" class="form-control"
                                       value="<?= htmlspecialchars($editProduct['sku'] ?? '') ?>">
                            </div> -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Dimensions</label>
                                <input type="text" name="dimensions" class="form-control"
                                       value="<?= htmlspecialchars($editProduct['dimensions'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Material</label>
                                <input type="text" name="material" class="form-control"
                                       value="<?= htmlspecialchars($editProduct['material'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Colors</label>
                                <input type="text" name="color" class="form-control"
                                       value="<?= htmlspecialchars($editProduct['color'] ?? '') ?>">
                            </div>
                            <!-- <div class="col-md-6 mb-3">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" name="stock_quantity" class="form-control"
                                       value="<?= htmlspecialchars($editProduct['stock_quantity'] ?? 0) ?>">
                            </div> -->
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Featured Product?</label>
                                <select name="featured" class="form-select">
                                    <option value="no"  <?= ($editProduct['featured'] ?? 'no') === 'no' ? 'selected' : '' ?>>No</option>
                                    <option value="yes" <?= ($editProduct['featured'] ?? 'no') === 'yes' ? 'selected' : '' ?>>Yes</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active"   <?= ($editProduct['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= ($editProduct['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Main Image</label>
                            <input type="file" name="main_image" class="form-control" accept="image/*">
                            <?php if ($editProduct && !empty($editProduct['main_image'])): ?>
                                <img src="uploads/products/<?= htmlspecialchars($editProduct['main_image']) ?>"
                                     class="img-thumbnail mt-2" width="100" alt="">
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gallery Images (you can select multiple)</label>
                            <input type="file" name="gallery_images[]" class="form-control"
                                   accept="image/*" multiple>
                            <?php if ($editProduct && !empty($galleryImages)): ?>
                                <small class="text-muted d-block mt-1">Existing gallery images:</small>
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    <?php foreach ($galleryImages as $gimg): ?>
                                        <div class="position-relative" style="width:70px;height:70px;">
                                            <img src="uploads/products/<?= htmlspecialchars($gimg['image']) ?>"
                                                 class="img-thumbnail"
                                                 style="width:100%;height:100%;object-fit:cover;" alt="">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <?= $editProduct ? 'Update' : 'Add' ?> Product
                        </button>
                        <?php if ($editProduct): ?>
                            <a href="products.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                        <?php endif; ?>
                    </form>

                    <?php if ($editProduct && !empty($galleryImages)): ?>
                        <div class="mt-3">
                            <small class="text-muted d-block">Delete gallery images:</small>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                <?php foreach ($galleryImages as $gimg): ?>
                                    <div class="position-relative" style="width:70px;height:70px;">
                                        <img src="uploads/products/<?= htmlspecialchars($gimg['image']) ?>"
                                             class="img-thumbnail"
                                             style="width:100%;height:100%;object-fit:cover;" alt="">
                                        <form method="POST"
                                              style="position:absolute;top:0;right:0;"
                                              onsubmit="return confirm('Delete this image?');">
                                            <input type="hidden" name="action" value="delete_gallery">
                                            <input type="hidden" name="image_id" value="<?= (int)$gimg['id'] ?>">
                                            <button type="submit"
                                                    class="btn btn-sm btn-danger p-0"
                                                    style="width:20px;height:20px;line-height:18px;font-size:11px;">
                                                &times;
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- List column -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h5>All Products</h5></div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>ID</th><th>Name</th><th>Category</th>
                            <th>Price</th><th>Featured</th><th>Status</th><th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($products as $prod): ?>
                            <tr>
                                <td><?= $prod['id'] ?></td>
                                <td><?= htmlspecialchars($prod['name']) ?></td>
                                <td><?= htmlspecialchars($prod['category_name']) ?></td>
                                <td>₹<?= number_format($prod['price'] ?? 0, 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= $prod['featured'] === 'yes' ? 'warning' : 'secondary' ?>">
                                        <?= ucfirst($prod['featured']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $prod['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($prod['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?edit=<?= $prod['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" style="display:inline;"
                                          onsubmit="return confirm('Delete this product?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $prod['id'] ?>">
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