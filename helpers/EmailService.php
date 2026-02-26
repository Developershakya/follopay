<?php

use PHPMailer\PHPMailer\PHPMailer;

ini_set('display_errors', 1);
error_reporting(E_ALL);



// Manual include (NO composer)
require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';

class EmailService {
    private $mail;
    private $senderEmail;
    private $senderName;
    private function send($to, $subject, $body)
{
    try {
        $this->mail->clearAddresses();

        $this->mail->setFrom($this->senderEmail, $this->senderName);
        $this->mail->addAddress($to);

        $this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body = $body;
        $this->mail->AltBody = strip_tags($body);

        return $this->mail->send();

    } catch (Exception $e) {
        error_log("Email Error: " . $this->mail->ErrorInfo);
        return false;
    }
}
    public function __construct() {
        // Initialize PHPMailer
        $this->mail = new PHPMailer(true);
        
        // Configure SMTP settings
        $this->mail->isSMTP();
        $this->mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com'; // Use environment variable or Gmail
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_ENV['SMTP_USERNAME'] ?? 'follopayhelp@gmail.com';
        $this->mail->Password   = $_ENV['SMTP_PASSWORD'] ?? 'jvrhuvobjeaosqow';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = 587;
        
        // Sender details
        $this->senderEmail = $_ENV['SMTP_FROM_EMAIL'] ?? 'follopayhelp@gmail.com';
        $this->senderName = $_ENV['SMTP_FROM_NAME'] ?? 'Follopay';
    }
    
    /**
     * Send OTP Email
     */
    public function sendOTPEmail($recipientEmail, $username, $otp) {
        try {
            // Clear previous recipients
            $this->mail->clearAddresses();
            
            // Set recipient
            $this->mail->setFrom($this->senderEmail, $this->senderName);
            $this->mail->addAddress($recipientEmail);
            
            // Email content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Verify Your Email - OTP Code';
            
            $htmlBody = $this->getOTPEmailTemplate($username, $otp);
            $this->mail->Body = $htmlBody;
            $this->mail->AltBody = "Your OTP code is: {$otp}. This code will expire in 10 minutes.";
            
            // Send email
            return $this->mail->send();
            
        } catch (Exception $e) {
            error_log("OTP Email Error: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    public function sendPostsDeactivatedNotification($email, $affectedRows)
{
    $subject = "All Posts Deactivated - EarnApp";

    $body = "
    <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; border-radius: 5px 5px 0 0; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #dc3545; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 5px 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>All Posts Deactivated</h2>
                </div>
                <div class='content'>
                    <p>Hello Admin,</p>

                    <div class='info-box'>
                        <strong>Total Posts Affected:</strong> {$affectedRows}<br>
                        <strong>Date:</strong> " . date('Y-m-d H:i:s') . "
                    </div>

                    <p>All posts have been successfully set to <strong>Inactive</strong> by system automation.</p>

                    <p>This is an automated system notification.</p>

                    <p>Regards,<br><strong>EarnApp System</strong></p>
                </div>
                <div class='footer'>
                    <p>&copy; 2026 EarnApp. All rights reserved.</p>
                </div>
            </div>
        </body>
    </html>
    ";

    return $this->send($email, $subject, $body);
}
    /**
     * Send Password Reset Email
     */
    public function sendPasswordResetEmail($recipientEmail, $username, $resetToken) {
        try {
            // Clear previous recipients
            $this->mail->clearAddresses();
            
            // Set recipient
            $this->mail->setFrom($this->senderEmail, $this->senderName);
            $this->mail->addAddress($recipientEmail);
            
            // Generate reset link
            $resetLink = $_ENV['APP_URL'] ?? 'https://follopay.free.nf';
            $resetLink .= '?page=reset-password&token=' . urlencode($resetToken);
            
            // Email content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Reset Your Password - Follopay';
            
            $htmlBody = $this->getPasswordResetEmailTemplate($username, $resetLink);
            $this->mail->Body = $htmlBody;
            $this->mail->AltBody = "Click this link to reset your password: {$resetLink}. This link will expire in 1 hour.";
            
            // Send email
            return $this->mail->send();
            
        } catch (Exception $e) {
            error_log("Password Reset Email Error: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send Password Reset Confirmation Email
     */
    public function sendPasswordResetConfirmation($recipientEmail, $username) {
        try {
            // Clear previous recipients
            $this->mail->clearAddresses();
            
            // Set recipient
            $this->mail->setFrom($this->senderEmail, $this->senderName);
            $this->mail->addAddress($recipientEmail);
            
            // Email content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Password Reset Successful - Follopay';
            
            $htmlBody = $this->getPasswordResetConfirmationTemplate($username);
            $this->mail->Body = $htmlBody;
            $this->mail->AltBody = "Your password has been successfully reset. You can now login with your new password.";
            
            // Send email
            return $this->mail->send();
            
        } catch (Exception $e) {
            error_log("Password Confirmation Email Error: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * OTP Email HTML Template
     */
    private function getOTPEmailTemplate($username, $otp) {
        $logoUrl = 'https://res.cloudinary.com/dlg5fygaz/image/upload/v1770007061/logo_v89hgg.png';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: #f9f9f9;
                    padding: 20px;
                    border-radius: 8px;
                }
                .header {
                    text-align: center;
                    border-bottom: 3px solid #667eea;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                .logo {
                    max-height: 100px;
                    margin-bottom: 10px;
                }
                .content {
                    background: white;
                    padding: 30px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                }
                .otp-box {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 20px;
                    border-radius: 8px;
                    text-align: center;
                    margin: 20px 0;
                    font-size: 28px;
                    font-weight: bold;
                    letter-spacing: 5px;
                }
                .footer {
                    text-align: center;
                    color: #666;
                    font-size: 12px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                }
                .warning {
                    background: #fff3cd;
                    border-left: 4px solid #ffc107;
                    padding: 10px 15px;
                    margin: 20px 0;
                    border-radius: 4px;
                }
            </style>
        </head>
        <body>
            <div class=\"container\">
                <div class=\"header\">
                    <img src=\"{$logoUrl}\" alt=\"Follopay\" class=\"logo\">
                    <h2>Email Verification</h2>
                </div>
                
                <div class=\"content\">
                    <p>Hi <strong>{$username}</strong>,</p>
                    
                    <p>Welcome to Follopay! To complete your registration, please verify your email using the OTP code below:</p>
                    
                    <div class=\"otp-box\">
                        {$otp}
                    </div>
                    
                    <p><strong>This OTP will expire in 10 minutes.</strong></p>
                    
                    <div class=\"warning\">
                        ⚠️ <strong>Never share this code with anyone.</strong> Our team will never ask you for this code.
                    </div>
                    
                    <p>If you didn't sign up for Follopay, please ignore this email.</p>
                    
                    <p>
                        Best regards,<br>
                        <strong>Follopay Team</strong>
                    </p>
                </div>
                
                <div class=\"footer\">
                    <p>© 2026 Follopay. All rights reserved.</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Password Reset Email HTML Template
     */
    private function getPasswordResetEmailTemplate($username, $resetLink) {
        $logoUrl = 'https://res.cloudinary.com/dlg5fygaz/image/upload/v1770007061/logo_v89hgg.png';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: #f9f9f9;
                    padding: 20px;
                    border-radius: 8px;
                }
                .header {
                    text-align: center;
                    border-bottom: 3px solid #667eea;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                .logo {
                    max-height: 50px;
                    margin-bottom: 10px;
                }
                .content {
                    background: white;
                    padding: 30px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                }
                .reset-button {
                    display: inline-block;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 15px 40px;
                    border-radius: 8px;
                    text-decoration: none;
                    font-weight: bold;
                    margin: 20px 0;
                    text-align: center;
                    width: 100%;
                    box-sizing: border-box;
                }
                .reset-button:hover {
                    opacity: 0.9;
                }
                .reset-link {
                    background: #f0f0f0;
                    padding: 10px;
                    word-break: break-all;
                    border-radius: 4px;
                    margin: 15px 0;
                    font-size: 12px;
                }
                .footer {
                    text-align: center;
                    color: #666;
                    font-size: 12px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                }
                .warning {
                    background: #f8d7da;
                    border-left: 4px solid #dc3545;
                    padding: 10px 15px;
                    margin: 20px 0;
                    border-radius: 4px;
                    color: #721c24;
                }
            </style>
        </head>
        <body>
            <div class=\"container\">
                <div class=\"header\">
                    <img src=\"{$logoUrl}\" alt=\"Follopay\" class=\"logo\">
                    <h2>Reset Your Password</h2>
                </div>
                
                <div class=\"content\">
                    <p>Hi <strong>{$username}</strong>,</p>
                    
                    <p>We received a request to reset your password. Click the button below to create a new password:</p>
                    
                    <a href=\"{$resetLink}\" class=\"reset-button\">
                        Reset Password
                    </a>
                    
                    <p>Or copy and paste this link in your browser:</p>
                    <div class=\"reset-link\">{$resetLink}</div>
                    
                    <p><strong>This link will expire in 1 hour.</strong></p>
                    
                    <div class=\"warning\">
                        ⚠️ <strong>If you did not request this, your password will remain unchanged.</strong><br>
                        If you believe your account is compromised, please change your password immediately and contact support.
                    </div>
                    
                    <p>
                        Best regards,<br>
                        <strong>Follopay Team</strong>
                    </p>
                </div>
                
                <div class=\"footer\">
                    <p>© 2026 Follopay. All rights reserved.</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Password Reset Confirmation Email HTML Template
     */
    private function getPasswordResetConfirmationTemplate($username) {
        $logoUrl = 'https://res.cloudinary.com/dlg5fygaz/image/upload/v1770007061/logo_v89hgg.png';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: #f9f9f9;
                    padding: 20px;
                    border-radius: 8px;
                }
                .header {
                    text-align: center;
                    border-bottom: 3px solid #28a745;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                .logo {
                    max-height: 50px;
                    margin-bottom: 10px;
                }
                .content {
                    background: white;
                    padding: 30px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                }
                .success-badge {
                    text-align: center;
                    margin: 20px 0;
                }
                .success-badge svg {
                    width: 60px;
                    height: 60px;
                    color: #28a745;
                }
                .footer {
                    text-align: center;
                    color: #666;
                    font-size: 12px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                }
            </style>
        </head>
        <body>
            <div class=\"container\">
                <div class=\"header\">
                    <img src=\"{$logoUrl}\" alt=\"Follopay\" class=\"logo\">
                    <h2 style=\"color: #28a745;\">✓ Password Reset Successful</h2>
                </div>
                
                <div class=\"content\">
                    <p>Hi <strong>{$username}</strong>,</p>
                    
                    <p>Your password has been successfully reset. You can now login to your account using your new password.</p>
                    
                    <p><strong>If you did not reset your password, please contact our support team immediately.</strong></p>
                    
                    <p>
                        Best regards,<br>
                        <strong>Follopay Team</strong>
                    </p>
                </div>
                
                <div class=\"footer\">
                    <p>© 2026 Follopay. All rights reserved.</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    public function sendDeletionRequestConfirmation($email, $username, $requestId) {
    $subject = "Account Deletion Request Received - EarnApp";
    
    $body = "
    <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #667eea; color: white; padding: 20px; border-radius: 5px 5px 0 0; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #667eea; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 5px 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Account Deletion Request Received</h2>
                </div>
                <div class='content'>
                    <p>Hello <strong>{$username}</strong>,</p>
                    
                    <p>We have received your request to delete your EarnApp account. Our admin team will review your request and contact you within 24-48 hours.</p>
                    
                    <div class='info-box'>
                        <strong>Request Details:</strong><br>
                        Request ID: <code>{$requestId}</code><br>
                        Status: <span style='color: #ff9800;'>Pending Review</span><br>
                        Submitted: " . date('Y-m-d H:i:s') . "
                    </div>
                    
                    <p><strong>Important:</strong></p>
                    <ul>
                        <li>Your account will be permanently deleted if approved</li>
                        <li>All your data and history will be removed</li>
                        <li>This action cannot be reversed</li>
                        <li>Any pending payments will be forfeited</li>
                    </ul>
                    
                    <p>If you did not submit this request or wish to cancel it, please contact our support team immediately.</p>
                    
                    <p>Best regards,<br>The EarnApp Team</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2024 EarnApp. All rights reserved.</p>
                </div>
            </div>
        </body>
    </html>
    ";
    
    return $this->send($email, $subject, $body);
}

/**
 * Send notification to admin about deletion request
 */
public function sendAdminDeletionNotification($username, $email, $phone) {
    $adminEmail = ADMIN_EMAIL; // Define this in your config
    $subject = "[ALERT] New Account Deletion Request - EarnApp Admin";
    
    $body = "
    <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .alert { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin: 10px 0; }
                .details { background: #f9f9f9; padding: 15px; margin: 10px 0; }
                .table { width: 100%; border-collapse: collapse; }
                .table th { background: #667eea; color: white; padding: 10px; text-align: left; }
                .table td { padding: 10px; border-bottom: 1px solid #ddd; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>⚠️ New Account Deletion Request</h2>
                
                <div class='alert'>
                    <strong>A user has requested account deletion. Please review and take action within 48 hours.</strong>
                </div>
                
                <div class='details'>
                    <table class='table'>
                        <tr>
                            <th>Field</th>
                            <th>Value</th>
                        </tr>
                        <tr>
                            <td><strong>Username</strong></td>
                            <td>{$username}</td>
                        </tr>
                        <tr>
                            <td><strong>Email</strong></td>
                            <td>{$email}</td>
                        </tr>
                        <tr>
                            <td><strong>Phone</strong></td>
                            <td>{$phone}</td>
                        </tr>
                        <tr>
                            <td><strong>Submitted At</strong></td>
                            <td>" . date('Y-m-d H:i:s') . "</td>
                        </tr>
                    </table>
                </div>
                
                <p><strong>Action Required:</strong></p>
                <p>Please log in to the admin panel to review and process this deletion request.</p>
                <p><a href='" . BASE_URL . "/admin/deletion-requests.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>View in Admin Panel</a></p>
                
                <hr>
                <p style='color: #999; font-size: 12px;'>This is an automated notification from EarnApp Admin System.</p>
            </div>
        </body>
    </html>
    ";
    
    return $this->send($adminEmail, $subject, $body);
}

/**
 * Send deletion completion email
 */
public function sendDeletionCompletionEmail($email, $username) {
    $subject = "Your Account Has Been Deleted - EarnApp";
    
    $body = "
    <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; border-radius: 5px 5px 0 0; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #28a745; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 5px 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Account Deletion Complete</h2>
                </div>
                <div class='content'>
                    <p>Hello {$username},</p>
                    
                    <div class='info-box'>
                        <strong>✓ Your account has been permanently deleted</strong><br>
                        Completed: " . date('Y-m-d H:i:s') . "
                    </div>
                    
                    <p>Your EarnApp account and all associated data have been permanently removed from our servers.</p>
                    
                    <p><strong>What happens next:</strong></p>
                    <ul>
                        <li>Your profile and personal information have been deleted</li>
                        <li>All transaction history has been removed</li>
                        <li>You can no longer log in to EarnApp</li>
                        <li>If you change your mind, you can create a new account</li>
                    </ul>
                    
                    <p>We're sorry to see you go! If you have any feedback about your experience, please feel free to reach out.</p>
                    
                    <p>Best regards,<br>The EarnApp Team</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2024 EarnApp. All rights reserved.</p>
                </div>
            </div>
        </body>
    </html>
    ";
    
    return $this->send($email, $subject, $body);
}

/**
 * Send deletion rejection email
 */
public function sendDeletionRejectionEmail($email, $username, $reason) {
    $subject = "Your Account Deletion Request Has Been Rejected - EarnApp";
    
    $reasonText = $reason ? htmlspecialchars($reason) : "The request did not meet our deletion criteria.";
    
    $body = "
    <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; border-radius: 5px 5px 0 0; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .info-box { background: #ffe6e6; padding: 15px; margin: 10px 0; border-left: 4px solid #dc3545; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 5px 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Account Deletion Request Rejected</h2>
                </div>
                <div class='content'>
                    <p>Hello {$username},</p>
                    
                    <div class='info-box'>
                        <strong>Your account deletion request has been rejected.</strong><br>
                        Decision Date: " . date('Y-m-d H:i:s') . "
                    </div>
                    
                    <p><strong>Reason:</strong></p>
                    <p>{$reasonText}</p>
                    
                    <p>Your account remains active and you can continue using EarnApp normally.</p>
                    
                    <p>If you believe this decision was made in error or have questions, please contact our support team.</p>
                    
                    <p>Best regards,<br>The EarnApp Team</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2024 EarnApp. All rights reserved.</p>
                </div>
            </div>
        </body>
    </html>
    ";
    
    return $this->send($email, $subject, $body);
}
}
?>