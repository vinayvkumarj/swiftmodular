<?php
require_once '../includes/database.php';

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if($username && $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($admin && $password === $admin['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Please enter username and password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Furniture Catalogue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; min-height: 100vh; display: flex; align-items: center; }
        .login-container { max-width: 400px; margin: auto; }
        .card { border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card p-4">
                <h3 class="text-center mb-4">Admin Login</h3>
                
                <?php if($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                
                <div class="text-center mt-3">
                    <a href="<?= BASE_URL ?>">← Back to Website</a>
                </div>
                
            </div>
        </div>
    </div>
</body>
</html>