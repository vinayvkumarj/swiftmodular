<?php require_once 'includes/database.php'; ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'SNA Catalogue' ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper@9/swiper-bundle.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts (Hurst style) -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-end">
                    <a href="https://wa.me/9900825976" target="_blank"><i class="fa-brands fa-whatsapp"></i> +91 99008 25976 </a>
                </div>
                <!--
                <div class="col-md-6 text-end">
                    <a href="<?= ADMIN_URL ?>login.php"><i class="fas fa-user"></i> Admin</a>
                </div>
                -->
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="row align-items-center py-3">
                <div class="col-md-11">
                    
                        <!-- <h1>SWIFT MODULAR</h1> -->
                        <div class="main-logo">
                            <a href="<?= BASE_URL ?>" class="logo main-logo">
                                <img id="siteLogo" src="<?= BASE_URL ?>/admin/uploads/sm_logo.png" alt="SM logo" 
                                data-logo-light="<?= BASE_URL ?>/admin/uploads/sm_logo.png"
                                data-logo-dark="<?= BASE_URL ?>/admin/uploads/sm_logo_light.png">
                            </a>
                        </div>
                        <div class="tagline">A furniture brand by Shree Nanjundeshwara Associates</div>
                    
                </div>
                <!-- <div class="col-md-3">
                    <form class="search-form" action="<?= BASE_URL ?>categories.php" method="GET">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Search furniture...">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div> -->
                <div class="col-md-1 text-end d-none d-md-block">
                <button class="btn btn-sm theme-toggle-btn" type="button"
                        aria-label="Toggle light and dark mode" id="mode-btn">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
            </div>
        </div>
    </header>

   <?php
    // Get only the path part (no query string)
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $currentFile = basename($currentPath);
    ?>

<nav class="navbar navbar-expand-lg navbar-dark " style="background:#2c3e50;">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentFile === '' || $currentFile === 'index.php') ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>">Home</a>
                </li>

                <?php
                $db = getDB();
                $stmt = $db->query("SELECT * FROM categories WHERE status='active' ORDER BY display_order");
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= ($currentFile === 'categories.php') ? 'active' : '' ?>"
                       href="#" data-bs-toggle="dropdown">Categories</a>
                    <ul class="dropdown-menu">
                        <?php foreach ($categories as $cat): ?>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>categories.php?slug=<?= $cat['slug'] ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= ($currentFile === 'products.php') ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>products.php">Products</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= ($currentFile === 'about.php') ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>about.php">About Us</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= ($currentFile === 'contact.php') ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>contact.php">Contact</a>
                </li>
            </ul>
        </div>
    </div>
</nav>