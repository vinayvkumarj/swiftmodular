<?php
$pageTitle = "Product Details - Furniture Catalogue";
require_once 'includes/header.php';

$db = getDB();

// Get slug from URL
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    // If no slug, redirect back to catalogue
    header("Location: " . BASE_URL . "categories.php");
    exit;
}

// Fetch product + category data by slug
$stmt = $db->prepare("
    SELECT 
        p.*,
        c.name AS category_name,
        c.slug AS category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.slug = ? AND p.status = 'active'
    LIMIT 1
");
$stmt->execute([$slug]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    // If product not found, redirect
    header("Location: " . BASE_URL . "categories.php");
    exit;
}

// Increment view count (optional)
$update = $db->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
$update->execute([$product['id']]);

// Fetch gallery images for this product
$gStmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, id");
$gStmt->execute([$product['id']]);
$galleryImages = $gStmt->fetchAll(PDO::FETCH_ASSOC);

// Build slides array: main image first, then gallery
$slides = [];

if (!empty($product['main_image'])) {
    $slides[] = [
        'src' => BASE_URL . "admin/uploads/products/" . $product['main_image'],
        'alt' => $product['name']
    ];
}

foreach ($galleryImages as $gimg) {
    $slides[] = [
        'src' => BASE_URL . "admin/uploads/products/" . $gimg['image'],
        'alt' => $product['name']
    ];
}

// If no images at all, fallback to placeholder
if (empty($slides)) {
    $slides[] = [
        'src' => "https://via.placeholder.com/600x600?text=" . urlencode($product['name']),
        'alt' => $product['name']
    ];
}
?>

<section class="page-title-area">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2><?= htmlspecialchars($product['name']) ?></h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>categories.php?slug=<?= $product['category_slug'] ?>">
                                <?= htmlspecialchars($product['category_name']) ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= htmlspecialchars($product['name']) ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="product-details-area">
    <div class="container">
        <div class="row">
            <!-- Image left: carousel + thumbnails -->
            <div class="col-md-6">
                <div class="pd-img-wrapper">
                    <div id="productGallery" class="carousel slide" aria-label="Product gallery">
                        <div class="carousel-inner">
                            <?php foreach ($slides as $index => $img): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <!-- IMPORTANT: plain img, not inside a button or link -->
                                    <img src="<?= htmlspecialchars($img['src']) ?>"
                                        class="d-block w-100 main-product-img"
                                        alt="<?= htmlspecialchars($img['alt']) ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($slides) > 1): ?>
                            <!-- Simple custom prev/next buttons -->
                            <button type="button" id="pg-prev" class="carousel-control-prev" aria-label="Previous slide">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            </button>
                            <button type="button" id="pg-next" class="carousel-control-next" aria-label="Next slide">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            </button>

                            <!-- Thumbnails JUST below, NOT wrapped around the main image -->
                            <div class="product-thumbnails mt-3">
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($slides as $index => $img): ?>
                                        <button type="button"
                                                class="btn p-0 border-0 thumb-item"
                                                data-index="<?= $index ?>">
                                            <img src="<?= htmlspecialchars($img['src']) ?>"
                                                class="img-thumbnail gallery-thumbnail <?= $index === 0 ? 'active' : '' ?>"
                                                alt="<?= htmlspecialchars($img['alt']) ?>">
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Content right -->
            <div class="col-md-6">
                <div class="pd-content">
                    <h1 class="pd-title"><?= htmlspecialchars($product['name']) ?></h1>
                    <p class="pd-category">
                        In <a href="<?= BASE_URL ?>categories.php?slug=<?= $product['category_slug'] ?>">
                            <?= htmlspecialchars($product['category_name']) ?>
                        </a>
                    </p>

                    <?php if ($product['price']): ?>
                        <div class="pd-price-box">
                            <?php if ($product['original_price']): ?>
                                <span class="pd-old-price">
                                    ₹<?= number_format($product['original_price'], 2) ?>
                                </span>
                            <?php endif; ?>
                            <span class="pd-current-price">
                                ₹<?= number_format($product['price'], 2) ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if ($product['short_description']): ?>
                        <p class="pd-short-desc">
                            <?= htmlspecialchars($product['short_description']) ?>
                        </p>
                    <?php endif; ?>

                    <!-- <div class="pd-meta">
                        <?php if ($product['sku']): ?>
                            <p><strong>SKU:</strong> <?= htmlspecialchars($product['sku']) ?></p>
                        <?php endif; ?>
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <p><strong>Availability:</strong> <span class="text-success">In stock</span></p>
                        <?php else: ?>
                            <p><strong>Availability:</strong> <span class="text-danger">Out of stock</span></p>
                        <?php endif; ?>
                    </div> -->

                    <div class="pd-actions">
                        <a href="<?= BASE_URL ?>contact.php" class="btn btn-primary">
                            Enquire about this item
                        </a>
                        <a href="<?= BASE_URL ?>categories.php?slug=<?= $product['category_slug'] ?>"
                           class="btn btn-outline-secondary ms-2">
                            Back to <?= htmlspecialchars($product['category_name']) ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs for description/specs -->
        <div class="row mt-5">
            <div class="col-12">
                <ul class="nav nav-tabs hurst-pd-tabs" id="pdTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="specs-tab" data-bs-toggle="tab"
                                data-bs-target="#specs" type="button" role="tab">
                            Specifications
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link " id="desc-tab" data-bs-toggle="tab"
                                data-bs-target="#desc" type="button" role="tab">
                            Description
                        </button>
                    </li>
                </ul>
                <div class="tab-content hurst-pd-tab-content" id="pdTabContent">
                    <div class="tab-pane fade  " id="desc" role="tabpanel">
                        <?php if ($product['description']): ?>
                            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        <?php else: ?>
                            <p>No additional description for this item.</p>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade show active" id="specs" role="tabpanel">
                        <table class="table table-bordered specs-table mb-0">
                            <?php if ($product['dimensions']): ?>
                                <tr><th>Dimensions</th><td><?= htmlspecialchars($product['dimensions']) ?></td></tr>
                            <?php endif; ?>
                            <?php if ($product['material']): ?>
                                <tr><th>Material</th><td><?= htmlspecialchars($product['material']) ?></td></tr>
                            <?php endif; ?>
                            <?php if ($product['color']): ?>
                                <tr><th>Color</th><td><?= htmlspecialchars($product['color']) ?></td></tr>
                            <?php endif; ?>
                            <!-- <tr>
                                <th>Views</th>
                                <td><?= (int)$product['views'] ?></td>
                            </tr> -->
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const gallery  = document.getElementById('productGallery');
    if (!gallery) return;

    const items    = Array.from(gallery.querySelectorAll('.carousel-item'));
    const prevBtn  = document.getElementById('pg-prev');
    const nextBtn  = document.getElementById('pg-next');
    const thumbs   = Array.from(gallery.querySelectorAll('.product-thumbnails .thumb-item'));

    if (!items.length) return;

    // Determine initial active slide
    let currentIndex = items.findIndex(item => item.classList.contains('active'));
    if (currentIndex < 0) currentIndex = 0;

    function updateSlides(index) {
        const total = items.length;
        if (index < 0) index = total - 1;
        if (index >= total) index = 0;

        // Update main slides
        items.forEach((item, i) => {
            item.classList.toggle('active', i === index);
        });

        // Update thumbnails
        thumbs.forEach((btn, i) => {
            const img = btn.querySelector('.gallery-thumbnail');
            if (img) img.classList.toggle('active', i === index);
        });

        currentIndex = index;
    }

    // Prev button click
    if (prevBtn) {
        prevBtn.addEventListener('click', function (e) {
            e.preventDefault();
            updateSlides(currentIndex - 1);
        });
    }

    // Next button click
    if (nextBtn) {
        nextBtn.addEventListener('click', function (e) {
            e.preventDefault();
            updateSlides(currentIndex + 1);
        });
    }

    // Thumbnail click
    thumbs.forEach((btn) => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const idx = parseInt(btn.getAttribute('data-index'), 10);
            if (!Number.isNaN(idx)) {
                updateSlides(idx);
            }
        });
    });

    // CRUCIAL: prevent any click on the main image from bubbling into controls
    const mainImages = gallery.querySelectorAll('.main-product-img');
    mainImages.forEach(img => {
        img.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            // Do nothing on main image click
        });
    });

    // Initialise state
    updateSlides(currentIndex);
});
</script>
<?php require_once 'includes/footer.php'; ?>