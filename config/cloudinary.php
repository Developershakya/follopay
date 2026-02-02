<?php
/**
 * Cloudinary Configuration (No Composer Version)
 */

require_once __DIR__ . '/../helpers/CloudinaryHelper.php';

class CloudinaryConfig {
    private static $instance = null;
    private $helper;
    
    // Your Cloudinary credentials
    private $cloudName = 'dlg5fygaz';  // Replace
    private $apiKey = '367615847344427';        // Replace
    private $apiSecret = 'crLALnaw7pCTdbvOklJ0pzhhg3I';  // Replace
    
    private function __construct() {
        $this->helper = new CloudinaryHelper(
            $this->cloudName,
            $this->apiKey,
            $this->apiSecret
        );
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function upload($filePath, $options = []) {
        return $this->helper->upload($filePath, $options);
    }
    
    public function delete($publicId) {
        return $this->helper->delete($publicId);
    }
}