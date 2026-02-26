<?php
/**
 * ajax/slider.php
 * Complete Slider API
 *
 * PUBLIC  : get_slides
 * ADMIN   : admin_get_all, add_url, add_upload, toggle, delete
 */

require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../middleware/AuthMiddleware.php';

header('Content-Type: application/json');

// Get action from either GET or POST
$action = '';
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} elseif (isset($_POST['action'])) {
    $action = $_POST['action'];
}

// Debug - remove in production
error_log("Slider API called with action: " . $action);

// ══════════════════════════════════════════
// PUBLIC — dashboard slider fetch
// ══════════════════════════════════════════
if ($action === 'get_slides') {
    try {
        $db = Database::getInstance()->getConnection();
        
        // Check if table exists first
        $tableCheck = $db->query("SHOW TABLES LIKE 'slider_images'");
        if ($tableCheck->rowCount() == 0) {
            echo json_encode(['success' => true, 'slides' => []]);
            exit;
        }
        
        $stmt = $db->query("
            SELECT id, title, image_url, redirect_url, sort_order
            FROM slider_images
            WHERE is_active = 1
            ORDER BY sort_order ASC, id ASC
        ");
        echo json_encode(['success' => true, 'slides' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        error_log("Slider API error (get_slides): " . $e->getMessage());
        echo json_encode(['success' => true, 'slides' => []]);
    }
    exit;
}

// ══════════════════════════════════════════
// ADMIN ONLY
// ══════════════════════════════════════════
AuthMiddleware::handle();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'DB connection error']);
    exit;
}

// Handle admin actions
switch ($action) {

    // ── Admin: get all slides ──
    case 'admin_get_all':
        try {
            // Check if table exists
            $tableCheck = $db->query("SHOW TABLES LIKE 'slider_images'");
            if ($tableCheck->rowCount() == 0) {
                echo json_encode(['success' => true, 'slides' => []]);
                break;
            }
            
            $stmt = $db->query("
                SELECT id, title, image_url, cloudinary_public_id,
                       redirect_url, sort_order, is_active, created_at
                FROM slider_images
                ORDER BY sort_order ASC, id ASC
            ");
            echo json_encode(['success' => true, 'slides' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (Exception $e) {
            error_log("Slider API error (admin_get_all): " . $e->getMessage());
            echo json_encode(['success' => true, 'slides' => []]);
        }
        break;

    // ── Add via URL ──
    case 'add_url':
        $title    = trim($_POST['title']       ?? '');
        $imageUrl = trim($_POST['image_url']    ?? '');
        $redirect = trim($_POST['redirect_url'] ?? '');
        $sort     = intval($_POST['sort_order'] ?? 0);

        if (empty($imageUrl)) {
            echo json_encode(['success' => false, 'message' => 'Image URL is required']);
            break;
        }

        // Validate URL
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid image URL format']);
            break;
        }

        try {
            $stmt = $db->prepare("
                INSERT INTO slider_images (title, image_url, redirect_url, sort_order, is_active)
                VALUES (?, ?, ?, ?, 1)
            ");
            $stmt->execute([$title, $imageUrl, $redirect ?: null, $sort]);
            echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
        } catch (Exception $e) {
            error_log("Slider API error (add_url): " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    // ── Add via server upload ──
    case 'add_upload':
        $title    = trim($_POST['title']                ?? '');
        $imageUrl = trim($_POST['image_url']             ?? '');
        $publicId = trim($_POST['cloudinary_public_id']  ?? '');
        $redirect = trim($_POST['redirect_url']          ?? '');
        $sort     = intval($_POST['sort_order']          ?? 0);

        if (empty($imageUrl)) {
            echo json_encode(['success' => false, 'message' => 'Image URL is required']);
            break;
        }

        try {
            $stmt = $db->prepare("
                INSERT INTO slider_images
                    (title, image_url, cloudinary_public_id, redirect_url, sort_order, is_active)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$title, $imageUrl, $publicId ?: null, $redirect ?: null, $sort]);
            echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
        } catch (Exception $e) {
            error_log("Slider API error (add_upload): " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    // ── Toggle active/inactive ──
    case 'toggle':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { 
            echo json_encode(['success' => false, 'message' => 'Invalid ID']); 
            break; 
        }
        
        try {
            $db->prepare("UPDATE slider_images SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("Slider API error (toggle): " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        break;

    // ── Delete ──
    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { 
            echo json_encode(['success' => false, 'message' => 'Invalid ID']); 
            break; 
        }

        try {
            // Delete from Cloudinary if has public_id
            $s = $db->prepare("SELECT cloudinary_public_id FROM slider_images WHERE id = ?");
            $s->execute([$id]);
            $row = $s->fetch(PDO::FETCH_ASSOC);
            
            if ($row && !empty($row['cloudinary_public_id'])) {
                try {
                    require_once '../config/cloudinary.php';
                    if (class_exists('CloudinaryConfig')) {
                        CloudinaryConfig::getInstance()->delete($row['cloudinary_public_id']);
                    }
                } catch (Exception $e) {
                    error_log('Cloudinary slider delete: ' . $e->getMessage());
                }
            }

            $db->prepare("DELETE FROM slider_images WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("Slider API error (delete): " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        break;

    // ── Handle upload action (this is for the file upload from your form) ──
    case 'upload_image':
        // This handles the FormData upload from addByUpload() function
        if (!isset($_FILES['slider_image']) || $_FILES['slider_image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
            break;
        }

        $file = $_FILES['slider_image'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, WEBP, GIF']);
            break;
        }

        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File too large. Max 5MB']);
            break;
        }

        // Here you would typically upload to Cloudinary or your server
        // For now, let's return a mock response or implement basic upload
        
        try {
            // Example: Save to local server first
            $uploadDir = '../uploads/slider/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '', $file['name']);
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Generate URL for the uploaded file
                $baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
                $baseUrl .= str_replace('/ajax', '', dirname($_SERVER['SCRIPT_NAME']));
                $imageUrl = $baseUrl . '/uploads/slider/' . $filename;
                
                // Insert into database
                $title    = trim($_POST['title'] ?? '');
                $redirect = trim($_POST['redirect_url'] ?? '');
                $sort     = intval($_POST['sort_order'] ?? 0);
                
                $stmt = $db->prepare("
                    INSERT INTO slider_images (title, image_url, redirect_url, sort_order, is_active)
                    VALUES (?, ?, ?, ?, 1)
                ");
                $stmt->execute([$title, $imageUrl, $redirect ?: null, $sort]);
                
                echo json_encode([
                    'success' => true, 
                    'id' => $db->lastInsertId(),
                    'image_url' => $imageUrl,
                    'message' => 'File uploaded successfully'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
            }
        } catch (Exception $e) {
            error_log("Slider upload error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()]);
        }
        break;

    default:
        error_log("Slider API - Invalid action: " . $action);
        echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
}
?>