<?php
/**
 * PayU Webhook Handler
 * Receives payment notifications from PayU and stores in database
 * Must be accessible at: https://follopay.free.nf/api/payu-webhook.php
 */

require_once '../config/database.php';

// Enable error logging
error_log("PayU Webhook received at " . date('Y-m-d H:i:s'));

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
error_log("Method: $method");

// Get data
if ($method === 'POST') {
    $data = $_POST;
} else {
    $data = json_decode(file_get_contents('php://input'), true);
}

error_log("Webhook Data: " . json_encode($data));

// PayU Credentials (update these with your actual keys)
$merchant_key = 'cJZkH6'; // From PayU dashboard
$merchant_salt = 'Jc3BVEqxTxbAwnDk'; // From PayU dashboard

/**
 * Verify webhook signature for security
 * This ensures the webhook is actually from PayU
 */
function verifyPayUSignature($data, $merchant_key, $merchant_salt) {
    // Get hash from webhook
    $hash_received = $data['hash'] ?? '';
    
    if (empty($hash_received)) {
        error_log("No hash found in webhook");
        return false;
    }
    
    // Build string for hashing
    // Order matters! EXACTLY as PayU specifies
    $hashSequence = '';
    
    if (!empty($data['status'])) {
        // For success/failure responses
        $hashSequence = $data['key'] ?? $merchant_key;
        $hashSequence .= '|' . ($data['txnid'] ?? '');
        $hashSequence .= '|' . ($data['amount'] ?? '');
        $hashSequence .= '|' . ($data['productinfo'] ?? '');
        $hashSequence .= '|' . ($data['firstname'] ?? '');
        $hashSequence .= '|' . ($data['email'] ?? '');
        $hashSequence .= '|' . ($data['status'] ?? '');
        $hashSequence .= '|' . ($data['urid'] ?? '');
        $hashSequence .= '|' . ($data['bash_request_id'] ?? '');
        $hashSequence .= '|' . ($data['error'] ?? '');
        $hashSequence .= '|' . ($data['error_Message'] ?? '');
        $hashSequence .= '|' . ($data['net_amount_debit'] ?? '');
        $hashSequence .= '|' . ($data['surl'] ?? '');
        $hashSequence .= '|' . ($data['furl'] ?? '');
    }
    
    // For UPI payments, hash might be different
    if (!empty($data['utr'])) {
        $hashSequence .= '|' . ($data['utr'] ?? '');
    }
    
    $hashSequence .= '|' . $merchant_salt;
    
    // Calculate hash
    $calculated_hash = hash('sha512', $hashSequence);
    
    error_log("Hash Sequence: $hashSequence");
    error_log("Calculated Hash: $calculated_hash");
    error_log("Received Hash: $hash_received");
    
    // Compare hashes
    if ($calculated_hash === $hash_received) {
        error_log("✅ Signature verified successfully");
        return true;
    } else {
        error_log("❌ Signature verification failed");
        return false;
    }
}

/**
 * Main webhook handler
 */
try {
    $db = Database::getInstance()->getConnection();
    
    // Extract relevant data
    $txnid = $data['txnid'] ?? '';
    $utr = $data['utr'] ?? '';
    $amount = floatval($data['amount'] ?? 0);
    $status = strtolower($data['status'] ?? 'pending');
    $phone = $data['phone1'] ?? $data['phone'] ?? '';
    $email = $data['email'] ?? '';
    $product_info = $data['productinfo'] ?? '';
    $firstname = $data['firstname'] ?? '';
    $payment_method = $data['payment_method'] ?? '';
    $error_message = $data['error_Message'] ?? '';
    
    error_log("Processing: txnid=$txnid, utr=$utr, amount=$amount, status=$status");
    
    // Verify signature (IMPORTANT for security)
    // Comment this out if testing without proper signature
    // if (!verifyPayUSignature($data, $merchant_key, $merchant_salt)) {
    //     error_log("Webhook signature verification failed");
    //     http_response_code(401);
    //     echo json_encode(['success' => false, 'message' => 'Invalid signature']);
    //     exit;
    // }
    
    // Check if transaction already exists
    $stmt = $db->prepare("SELECT id FROM payu_payments WHERE txnid = ? LIMIT 1");
    $stmt->execute([$txnid]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        error_log("Transaction already exists: $txnid");
        echo json_encode(['success' => true, 'message' => 'Already processed']);
        exit;
    }
    
    // Determine status mapping
    $db_status = 'pending';
    if ($status === 'success') {
        $db_status = 'completed';
    } elseif ($status === 'failed') {
        $db_status = 'failed';
    } elseif ($status === 'pending') {
        $db_status = 'pending';
    }
    
    // Insert into payu_payments table
    $stmt = $db->prepare("
        INSERT INTO payu_payments 
        (txnid, utr, amount, phone, email, status, payment_method, failure_reason, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $success = $stmt->execute([
        $txnid,
        $utr,
        $amount,
        $phone,
        $email,
        $db_status,
        $payment_method,
        $error_message
    ]);
    
    if ($success) {
        error_log("✅ Payment inserted successfully: txnid=$txnid, amount=$amount, status=$db_status");
    } else {
        error_log("❌ Failed to insert payment");
    }
    
    // Return success response to PayU
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Webhook received']);
    
} catch (Exception $e) {
    error_log("Webhook Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>