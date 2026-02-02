<?php
require_once '../config/constants.php';
require_once '../controllers/AuthController.php';

$auth = new AuthController();
$isLoggedIn = $auth->checkAuth();
$isAdmin = $auth->isAdmin();

// Determine page to load
$page = $_GET['page'] ?? 'dashboard';

// Pages accessible without login
$publicPages = ['login', 'register', 'forgot-password'];

// Redirect to login if not authenticated and trying to access protected page
if (!$isLoggedIn && !in_array($page, $publicPages)) {
    header('Location: index.php?page=login');
    exit;
}

// Redirect to dashboard if already logged in and trying to access auth pages
if ($isLoggedIn && in_array($page, ['login', 'register'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Check if user is banned
if ($isLoggedIn) {
    $user = $auth->getCurrentUser();
    if ($user && $user['is_banned']) {
        $page = 'banned';
    }
}

// Load appropriate page
switch($page) {
    case 'dashboard':
        include '../views/dashboard.php';
        break;
    case 'earn':
        include '../views/earn.php';
        break;
    case 'wallet':
        include '../views/wallet.php';
        break;
    case 'withdraw':
        include '../views/withdraw.php';
        break;
    case 'profile':
        include '../views/profile.php';
        break;
    case 'help':
        include '../views/help.php';
        break;
    case 'login':
        include '../views/auth/login.php';
        break;
    case 'register':
        include '../views/auth/register.php';
        break;
    case 'admin':
        if (!$isAdmin) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        include '../views/admin/dashboard.php';
        break;
    case 'admin-posts':
        if (!$isAdmin) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        include '../views/admin/posts.php';
        break;
    case 'admin-withdrawals':
        if (!$isAdmin) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        include '../views/admin/withdrawals.php';
        break;
    case 'admin-users':
        if (!$isAdmin) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        include '../views/admin/users.php';
        break;
    case 'banned':
        include '../views/banned.php';
        break;
    default:
        include '../views/404.php';
}
?>