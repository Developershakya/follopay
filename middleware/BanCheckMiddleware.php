<?php
class BanCheckMiddleware {
    public static function handle() {
     
        
        if (isset($_SESSION['user_id'])) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT is_banned, ban_reason FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if ($user && $user['is_banned']) {
                // Store ban info in session
                $_SESSION['banned'] = true;
                $_SESSION['ban_reason'] = $user['ban_reason'];
                
                // Destroy user session but keep ban info
                unset($_SESSION['user_id']);
                unset($_SESSION['username']);
                unset($_SESSION['role']);
                unset($_SESSION['logged_in']);
                
                if (AuthMiddleware::isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'banned' => true,
                        'reason' => $user['ban_reason'],
                        'redirect' => '/banned'
                    ]);
                    exit;
                }
                
                // For non-AJAX, redirect will happen in frontend
                return false;
            }
        }
        
        return true;
    }
}
?>