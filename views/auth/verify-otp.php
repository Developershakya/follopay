<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - EarnApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="asserts/js/toast.js"></script>
    <style>
        .otp-input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .otp-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .otp-input.filled {
            border-color: #667eea;
            color: #667eea;
        }
        
        .timer {
            font-size: 14px;
            font-weight: bold;
            color: #667eea;
        }
        
        .timer.warning {
            color: #f59e0b;
        }
        
        .timer.danger {
            color: #ef4444;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 bg-gradient-to-br from-blue-500 to-purple-600">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white text-center">
            <img src="https://res.cloudinary.com/dlg5fygaz/image/upload/v1770007061/logo_v89hgg.png" alt="Logo" class="h-12 mx-auto mb-2">
            <h1 class="text-2xl font-bold">Verify Email</h1>
            <p class="opacity-90 mt-2">Enter the OTP code sent to your email</p>
        </div>
        
        <!-- Content -->
        <div class="p-8">
            <div id="emailDisplay" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-gray-700 text-center">
                    We've sent a 6-digit code to <br>
                    <strong id="displayEmail" class="text-blue-600"></strong>
                </p>
            </div>
            
            <!-- OTP Input -->
            <form id="otpForm" class="space-y-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-4 text-center">Enter OTP Code</label>
                    <div id="otpInputs" class="flex justify-center gap-2">
                        <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0">
                        <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0">
                        <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0">
                        <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0">
                        <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0">
                        <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0">
                    </div>
                    <input type="hidden" id="otpCode" name="otp" value="">
                    <input type="hidden" id="emailInput" name="email" value="">
                </div>
                
                <!-- Timer -->
                <div class="text-center">
                    <p class="text-gray-600 text-sm">
                        OTP expires in <span id="timer" class="timer">10:00</span>
                    </p>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 rounded-lg font-bold hover:from-blue-600 hover:to-purple-700 transition-all disabled:opacity-50"
                        id="submitBtn">
                    <i class="fas fa-check-circle mr-2"></i> Verify OTP
                </button>
            </form>
            
            <!-- Resend OTP -->
            <div class="text-center mt-6 pt-6 border-t">
                <p class="text-gray-600 text-sm mb-4">Didn't receive the code?</p>
                <button type="button" id="resendBtn"
                        class="text-blue-600 hover:text-blue-800 font-bold text-sm disabled:opacity-50">
                    <i class="fas fa-redo mr-2"></i> Resend OTP (<span id="resendTimer">60</span>s)
                </button>
            </div>
            
            <!-- Change Email -->
            <div class="text-center mt-4">
                <a href="?page=register" class="text-gray-500 hover:text-blue-600 text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Change email
                </a>
            </div>
        </div>
    </div>

    <script>
        // Get email from URL or session storage
        const email = new URLSearchParams(window.location.search).get('email') || 
                      sessionStorage.getItem('registration_email') || '';
        
        if (!email) {
            showToast('Email not found. Please register again.', 3000, 'error');
            setTimeout(() => window.location.href = '?page=register', 2000);
        }
        
        // Display email
        document.getElementById('emailInput').value = email;
        document.getElementById('displayEmail').textContent = email;
        
        // OTP Input Handler
        const otpInputs = document.querySelectorAll('.otp-input');
        
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                // Only allow numbers
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value) {
                    this.classList.add('filled');
                    // Move to next input
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                } else {
                    this.classList.remove('filled');
                }
                
                updateOtpValue();
            });
            
            // Backspace handling
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
            
            // Paste handling
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text');
                const digits = pastedData.replace(/[^0-9]/g, '').split('');
                
                digits.forEach((digit, i) => {
                    if (index + i < otpInputs.length) {
                        otpInputs[index + i].value = digit;
                        otpInputs[index + i].classList.add('filled');
                    }
                });
                
                // Focus last input
                if (index + digits.length - 1 < otpInputs.length) {
                    otpInputs[index + digits.length - 1].focus();
                } else {
                    otpInputs[otpInputs.length - 1].focus();
                }
                
                updateOtpValue();
            });
        });
        
        function updateOtpValue() {
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            document.getElementById('otpCode').value = otp;
        }
        
        // Timer for OTP expiry (10 minutes)
        let timeRemaining = 600; // 10 minutes in seconds
        const timerElement = document.getElementById('timer');
        
        const timerInterval = setInterval(() => {
            timeRemaining--;
            
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            const timeStr = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            timerElement.textContent = timeStr;
            
            // Update color based on time remaining
            if (timeRemaining <= 60) {
                timerElement.classList.add('danger');
                timerElement.classList.remove('warning');
            } else if (timeRemaining <= 180) {
                timerElement.classList.add('warning');
                timerElement.classList.remove('danger');
            }
            
            // Expire OTP
            if (timeRemaining <= 0) {
                clearInterval(timerInterval);
                timerElement.textContent = 'Expired';
                document.getElementById('submitBtn').disabled = true;
                showToast('OTP has expired. Please request a new one.', 3000, 'error');
            }
        }, 1000);
        
        // Resend OTP Timer (60 seconds)
        const resendBtn = document.getElementById('resendBtn');
        const resendTimer = document.getElementById('resendTimer');
        let resendCooldown = 0;
        
        function startResendTimer() {
            resendCooldown = 60;
            resendBtn.disabled = true;
            
            const resendInterval = setInterval(() => {
                resendCooldown--;
                resendTimer.textContent = resendCooldown;
                
                if (resendCooldown <= 0) {
                    clearInterval(resendInterval);
                    resendBtn.disabled = false;
                    resendTimer.textContent = '60';
                }
            }, 1000);
        }
        
        // Resend OTP Handler
        resendBtn.addEventListener('click', async function() {
            if (resendBtn.disabled) return;
            
            const originalText = resendBtn.innerHTML;
            resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending...';
            resendBtn.disabled = true;
            
            try {
                const response = await fetch('ajax/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        action: 'refresh-otp',
                        email: email
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('New OTP sent to your email!', 3000, 'success');
                    
                    // Reset timer
                    clearInterval(timerInterval);
                    timeRemaining = 600;
                    timerElement.classList.remove('warning', 'danger');
                    document.getElementById('submitBtn').disabled = false;
                    
                    // Restart timer
                    startResendTimer();
                } else {
                    showToast(data.message || 'Failed to send OTP.', 3000, 'error');
                    resendBtn.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Network error. Please try again.', 3000, 'error');
                resendBtn.disabled = false;
            } finally {
                resendBtn.innerHTML = originalText;
            }
        });
        
        // Form Submission
        document.getElementById('otpForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const otp = document.getElementById('otpCode').value;
            
            if (otp.length !== 6) {
                showToast('Please enter all 6 digits', 3000, 'error');
                return;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Verifying...';
            submitBtn.disabled = true;
            
            try {
                const otp = document.getElementById('otpCode').value;
                const emailVal = document.getElementById('emailInput').value;
                
                const response = await fetch('ajax/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        action: 'verify-otp',
                        email: emailVal,
                        otp: otp
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('✓ Email verified successfully!', 2000, 'success');
                    
                    setTimeout(() => {
                        window.location.href = '?page=dashboard';
                    }, 2000);
                } else {
                    showToast(data.message || 'Invalid OTP. Please try again.', 3000, 'error');
                    
                    // Clear OTP inputs
                    otpInputs.forEach(input => {
                        input.value = '';
                        input.classList.remove('filled');
                    });
                    document.getElementById('otpCode').value = '';
                    otpInputs[0].focus();
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Network error. Please try again.', 3000, 'error');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
        
        // Auto-submit when all digits are entered
        function checkAutoSubmit() {
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            if (otp.length === 6) {
                // Don't auto-submit, let user click the button
                // This is better UX
            }
        }
        
        // Focus first input on load
        otpInputs[0].focus();
    </script>
</body>
</html>