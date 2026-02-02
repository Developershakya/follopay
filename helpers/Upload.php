<?php
class UploadHelper {
    private $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    public function uploadScreenshot($file, $userId) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedImageTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.');
        }
        
        // Validate file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File too large. Maximum size is 5MB.');
        }
        
        // Create directory if not exists
        $userDir = SCREENSHOT_PATH . 'user_' . $userId . '/';
        if (!file_exists($userDir)) {
            mkdir($userDir, 0777, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'screenshot_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $filepath = $userDir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to move uploaded file.');
        }
        
        // Compress image
        $this->compressImage($filepath);
        
        // Return relative path
        return 'uploads/screenshots/user_' . $userId . '/' . $filename;
    }
    
    private function compressImage($sourcePath) {
        $info = getimagesize($sourcePath);
        $mime = $info['mime'];
        
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                imagejpeg($image, $sourcePath, 75);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                imagepng($image, $sourcePath, 6);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($sourcePath);
                imagegif($image, $sourcePath);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($sourcePath);
                imagewebp($image, $sourcePath, 75);
                break;
        }
        
        if (isset($image)) {
            imagedestroy($image);
        }
    }
    
    public function uploadProof($file, $withdrawalId) {
        // Similar to uploadScreenshot but for payment proofs
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedImageTypes)) {
            throw new Exception('Invalid file type. Only images are allowed.');
        }
        
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File too large. Maximum size is 5MB.');
        }
        
        $proofDir = PROOF_PATH . 'withdrawal_' . $withdrawalId . '/';
        if (!file_exists($proofDir)) {
            mkdir($proofDir, 0777, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'proof_' . time() . '.' . $extension;
        $filepath = $proofDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to move uploaded file.');
        }
        
        $this->compressImage($filepath);
        
        return 'uploads/proofs/withdrawal_' . $withdrawalId . '/' . $filename;
    }
    
    public function deleteFile($filepath) {
        $fullPath = dirname(__DIR__) . '/' . $filepath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
            
            // Try to remove empty directory
            $dir = dirname($fullPath);
            if (count(scandir($dir)) == 2) { // Only . and .. remain
                rmdir($dir);
            }
            return true;
        }
        return false;
    }
    
    public function validateImage($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        return in_array($mimeType, $this->allowedImageTypes) && 
               $file['size'] <= $this->maxFileSize;
    }
}
?>