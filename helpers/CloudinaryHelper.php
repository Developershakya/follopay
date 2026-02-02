<?php
/**
 * Simple Cloudinary Upload Helper (No Composer Required)
 * Uses Cloudinary Upload API directly
 */

class CloudinaryHelper {
    private $cloudName;
    private $apiKey;
    private $apiSecret;
    
    public function __construct($cloudName, $apiKey, $apiSecret) {
        $this->cloudName = $cloudName;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }
    
    /**
     * Upload image to Cloudinary
     */
    public function upload($filePath, $options = []) {
        try {
            // Generate timestamp
            $timestamp = time();
            
            // Default folder
            $folder = isset($options['folder']) ? $options['folder'] : 'follopay/screenshots';
            $publicId = isset($options['public_id']) ? $options['public_id'] : null;
            
            // Prepare upload parameters
            $params = [
                'timestamp' => $timestamp,
                'folder' => $folder
            ];
            
            if ($publicId) {
                $params['public_id'] = $publicId;
            }
            
            // Generate signature
            $signature = $this->generateSignature($params);
            
            // Prepare POST data
            $postData = [
                'file' => new CURLFile($filePath),
                'timestamp' => $timestamp,
                'folder' => $folder,
                'api_key' => $this->apiKey,
                'signature' => $signature
            ];
            
            if ($publicId) {
                $postData['public_id'] = $publicId;
            }
            
            // Upload URL
            $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";
            
            // Initialize cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            // Execute request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Parse response
            $result = json_decode($response, true);
            
            if ($httpCode == 200 && isset($result['secure_url'])) {
                return [
                    'success' => true,
                    'url' => $result['secure_url'],
                    'public_id' => $result['public_id']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => isset($result['error']['message']) ? $result['error']['message'] : 'Upload failed'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete image from Cloudinary
     */
    public function delete($publicId) {
        try {
            $timestamp = time();
            
            // Prepare parameters
            $params = [
                'public_id' => $publicId,
                'timestamp' => $timestamp
            ];
            
            // Generate signature
            $signature = $this->generateSignature($params);
            
            // Prepare POST data
            $postData = [
                'public_id' => $publicId,
                'timestamp' => $timestamp,
                'api_key' => $this->apiKey,
                'signature' => $signature
            ];
            
            // Delete URL
            $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy";
            
            // Initialize cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            // Execute request
            $response = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            return isset($result['result']) && $result['result'] === 'ok';
            
        } catch (Exception $e) {
            error_log('Cloudinary delete error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate signature for Cloudinary API
     */
    private function generateSignature($params) {
        // Sort parameters
        ksort($params);
        
        // Build string to sign
        $stringToSign = '';
        foreach ($params as $key => $value) {
            $stringToSign .= $key . '=' . $value . '&';
        }
        $stringToSign = rtrim($stringToSign, '&');
        
        // Append API secret
        $stringToSign .= $this->apiSecret;
        
        // Generate SHA-1 hash
        return sha1($stringToSign);
    }
}