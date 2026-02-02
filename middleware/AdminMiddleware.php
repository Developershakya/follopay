<?php
class AdminMiddleware {
    public static function check($redirectTo = '/dashboard') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            // AJAX request check
            if (function_exists('isAjaxRequest') && isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Admin access required',
                    'redirect' => $redirectTo
                ]);
                exit;
            } else {
                header('Location: ' . $redirectTo);
                exit;
            }
        }
    }

    public static function checkAdminOrJson() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized access'
            ]);
            exit;
        }
    }
}
?>
