<?php
// Session configuration
session_start();

// Site settings
define('SITE_NAME', 'FolloPay');
define('SITE_URL', 'http://localhost/follo');
define('TIMEZONE', 'Asia/Kolkata');
date_default_timezone_set(TIMEZONE);
define('CRON_SECRET_KEY', 'my_super_secret_12345');
// Upload paths
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
define('SCREENSHOT_PATH', UPLOAD_PATH . 'screenshots/');
define('PROOF_PATH', UPLOAD_PATH . 'proofs/');

// Create directories if they don't exist
if (!file_exists(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0777, true);
if (!file_exists(SCREENSHOT_PATH)) mkdir(SCREENSHOT_PATH, 0777, true);
if (!file_exists(PROOF_PATH)) mkdir(PROOF_PATH, 0777, true);

// Application settings
define('COMMENT_LOCK_TIME', 300); // 5 minutes in seconds
define('HEARTBEAT_INTERVAL', 30); // 30 seconds
define('WITHDRAW_INSTANT_MIN', 5);
define('WITHDRAW_DURATION_MIN', 10);
define('WITHDRAW_INSTANT_CHARGE', 20); // 20%

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');

// Response codes
define('RESPONSE_SUCCESS', 200);
define('RESPONSE_ERROR', 400);
define('RESPONSE_UNAUTHORIZED', 401);
define('RESPONSE_FORBIDDEN', 403);
define('RESPONSE_NOT_FOUND', 404);
define('RESPONSE_VALIDATION_ERROR', 422);
?>