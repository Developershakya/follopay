<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Manual include (NO composer)
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

/* ========= CONFIG ========= */

$FROM_EMAIL = 'shakyadeveloper@gmail.com';   // Gmail
$APP_PASS   = 'yjfchfxxmyrvkbrz';   // Gmail App Password
$TO_EMAIL   = 'premc3003@gmail.com';

/* ========================== */

try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $FROM_EMAIL;
    $mail->Password   = $APP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Important for free hosting
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ];

    $mail->setFrom($FROM_EMAIL, 'Auto Mail');
    $mail->addAddress($TO_EMAIL);

    $mail->isHTML(true);
    $mail->Subject = 'Auto Mail Working';
    $mail->Body    = '<h3>InfinityFree SMTP test successful</h3>';

    $mail->send();
    echo '✅ Mail sent successfully';

} catch (Exception $e) {
    echo '❌ Error: ' . $mail->ErrorInfo;
}
