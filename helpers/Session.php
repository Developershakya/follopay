<?php
class Session {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public function has($key) {
        return isset($_SESSION[$key]);
    }
    
    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    public function flash($key, $value) {
        $_SESSION['flash_' . $key] = $value;
    }
    
    public function getFlash($key, $default = null) {
        $value = $_SESSION['flash_' . $key] ?? $default;
        $this->remove('flash_' . $key);
        return $value;
    }
    
    public function destroy() {
        session_destroy();
    }
    
    public function regenerate() {
        session_regenerate_id(true);
    }
    
    public function setCSRFToken() {
        if (!$this->has('csrf_token')) {
            $this->set('csrf_token', bin2hex(random_bytes(32)));
        }
        return $this->get('csrf_token');
    }
    
    public function validateCSRFToken($token) {
        return hash_equals($this->get('csrf_token', ''), $token);
    }
}
?>