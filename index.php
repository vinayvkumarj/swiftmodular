<?php
$pageTitle = "Home - SNA Catalogue";
require_once 'includes/header.php';

$db = getDB();

// Get featured products
$featuredStmt = $db->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.featured='yes' AND p.status='active' 
    LIMIT 8
");
$featuredProducts = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);

// Get all categories
$catStmt = $db->query("
    SELECT * FROM categories 
    WHERE status='active' 
    ORDER BY display_order 
    LIMIT 6
");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Get home slider images
$sliderStmt = $db->query("
    SELECT * FROM home_slides 
    WHERE status = 'active' 
    ORDER BY sort_order, created_at DESC
");
$homeSlides = $sliderStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Hero Section -->
<section class="hurst-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 hero-left">
                <!-- <span class="hero-subtitle">New Collection 2026</span> -->
                <h2>Big Style for Smart Spaces.</h2>
                <p>Maximize every inch with clever modular designs that don't compromise on aesthetic or utility.</p>
                <a href="#featured" class="btn btn-primary">Check Out</a>
            </div>
            <div class="col-md-6 hero-right text-center">
                <img src="<?= BASE_URL ?>assets/images/main-hero.png" alt="Hero Image" class="img-fluid hero-image">
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-3">Top Categories</h2>

        <div class="row">
            <?php foreach ($categories as $cat): ?>
                <div class="col-6 col-sm-6 col-md-4 mb-4">
                    <div class="category-card">
                        <?php if ($cat['image']): ?>
                            <a href="<?= BASE_URL ?>categories.php?slug=<?= $cat['slug'] ?>">
                                <img src="<?= BASE_URL ?>admin/uploads/categories/<?= htmlspecialchars($cat['image']) ?>" 
                                 alt="<?= htmlspecialchars($cat['name']) ?>">
                            </a>
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x300?text=<?= urlencode($cat['name']) ?>" 
                                 alt="<?= htmlspecialchars($cat['name']) ?>">
                        <?php endif; ?>
                        <div class="category-overlay">
                            <a href="<?= BASE_URL ?>categories.php?slug=<?= $cat['slug'] ?>">
                                <h3><?= htmlspecialchars($cat['name']) ?></h3>
                            </a>
                            <p><?= htmlspecialchars(substr($cat['description'], 0, 50)) ?>...</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5" id="featured">
    <div class="container">
        <h2 class="text-center mb-3">Featured Products</h2>

        <?php if (count($featuredProducts) > 0): ?>
            <div class="row">
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="col-6 col-sm-6 col-md-3 mb-4">
                        <div class="product-card">
                            <?php if ($product['main_image']): ?>
                                <img src="<?= BASE_URL ?>admin/uploads/products/<?= htmlspecialchars($product['main_image']) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/300x300?text=<?= urlencode($product['name']) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php endif; ?>

                            <div class="product-info">
                                <small class="text-muted"><?= htmlspecialchars($product['category_name']) ?></small>
                                <h3><?= htmlspecialchars($product['name']) ?></h3>

                                <?php if ($product['price']): ?>
                                    <div class="price">
                                        ₹<?= number_format($product['price'], 2) ?>
                                        <?php if ($product['original_price']): ?>
                                            <span class="original-price">₹<?= number_format($product['original_price'], 2) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <a href="<?= BASE_URL ?>product.php?slug=<?= $product['slug'] ?>" class="btn btn-primary btn-sm mt-2">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center">
                <p>No featured products yet. <a href="<?= ADMIN_URL ?>products.php">Add products</a> from admin panel.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Featured Products -->
<?php if (!empty($homeSlides)): ?>
<section class="home-slider py-4">
    <div class="container">
        <h2 class="text-center mb-3">Latest Products</h2>
        <div class="swiper homeSliderSwiper">
            <div class="swiper-wrapper">
                <?php foreach ($homeSlides as $slide): ?>
                    <div class="swiper-slide">
                        <div class="home-slide-card text-center">
                            <div class="home-slide-img-wrap">
                                <img src="<?= BASE_URL ?>admin/uploads/slider/<?= htmlspecialchars($slide['image']) ?>"
                                     class="img-fluid home-slide-img"
                                     alt="<?= htmlspecialchars($slide['title'] ?: 'Slide') ?>">
                            </div>
                            <?php if (!empty($slide['title'])): ?>
                                <div class="home-slide-title">
                                    <?= htmlspecialchars($slide['title']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Navigation buttons -->
            <div class="swiper-button-prev home-swiper-button"></div>
            <div class="swiper-button-next home-swiper-button"></div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Custom -->
<section class="py-5" style="background-color:#C68754">
    <div class="container"> 
        <div class="row">
            <div class="col-md-6">
                <h2 class=" mb-3 text-white">Want custom modular furniture?</h2>
                <h5 class=" text-white"><em>"Tailored to your taste, built for your life."</em></h5>
            </div>
            <div class="col-md-6 d-flex text-center align-items-center">
                <a href="<?= BASE_URL ?>contact.php"><button class="center-btn">Reach out now to start designing your perfect piece.</button></a>
            </div>
        </div>
    </div>
</section>

<!-- Image preview modal -->
<div class="modal fade" id="sliderImagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-body p-0 position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center p-3">
                    <img id="sliderPreviewImage" src="" alt="Preview"
                         class="img-fluid" style="max-height:70vh; object-fit:contain;">
                    <div id="sliderPreviewTitle" class="text-white mt-2 small"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>