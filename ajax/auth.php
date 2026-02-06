<?php
require_once '../config/constants.php';
require_once '../controllers/AuthController.php';

header('Content-Type: application/json');

$auth = new AuthController();
$action = $_POST['action'] ?? '';

switch($action) {
    case 'register':
        $result = $auth->register([
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? ''
        ]);
        echo json_encode($result);
        break;

    case 'verify-otp':
        $result = $auth->verifyOTP([
            'email' => $_POST['email'] ?? '',
            'otp' => $_POST['otp'] ?? ''
        ]);
        echo json_encode($result);
        break;

    case 'refresh-otp':
        $result = $auth->refreshOTP($_POST['email'] ?? '');
        echo json_encode($result);
        break;
        
    case 'login':
        $result = $auth->login([
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'remember' => $_POST['remember'] ?? false
        ]);
        echo json_encode($result);
        break;

    case 'forgot-password':
        $result = $auth->forgotPassword($_POST['email'] ?? '');
        echo json_encode($result);
        break;

    case 'reset-password':
        $result = $auth->resetPassword([
            'token' => $_POST['token'] ?? '',
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? ''
        ]);
        echo json_encode($result);
        break;
        
    case 'logout':
        $result = $auth->logout();
        echo json_encode($result);
        break;
        
    case 'check':
        echo json_encode([
            'authenticated' => $auth->checkAuth(),
            'user' => $auth->getCurrentUser()
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>