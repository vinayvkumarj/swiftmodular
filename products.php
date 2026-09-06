<?php
$pageTitle = "All Products - SNA Catalogue";
require_once 'includes/header.php';

$db = getDB();

// Filters
$search     = $_GET['search'] ?? '';
$catFilter  = $_GET['category'] ?? '';
$minPrice   = $_GET['min_price'] ?? '';
$maxPrice   = $_GET['max_price'] ?? '';

// For sidebar filters
$catStmt = $db->query("SELECT id, name, slug FROM categories WHERE status='active' ORDER BY name");
$filterCategories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Build product query
$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active'";
$params = [];

if ($catFilter !== '' && $catFilter !== 'all') {
    $sql .= " AND c.id = ?";
    $params[] = $catFilter;
}

if ($search !== '') {
    $sql .= " AND (p.name LIKE ? OR p.short_description LIKE ? OR p.description LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($minPrice !== '' && is_numeric($minPrice)) {
    $sql .= " AND p.price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $sql .= " AND p.price <= ?";
    $params[] = $maxPrice;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="page-title-area">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2>All Products</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                        <li class="breadcrumb-item active">Products</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="product-area">
    <div class="container">
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <p class="mb-0">
                    Showing <?= count($products) ?> product(s)
                    <?php if ($search): ?>
                        for "<strong><?= htmlspecialchars($search) ?></strong>"
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="row">
            <!-- Filter sidebar -->
            <div class="col-lg-3 mb-4 mb-lg-0">
                <aside class="hurst-sidebar">
                    <h5 class="sidebar-title">Filter</h5>
                    <form method="GET">
                        <div class="mb-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control"
                                   value="<?= htmlspecialchars($search) ?>"
                                   placeholder="Search products...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="all">All Categories</option>
                                <?php foreach ($filterCategories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"
                                        <?= $catFilter == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price range (₹)</label>
                            <div class="d-flex gap-2">
                                <input type="number" name="min_price" class="form-control" placeholder="Min"
                                       value="<?= htmlspecialchars($minPrice) ?>">
                                <input type="number" name="max_price" class="form-control" placeholder="Max"
                                       value="<?= htmlspecialchars($maxPrice) ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                        <a href="<?= BASE_URL ?>products.php" class="btn btn-outline-secondary w-100 mt-2">
                            Reset
                        </a>
                    </form>
                </aside>
            </div>

            <!-- Products grid -->
            <div class="col-lg-9">
                <?php if (count($products) > 0): ?>
                    <div class="row g-4">
                        <?php foreach ($products as $product): ?>
                            <div class="col-md-4 col-sm-6">
                                <div class="hurst-product-card">
                                    <div class="hurst-product-img">
                                        <a href="<?= BASE_URL ?>product.php?slug=<?= $product['slug'] ?>">
                                            <?php if ($product['main_image']): ?>
                                                <img src="<?= BASE_URL ?>admin/uploads/products/<?= htmlspecialchars($product['main_image']) ?>"
                                                     alt="<?= htmlspecialchars($product['name']) ?>">
                                            <?php else: ?>
                                                <img src="https://via.placeholder.com/300x300?text=<?= urlencode($product['name']) ?>"
                                                     alt="<?= htmlspecialchars($product['name']) ?>">
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                    <div class="hurst-product-info text-center">
                                        <span class="product-category">
                                            <?= htmlspecialchars($product['category_name']) ?>
                                        </span>
                                        <h3>
                                            <a href="<?= BASE_URL ?>product.php?slug=<?= $product['slug'] ?>">
                                                <?= htmlspecialchars($product['name']) ?>
                                            </a>
                                        </h3>
                                        <?php if ($product['price']): ?>
                                            <div class="product-price">
                                                <span class="current">
                                                    ₹<?= number_format($product['price'], 2) ?>
                                                </span>
                                                <?php if ($product['original_price']): ?>
                                                    <span class="old">
                                                        ₹<?= number_format($product['original_price'], 2) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No products found for this filter.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>