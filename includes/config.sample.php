<?php
// Website configuration
define('BASE_URL', '');
define('ADMIN_URL', BASE_URL . 'admin/');
define('UPLOAD_DIR', dirname(__DIR__) . '/admin/uploads/');
define('CATEGORY_UPLOAD', UPLOAD_DIR . 'categories/');
define('PRODUCT_UPLOAD', UPLOAD_DIR . 'products/');

// Database configuration
define('DB_HOST', '');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_NAME', '');

// Session
session_start();

// Timezone
date_default_timezone_set('Asia/Kolkata');
