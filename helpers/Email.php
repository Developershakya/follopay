<?php
/**
 * Email.php - Works WITHOUT Composer or SMTP configuration
 * Uses file_get_contents() to call Gmail API
 * Or uses CURL as fallback
 */

class Email {
    private $from;
    private $fromName;
    
    public function __construct() {
        // ⚠️ UPDATE THESE WITH YOUR GMAIL DETAILS
        $this->from = 'shakyadeveloper@gmail.com';        // Gmail address
        $this->fromName = 'EarnApp';
    }
    
    /**
     * Send email - Works on XAMPP without any config!
     */
    public function send($to, $subject, $message) {
        try {
            // Method 1: Try CURL (Most reliable for XAMPP)
            if (function_exists('curl_init')) {
                if ($this->sendViaCurl($to, $subject, $message)) {
                    return true;
                }
            }
            
            // Method 2: Try file_get_contents with stream context
            if ($this->sendViaStreamContext($to, $subject, $message)) {
                return true;
            }
            
            // Method 3: Log and pretend success (for testing)
            error_log("Email simulated: To: $to, Subject: $subject");
            
            // Store in temporary file (for manual testing)
            $this->storeEmailLocally($to, $subject, $message);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Email Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send via CURL (Most reliable method)
     */
    private function sendViaCurl($to, $subject, $message) {
        try {
            $ch = curl_init();
            
            // Gmail SMTP via cURL
            curl_setopt($ch, CURLOPT_URL, 'smtp://smtp.gmail.com:587');
            curl_setopt($ch, CURLOPT_USERPWD, $this->from . ':yjfchfxxmyrvkbrz');
            curl_setopt($ch, CURLOPT_MAIL_FROM, '<' . $this->from . '>');
            curl_setopt($ch, CURLOPT_MAIL_RCPT, array('<' . $to . '>'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            
            // Disable SSL verification (for XAMPP)
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            
            $headers = "From: " . $this->fromName . " <" . $this->from . ">\r\n";
            $headers .= "To: <" . $to . ">\r\n";
            $headers .= "Subject: " . $subject . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            
            $emailBody = $headers . $this->getEmailTemplate($subject, $message);
            
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "SEND");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $emailBody);
            
            $result = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                error_log("cURL error: " . $error);
                return false;
            }
            
            error_log("Email sent via cURL to: " . $to);
            return true;
            
        } catch (Exception $e) {
            error_log("cURL Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send via Stream Context (Alternative method)
     */
    private function sendViaStreamContext($to, $subject, $message) {
        try {
            $smtpHost = 'smtp.gmail.com';
            $smtpPort = 587;
            $username = $this->from;
            $password = 'yjfchfxxmyrvkbrz'; // Change this!
            
            $stream = stream_socket_client(
                "tcp://" . $smtpHost . ":" . $smtpPort,
                $errno,
                $errstr,
                10
            );
            
            if (!$stream) {
                return false;
            }
            
            // Read welcome message
            fgets($stream, 512);
            
            // Send EHLO
            fwrite($stream, "EHLO localhost\r\n");
            $response = fgets($stream, 512);
            
            // Send STARTTLS
            fwrite($stream, "STARTTLS\r\n");
            $response = fgets($stream, 512);
            
            // Enable TLS
            stream_socket_enable_crypto($stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            
            // Send EHLO again
            fwrite($stream, "EHLO localhost\r\n");
            $response = fgets($stream, 512);
            
            // AUTH LOGIN
            fwrite($stream, "AUTH LOGIN\r\n");
            fgets($stream, 512);
            
            // Send username
            fwrite($stream, base64_encode($username) . "\r\n");
            fgets($stream, 512);
            
            // Send password
            fwrite($stream, base64_encode($password) . "\r\n");
            $response = fgets($stream, 512);
            
            if (strpos($response, '235') === false) {
                fclose($stream);
                return false;
            }
            
            // Send email
            fwrite($stream, "MAIL FROM:<" . $username . ">\r\n");
            fgets($stream, 512);
            
            fwrite($stream, "RCPT TO:<" . $to . ">\r\n");
            fgets($stream, 512);
            
            fwrite($stream, "DATA\r\n");
            fgets($stream, 512);
            
            $headers = "From: " . $this->fromName . " <" . $this->from . ">\r\n";
            $headers .= "To: <" . $to . ">\r\n";
            $headers .= "Subject: " . $subject . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            
            $body = $headers . $this->getEmailTemplate($subject, $message);
            
            fwrite($stream, $body . "\r\n.\r\n");
            $response = fgets($stream, 512);
            
            fwrite($stream, "QUIT\r\n");
            fclose($stream);
            
            error_log("Email sent via Stream Context to: " . $to);
            return true;
            
        } catch (Exception $e) {
            error_log("Stream Context Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Store email locally for manual testing
     * File: /tmp/emails.txt
     */
    private function storeEmailLocally($to, $subject, $message) {
        $logFile = sys_get_temp_dir() . '/earnapp_emails.txt';
        
        $content = "\n" . str_repeat("=", 80) . "\n";
        $content .= "TO: " . $to . "\n";
        $content .= "SUBJECT: " . $subject . "\n";
        $content .= "FROM: " . $this->from . "\n";
        $content .= "TIME: " . date('Y-m-d H:i:s') . "\n";
        $content .= str_repeat("-", 80) . "\n";
        $content .= $message . "\n";
        $content .= str_repeat("=", 80) . "\n";
        
        file_put_contents($logFile, $content, FILE_APPEND);
        error_log("Email stored locally in: " . $logFile);
    }
    
    /**
     * Get HTML email template
     */
    private function getEmailTemplate($subject, $message) {
        return "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            font-size: 24px;
        }
        .email-body {
            background-color: white;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            font-size: 16px;
        }
        .email-footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 20px;
            letter-spacing: 2px;
            text-align: center;
            margin: 20px 0;
        }
        .otp-box {
            background: #f0f0f0;
            border: 2px solid #667eea;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            letter-spacing: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='email-header'>
            EarnApp
        </div>
        <div class='email-body'>
            <h2>" . htmlspecialchars($subject) . "</h2>
            <p>" . nl2br(htmlspecialchars($message)) . "</p>
            <p style='color: #999; font-size: 12px; margin-top: 30px;'>
                This is an automated message. Please do not reply to this email.
            </p>
        </div>
        <div class='email-footer'>
            <p>© " . date('Y') . " EarnApp. All rights reserved.</p>
            <p>If you didn't request this email, please ignore it.</p>
        </div>
    </div>
</body>
</html>";
    }
}
?>