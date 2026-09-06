<?php
require_once '../includes/database.php';

// Check authentication
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$db = getDB();

// Get statistics
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$activeProducts = $db->query("SELECT COUNT(*) FROM products WHERE status='active'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SM Catalogue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand">SM Catalogue Admin</span>
            <div>
                <span class="text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 bg-light min-vh-100 p-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-folder me-2"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-box me-2"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="slider.php">
                            <i class="fas fa-box me-2"></i> Slider Images
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i> View Website
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2>Dashboard</h2>
                
                <!-- Statistics Cards -->
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5>Total Products</h5>
                                <h2><?= $totalProducts ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5>Active Products</h5>
                                <h2><?= $activeProducts ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5>Categories</h5>
                                <h2><?= $totalCategories ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="categories.php" class="btn btn-primary me-2">
                            <i class="fas fa-plus"></i> Add Category
                        </a>
                        <a href="products.php" class="btn btn-success me-2">
                            <i class="fas fa-plus"></i> Add Product
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>