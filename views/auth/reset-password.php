<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - EarnApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="asserts/js/toast.js"></script>
</head>
<body class="flex items-center justify-center min-h-screen p-4 bg-gradient-to-br from-blue-500 to-purple-600">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white text-center">
            <img src="https://res.cloudinary.com/dlg5fygaz/image/upload/v1770007061/logo_v89hgg.png" alt="Logo" class="h-12 mx-auto mb-2">
            <h1 class="text-2xl font-bold">Create New Password</h1>
            <p class="opacity-90 mt-2">Enter your new password below</p>
        </div>
        
        <!-- Content -->
        <div class="p-8">
            <div id="invalidToken" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 hidden">
                <p class="text-red-700 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>This reset link is invalid or has expired. <a href="?page=forgot-password" class="font-bold underline">Request a new one</a></span>
                </p>
            </div>
            
            <form id="resetPasswordForm" class="space-y-6">
                <input type="hidden" id="token" name="token" value="">
                
                <div>
                    <label for="password" class="block text-gray-700 font-medium mb-2">New Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="Enter your new password">
                        <button type="button" onclick="togglePassword('password', 'passwordIcon')" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-800 transition">
                            <i id="passwordIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="text-gray-500 text-sm mt-2">
                        Min 6 characters, 1 uppercase letter, 1 number
                    </p>
                </div>

                <!-- Password Strength Indicator -->
                <div>
                    <div id="passwordStrength" class="flex gap-1">
                        <div class="h-1 flex-1 bg-gray-300 rounded"></div>
                        <div class="h-1 flex-1 bg-gray-300 rounded"></div>
                        <div class="h-1 flex-1 bg-gray-300 rounded"></div>
                        <div class="h-1 flex-1 bg-gray-300 rounded"></div>
                        <div class="h-1 flex-1 bg-gray-300 rounded"></div>
                    </div>
                    <p id="strengthText" class="text-xs text-gray-600 mt-1"></p>
                </div>
                
                <div>
                    <label for="confirmPassword" class="block text-gray-700 font-medium mb-2">Confirm Password</label>
                    <div class="relative">
                        <input type="password" id="confirmPassword" name="confirm_password" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="Confirm your new password">
                        <button type="button" onclick="togglePassword('confirmPassword', 'confirmIcon')" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-800 transition">
                            <i id="confirmIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Password Match Indicator -->
                <div id="passwordMatch" class="hidden p-3 rounded-lg text-sm flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <span>Passwords match</span>
                </div>
                
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 rounded-lg font-bold hover:from-blue-600 hover:to-purple-700 transition-all disabled:opacity-50"
                        id="submitBtn">
                    <i class="fas fa-lock mr-2"></i> Reset Password
                </button>
            </form>
            
            <!-- Back to Login -->
            <div class="text-center mt-6 pt-6 border-t">
                <a href="?page=login" class="text-blue-600 hover:text-blue-800 font-bold flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>

    <script>
        // Get token from URL
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        
        if (!token) {
            document.getElementById('invalidToken').classList.remove('hidden');
            document.getElementById('resetPasswordForm').style.display = 'none';
        } else {
            document.getElementById('token').value = token;
        }
        
        // Password toggle
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthBars = document.querySelectorAll('#passwordStrength > div');
        const strengthText = document.getElementById('strengthText');
        
        function checkPasswordStrength(password) {
            let score = 0;
            
            if (password.length >= 6) score++;
            if (password.length >= 8) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            
            return Math.min(score, 5);
        }
        
        function updatePasswordStrength() {
            const password = passwordInput.value;
            const strength = checkPasswordStrength(password);
            
            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
            const texts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            
            strengthBars.forEach((bar, index) => {
                bar.className = `h-1 flex-1 rounded ${index < strength ? colors[strength - 1] : 'bg-gray-300'}`;
            });
            
            if (password) {
                strengthText.textContent = `Strength: ${texts[strength - 1]}`;
            } else {
                strengthText.textContent = '';
            }
        }
        
        passwordInput.addEventListener('input', updatePasswordStrength);
        
        // Password match checker
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const passwordMatchDiv = document.getElementById('passwordMatch');
        
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword && password === confirmPassword) {
                passwordMatchDiv.classList.remove('hidden', 'bg-red-50', 'text-red-700');
                passwordMatchDiv.classList.add('bg-green-50', 'text-green-700');
                passwordMatchDiv.innerHTML = '<i class="fas fa-check-circle"></i> <span>Passwords match</span>';
            } else if (confirmPassword && password !== confirmPassword) {
                passwordMatchDiv.classList.remove('hidden', 'bg-green-50', 'text-green-700');
                passwordMatchDiv.classList.add('bg-red-50', 'text-red-700');
                passwordMatchDiv.innerHTML = '<i class="fas fa-times-circle"></i> <span>Passwords do not match</span>';
            } else {
                passwordMatchDiv.classList.add('hidden');
            }
        }
        
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        passwordInput.addEventListener('input', checkPasswordMatch);
        
        // Form submission
        document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (!password) {
                showToast('Please enter your new password', 3000, 'error');
                return;
            }
            
            if (password.length < 6) {
                showToast('Password must be at least 6 characters', 3000, 'error');
                return;
            }
            
            if (!/[A-Z]/.test(password)) {
                showToast('Password must contain at least one uppercase letter', 3000, 'error');
                return;
            }
            
            if (!/[0-9]/.test(password)) {
                showToast('Password must contain at least one number', 3000, 'error');
                return;
            }
            
            if (password !== confirmPassword) {
                showToast('Passwords do not match', 3000, 'error');
                return;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Resetting...';
            submitBtn.disabled = true;
            
            try {
                const token = document.getElementById('token').value;
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                const response = await fetch('ajax/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        action: 'reset-password',
                        token: token,
                        password: password,
                        confirm_password: confirmPassword
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('✓ Password reset successfully!', 2000, 'success');
                    
                    setTimeout(() => {
                        window.location.href = '?page=login';
                    }, 2000);
                } else {
                    showToast(data.message || data.errors?.[0] || 'Failed to reset password', 3000, 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Network error. Please try again.', 3000, 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>