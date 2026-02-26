<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/EmailService.php';
require_once __DIR__ . '/../config/constants.php';

header('Content-Type: application/json');

// ✅ Secret Check
$providedKey = $_GET['key'] ?? '';

if ($providedKey !== CRON_SECRET_KEY) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {

    $db = Database::getInstance()->getConnection();
    $emailService = new EmailService();

    $stmt = $db->prepare("
        UPDATE posts 
        SET status = 'inactive'
        WHERE status != 'inactive'
    ");

    $stmt->execute();
    $affectedRows = $stmt->rowCount();

    $emails = [
        'kg6174923@gmail.com',
        'example1@gmail.com'
    ];

    foreach ($emails as $email) {
        $emailService->sendPostsDeactivatedNotification($email, $affectedRows);
    }

    echo json_encode([
        'success' => true,
        'affected_rows' => $affectedRows
    ]);

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error'
    ]);
}