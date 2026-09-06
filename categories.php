<?php
$pageTitle = "Categories - SNA Catalogue";
require_once 'includes/header.php';

$db = getDB();

/**
 * 1. Read incoming filters (category slug + search)
 */
$categorySlug = $_GET['slug'] ?? null;
$searchQuery  = $_GET['search'] ?? '';

/**
 * 2. Get active categories for sidebar and menu
 */
$catStmt = $db->query("SELECT * FROM categories WHERE status='active' ORDER BY display_order");
$allCategories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * 3. If a slug is provided, load that category row
 */
$currentCategory = null;
if ($categorySlug) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ? AND status='active'");
    $stmt->execute([$categorySlug]);
    $currentCategory = $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * 4. Build products query with optional filters
 */
$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active'";
$params = [];

if ($categorySlug) {
    $sql .= " AND c.slug = ?";
    $params[] = $categorySlug;
}

if ($searchQuery !== '') {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
    $like = '%' . $searchQuery . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Page title area (Hurst style) -->
<section class="page-title-area">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2>
                    <?= $currentCategory ? htmlspecialchars($currentCategory['name']) : 'All Products'; ?>
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                        <?php if ($currentCategory): ?>
                            <li class="breadcrumb-item active">
                                <?= htmlspecialchars($currentCategory['name']) ?>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item active">Catalogue</li>
                        <?php endif; ?>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Category/product listing area -->
<section class="product-area">
    <div class="container">
        <!-- Search + meta row -->
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <?php if ($searchQuery): ?>
                    <p class="mb-0">
                        Showing <?= count($products) ?> result(s) for 
                        "<strong><?= htmlspecialchars($searchQuery) ?></strong>"
                    </p>
                <?php else: ?>
                    <p class="mb-0">
                        <?= $currentCategory 
                            ? 'Products in ' . htmlspecialchars($currentCategory['name']) 
                            : 'All catalogue products'; ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <form class="d-inline-block" method="GET">
                    <?php if ($categorySlug): ?>
                        <input type="hidden" name="slug" value="<?= htmlspecialchars($categorySlug) ?>">
                    <?php endif; ?>
                    <input type="text" name="search"
                           value="<?= htmlspecialchars($searchQuery) ?>"
                           class="form-control d-inline-block w-auto me-2"
                           placeholder="Search in catalogue">
                    <button class="btn btn-primary btn-sm" type="submit">Search</button>
                </form>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4 mb-lg-0">
                <aside class="hurst-sidebar">
                    <h5 class="sidebar-title">Categories</h5>
                    <ul class="list-unstyled hurst-category-list">
                        <li>
                            <a href="<?= BASE_URL ?>categories.php"
                               class="<?= !$categorySlug ? 'active' : '' ?>">
                                All Products
                            </a>
                        </li>
                        <?php foreach ($allCategories as $cat): ?>
                            <li>
                                <a href="<?= BASE_URL ?>categories.php?slug=<?= $cat['slug'] ?>"
                                   class="<?= $categorySlug == $cat['slug'] ? 'active' : '' ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </aside>
            </div>

            <!-- Product grid -->
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
                        No products found in this view. Try another category or search term.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>