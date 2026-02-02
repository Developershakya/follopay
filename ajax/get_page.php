<?php
require_once '../config/constants.php';
require_once '../controllers/AuthController.php';

$auth = new AuthController();
$isLoggedIn = $auth->checkAuth();
$isAdmin = $auth->isAdmin();

$page = $_GET['page'] ?? 'dashboard';

// Pages accessible without login
$publicPages = ['login', 'register', 'forgot-password'];

// Redirect to login if not authenticated and trying to access protected page
if (!$isLoggedIn && !in_array($page, $publicPages)) {
    $page = 'login';
}

// Redirect to dashboard if already logged in and trying to access auth pages
if ($isLoggedIn && in_array($page, ['login', 'register'])) {
    $page = 'dashboard';
}

// Check if user is banned
if ($isLoggedIn) {
    $user = $auth->getCurrentUser();
    if ($user && $user['is_banned']) {
        $page = 'banned';
    }
}

// Map page to view file
$viewFiles = [
    'dashboard' => '../views/dashboard.php',
    'earn' => '../views/earn.php',
    'wallet' => '../views/wallet.php',
    'withdraw' => '../views/withdraw.php',
    'profile' => '../views/profile.php',
    'help' => '../views/help.php',
    'login' => '../views/auth/login.php',
    'register' => '../views/auth/register.php',
    'banned' => '../views/banned.php',
    'admin' => '../views/admin/dashboard.php',
    'admin-posts' => '../views/admin/posts.php',
    'admin-withdrawals' => '../views/admin/withdrawals.php',
    'admin-users' => '../views/admin/users.php',
];

$viewFile = $viewFiles[$page] ?? '../views/404.php';

// For AJAX requests, we only return the content of the view file (without layout)
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // If it's an AJAX request, we return the view file's content
    require_once $viewFile;
} else {
    // If it's not an AJAX request, we return the full page (this shouldn't happen in our SPA flow)
    // But we can handle it by redirecting to index.php with the page parameter
    header('Location: ../index.php?page=' . $page);
    exit;
}