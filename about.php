<?php
$pageTitle = "About Us - SNA Catalogue";
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row align-items-center">
        <div class="col-md-10">
            <h1>About Us</h1>
            <p class="lead">Your trusted partner for modular furniture since 2008</p>
            <p>At <b>SWIFT MODULAR</b>, we’re cutting out the middleman and the "luxury markup" that usually comes with high-end home decor. Based right here in the heart of Bengaluru, we noticed a massive gap: most showroom furniture looks great but costs a fortune, while affordable options often fall apart after a year. We decided to flip the script by offering the exact same premium, showroom-quality modular furniture you’ve been eyeing—but at direct factory prices..</p>
            <p>We specialize in free-standing modular furniture designed for the way we live today. Whether you’re moving into your first apartment, upgrading your home office, or constantly rearranging your vibe, our pieces are built to move with you. No permanent wall-fixing, no messy installations, and no "built-in" headaches. Just sleek, high-durability wardrobes, storage units, and kitchen modules that you can plug and play into any layout.</p>
        </div>
        <div class="col-md-2 footer-logo" >
            <img
                id="aboutImage"
                src="<?= BASE_URL ?>admin/uploads/sm_vertical_logo.png"
                alt="About SNA"
                class="img-fluid"
                data-img-light="<?= BASE_URL ?>admin/uploads/sm_vertical_logo.png"
                data-img-dark="<?= BASE_URL ?>admin/uploads/sm_vertical_light_logo.png"
            >
        </div>
    </div>
</div>

<div class="container py-3">
    <div class="row align-items-center">
        <div class="col-md-3 " >
            <img
                src="<?= BASE_URL ?>admin/uploads/products.png"
                alt="About SNA"
                class="img-fluid"
                data-img-light="<?= BASE_URL ?>admin/uploads/products.png"
                data-img-dark="<?= BASE_URL ?>admin/uploads/products.png"
            >
        </div>
        <div class="col-md-9">
            <h3>Our Product Offerings</h3>
            <p>We manufacture high-performance, free-standing modular furniture <b>built to order</b>, giving you custom layout flexibility without permanent wall-fixing. Every piece is engineered using premium substrates like <b>Particle Board, Plywood, Block Board, and waterproof WPC </b>. We source our materials exclusively from reputed, certified manufacturers, combining top-tier core boards with world-class hardware to ensure your furniture looks sleek and survives years of daily use.</p>
        </div>
    </div>
</div>

<div class="container py-3">
    <div class="row align-items-center">
        <div class="col-md-9">
            <h3>Custom Kitchens & Wardrobes</h3>
            <p>Our residential lineup specializes in space-optimizing modular kitchens and wardrobes tailored to your exact floor plan. Because we operate on a direct-from-factory model, you get the premium finishes and soft-close German engineering of luxury showrooms at direct factory prices. Best of all, these <b>made-to-order</b> units are engineered with a free-standing philosophy—built to fit your current space perfectly, but fully mobile if you ever decide to move.</p>
        </div>
        <div class="col-md-3" >
            <img
                src="<?= BASE_URL ?>admin/uploads/kitchen.png"
                alt="About SNA"
                class="img-fluid"
                data-img-light="<?= BASE_URL ?>admin/uploads/kitchen.png"
                data-img-dark="<?= BASE_URL ?>admin/uploads/kitchen.png"
            >
        </div>
    </div>
</div>


<?php require_once 'includes/footer.php'; ?>