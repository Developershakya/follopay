<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - <?php echo SITE_NAME ?? 'EarnApp'; ?></title>
    <?php include 'header.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .otp-input {
            width: 50px;
            height: 50px;
            font-size: 24px;
            text-align: center;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            margin: 0 5px;
            font-weight: bold;
        }
        .otp-input:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-envelope text-blue-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Verify Your Email</h1>
            <p class="text-gray-600 mt-2">We've sent a verification code to your email</p>
        </div>

        <form id="verifyForm" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Enter 6-Digit OTP</label>
                <div class="flex justify-center gap-2">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" data-index="0">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" data-index="1">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" data-index="2">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" data-index="3">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" data-index="4">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" data-index="5">
                </div>
                <input type="hidden" id="otpValue" name="otp" value="">
            </div>

            <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 rounded-lg font-bold hover:from-blue-600 hover:to-purple-700 transition-all">
                <i class="fas fa-check mr-2"></i>Verify Email
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-600 text-sm">Didn't receive OTP?</p>
            <button type="button" onclick="resendOTP()" 
                    class="text-blue-600 font-bold hover:text-blue-800 mt-2">
                <i class="fas fa-redo mr-1"></i>Resend OTP
            </button>
            <div id="timerDiv" class="text-xs text-gray-500 mt-2"></div>
        </div>

        <div class="mt-6 text-center pt-6 border-t">
            <a href="?page=register" class="text-gray-600 hover:text-gray-800 text-sm">
                <i class="fas fa-arrow-left mr-1"></i>Back to Registration
            </a>
        </div>

        <div id="message" class="mt-4 hidden text-center p-3 rounded-lg"></div>
    </div>

    <script>
        let resendTimer = 0;
        const otpInputs = document.querySelectorAll('.otp-input');

        // OTP input handling
        otpInputs.forEach((input, index) => {
            input.addEventListener('keyup', function(e) {
                if (this.value && /^[0-9]$/.test(this.value)) {
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                } else if (e.key === 'Backspace' && index > 0) {
                    otpInputs[index - 1].focus();
                }

                // Update hidden input
                const otp = Array.from(otpInputs).map(i => i.value).join('');
                document.getElementById('otpValue').value = otp;

                // Auto submit if all fields filled
                if (otp.length === 6) {
                    document.getElementById('verifyForm').dispatchEvent(new Event('submit'));
                }
            });

            input.addEventListener('input', function(e) {
                if (!/^[0-9]$/.test(this.value)) {
                    this.value = '';
                }
            });
        });

        // Form submission
        document.getElementById('verifyForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const otp = document.getElementById('otpValue').value;

            if (otp.length !== 6) {
                showMessage('Please enter all 6 digits', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'verify-otp');
            formData.append('otp', otp);

            try {
                const response = await fetch('ajax/auth.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showMessage('Email verified successfully! Logging you in...', 'success');
                    setTimeout(() => {
                        window.location.href = '?page=dashboard';
                    }, 1500);
                } else {
                    showMessage(data.message || 'Verification failed', 'error');
                }
            } catch(error) {
                console.error('Error:', error);
                showMessage('Network error. Please try again.', 'error');
            }
        });

        function showMessage(message, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.className = 'mt-4 p-3 rounded-lg';
            
            if (type === 'success') {
                messageDiv.classList.add('text-green-600', 'bg-green-50');
                messageDiv.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${message}`;
            } else {
                messageDiv.classList.add('text-red-600', 'bg-red-50');
                messageDiv.innerHTML = `<i class="fas fa-exclamation-circle mr-2"></i>${message}`;
            }
            messageDiv.classList.remove('hidden');
        }

        function startResendTimer() {
            resendTimer = 60;
            updateResendButton();
        }

        function updateResendButton() {
            const timerDiv = document.getElementById('timerDiv');
            const resendBtn = document.querySelector('button[onclick="resendOTP()"]');

            if (resendTimer > 0) {
                timerDiv.textContent = `Resend in ${resendTimer}s`;
                resendBtn.disabled = true;
                resendBtn.classList.add('opacity-50', 'cursor-not-allowed');
                resendTimer--;
                setTimeout(updateResendButton, 1000);
            } else {
                timerDiv.textContent = '';
                resendBtn.disabled = false;
                resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

        async function resendOTP() {
            const formData = new FormData();
            formData.append('action', 'resend-otp');

            try {
                const response = await fetch('ajax/auth.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showMessage('OTP resent successfully!', 'success');
                    startResendTimer();
                    // Clear OTP fields
                    otpInputs.forEach(input => input.value = '');
                    document.getElementById('otpValue').value = '';
                    otpInputs[0].focus();
                } else {
                    showMessage(data.message || 'Failed to resend OTP', 'error');
                }
            } catch(error) {
                console.error('Error:', error);
                showMessage('Network error. Please try again.', 'error');
            }
        }

        // Start timer on page load
        startResendTimer();
    </script>
</body>
</html>