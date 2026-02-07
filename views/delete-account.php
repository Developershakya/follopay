<?php
require_once 'config/constants.php';
require_once 'helpers/Session.php';

$session = new Session();

// Redirect to login if not authenticated
if (!$session->get('logged_in')) {
    header('Location: ' . BASE_URL . '/pages/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account - EarnApp</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .warning-box h3 {
            color: #856404;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .warning-box ul {
            color: #856404;
            margin-left: 20px;
            font-size: 14px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #333;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input[type="email"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="email"]:focus,
        input[type="tel"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        input[type="checkbox"] {
            margin-top: 4px;
            margin-right: 10px;
            cursor: pointer;
            width: 18px;
            height: 18px;
        }

        .checkbox-label {
            margin: 0;
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }

        .btn-delete:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .btn-cancel {
            background: #e9ecef;
            color: #333;
        }

        .btn-cancel:hover {
            background: #dee2e6;
        }

        .loading {
            display: none;
            text-align: center;
            color: #667eea;
            font-size: 14px;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #0c5aa0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Delete Account</h1>
            <p>Permanently delete your EarnApp account</p>
        </div>

        <div class="warning-box">
            <h3>⚠️ Warning</h3>
            <ul>
                <li>This action cannot be undone</li>
                <li>All your data will be permanently deleted</li>
                <li>Your account cannot be recovered</li>
                <li>Pending payments will be forfeited</li>
            </ul>
        </div>

        <div class="info-box">
            ℹ️ To confirm your identity, please enter the email address and phone number associated with your account.
        </div>

        <form id="deleteAccountForm">
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Enter your registered email"
                    required
                >
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    placeholder="Enter 10-digit phone number"
                    pattern="[0-9]{10}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="reason">Reason for Deletion (Optional)</label>
                <textarea 
                    id="reason" 
                    name="reason" 
                    placeholder="Please tell us why you want to delete your account (helps us improve)"
                ></textarea>
            </div>

            <div class="checkbox-group">
                <input 
                    type="checkbox" 
                    id="acknowledge" 
                    name="acknowledge" 
                    required
                >
                <label for="acknowledge" class="checkbox-label">
                    I understand that my account and all associated data will be permanently deleted and cannot be recovered
                </label>
            </div>

            <div class="button-group">
                <button type="button" class="btn-cancel" onclick="goBack()">Cancel</button>
                <button type="submit" class="btn-delete" id="submitBtn">Delete Account</button>
            </div>

            <div class="loading" id="loading">
                <span class="spinner"></span>
                Processing your request...
            </div>
        </form>

        <a href="?page=dashboard" class="back-link">← Back to Dashboard</a>
    </div>

    <!-- Toast Script -->
    <script src="asserts/js/toast.js"></script>

    <script>
        document.getElementById('deleteAccountForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const reason = document.getElementById('reason').value.trim();
            const acknowledge = document.getElementById('acknowledge').checked;

            // Validation
            if (!email) {
                window.showToast('Please enter your email address', 2500, 'error');
                return;
            }

            if (!phone) {
                window.showToast('Please enter your phone number', 2500, 'error');
                return;
            }

            if (phone.length !== 10) {
                window.showToast('Phone number must be 10 digits', 2500, 'error');
                return;
            }

            if (!acknowledge) {
                window.showToast('Please acknowledge that you understand the consequences', 2500, 'error');
                return;
            }

            // Show loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('submitBtn').disabled = true;

            // Send request
            const formData = new FormData();
            formData.append('action', 'request-deletion');
            formData.append('email', email);
            formData.append('phone', phone);
            if (reason) {
                formData.append('reason', reason);
            }

            fetch('ajax/account.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('submitBtn').disabled = false;

                if (data.success) {
                    window.showToast(data.message, 3000, 'success');
                    document.getElementById('deleteAccountForm').reset();
                    document.getElementById('acknowledge').checked = false;
                    
                    // Redirect after 3 seconds
                    setTimeout(() => {
                        window.location.href = '?page=dashboard';
                    }, 3000);
                } else {
                    window.showToast(data.message || 'Failed to submit deletion request', 2500, 'error');
                }
            })
            .catch(error => {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('submitBtn').disabled = false;
                window.showToast('An error occurred. Please try again.', 2500, 'error');
                console.error('Error:', error);
            });
        });

        function goBack() {
            window.history.back();
        }

        // Only allow numbers in phone field
        document.getElementById('phone').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 10);
        });
    </script>
</body>
</html>