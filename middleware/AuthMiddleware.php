<?php
class AuthMiddleware {
    public static function handle($redirectTo = '/login') {
        
        
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            if (self::isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Authentication required',
                    'redirect' => $redirectTo
                ]);
                exit;
            } else {
                header('Location: ' . $redirectTo);
                exit;
            }
        }
        
        // Update last active timestamp
        if (isset($_SESSION['user_id'])) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE users SET last_active = NOW() WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        }
    }
    
    public static function guestOnly($redirectTo = '/dashboard') {
        session_start();
        
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }
    
    public static function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    public static function checkBanStatus() {
        if (isset($_SESSION['user_id'])) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT is_banned, ban_reason FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if ($user && $user['is_banned']) {
                session_destroy();
                
                if (self::isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'banned' => true,
                        'reason' => $user['ban_reason']
                    ]);
                    exit;
                } else {
                    $_SESSION['ban_message'] = $user['ban_reason'];
                    header('Location: /banned');
                    exit;
                }
            }
        }
    }
}
?>