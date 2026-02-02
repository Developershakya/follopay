<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo SITE_NAME ?? 'EarnApp'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Set New Password</h1>
            <p class="text-gray-600 mt-2">Create a strong password for your account</p>
        </div>

        <form id="resetForm" class="space-y-6">
            <div>
                <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="password" id="newPassword" name="new_password" required 
                           class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                           placeholder="Enter new password">
                    <button type="button" onclick="toggleNewPassword()" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-800 transition">
                        <i id="newPasswordIcon" class="fas fa-eye"></i>
                    </button>
                </div>
                <div id="passwordStrength" class="mt-3 space-y-2">
                    <div class="text-xs font-medium text-gray-700">Password Strength:</div>
                    <div class="flex gap-1">
                        <div class="flex-1 h-2 bg-gray-300 rounded" id="strength0"></div>
                        <div class="flex-1 h-2 bg-gray-300 rounded" id="strength1"></div>
                        <div class="flex-1 h-2 bg-gray-300 rounded" id="strength2"></div>
                        <div class="flex-1 h-2 bg-gray-300 rounded" id="strength3"></div>
                    </div>
                    <div class="text-xs text-gray-600">
                        <ul class="list-disc pl-5 space-y-1 mt-2">
                            <li>At least 6 characters</li>
                            <li>Mix of uppercase and lowercase</li>
                            <li>At least one number</li>
                            <li>Special characters recommended</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div>
                <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="password" id="confirmPassword" name="confirm_password" required 
                           class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                           placeholder="Confirm new password">
                    <button type="button" onclick="toggleConfirmPassword()" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-800 transition">
                        <i id="confirmPasswordIcon" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div id="passwordMatch" class="hidden p-3 rounded-lg text-sm"></div>

            <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 rounded-lg font-bold hover:from-blue-600 hover:to-purple-700 transition-all">
                <i class="fas fa-save mr-2"></i>Update Password
            </button>
        </form>

        <div class="mt-6 text-center pt-6 border-t">
            <a href="?page=login" class="text-gray-600 hover:text-gray-800 text-sm">
                <i class="fas fa-sign-in-alt mr-1"></i>Back to Login
            </a>
        </div>

        <div id="message" class="mt-4 hidden text-center p-3 rounded-lg"></div>
    </div>

    <script>
        const newPasswordInput = document.getElementById('newPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const passwordMatchDiv = document.getElementById('passwordMatch');

        // Password strength checker
        newPasswordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            checkPasswordMatch();
        });

        confirmPasswordInput.addEventListener('input', checkPasswordMatch);

        function checkPasswordStrength(password) {
            let score = 0;
            const strengthBars = document.querySelectorAll('[id^="strength"]');
            
            // Reset all bars
            strengthBars.forEach(bar => {
                bar.className = 'flex-1 h-2 bg-gray-300 rounded';
            });

            if (!password) return;

            // Length check
            if (password.length >= 6) score++;
            if (password.length >= 8) score++;

            // Character type checks
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;

            score = Math.min(score, 4); // Max 4 bars

            // Color the appropriate number of bars
            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
            
            for (let i = 0; i < score; i++) {
                strengthBars[i].className = `flex-1 h-2 ${colors[i]} rounded`;
            }
        }

        function checkPasswordMatch() {
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (!newPassword || !confirmPassword) {
                passwordMatchDiv.classList.add('hidden');
                return;
            }

            passwordMatchDiv.classList.remove('hidden');

            if (newPassword === confirmPassword && newPassword.length >= 6) {
                passwordMatchDiv.className = 'p-3 rounded-lg text-sm text-green-600 bg-green-50';
                passwordMatchDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Passwords match';
            } else if (newPassword !== confirmPassword) {
                passwordMatchDiv.className = 'p-3 rounded-lg text-sm text-red-600 bg-red-50';
                passwordMatchDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>Passwords do not match';
            } else {
                passwordMatchDiv.className = 'p-3 rounded-lg text-sm text-orange-600 bg-orange-50';
                passwordMatchDiv.innerHTML = '<i class="fas fa-info-circle mr-2"></i>Password is too short';
            }
        }

        function toggleNewPassword() {
            const icon = document.getElementById('newPasswordIcon');
            if (newPasswordInput.type === 'password') {
                newPasswordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                newPasswordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function toggleConfirmPassword() {
            const icon = document.getElementById('confirmPasswordIcon');
            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                confirmPasswordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Form submission
        document.getElementById('resetForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (newPassword !== confirmPassword) {
                showMessage('Passwords do not match', 'error');
                return;
            }

            if (newPassword.length < 6) {
                showMessage('Password must be at least 6 characters', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'reset-password');
            formData.append('new_password', newPassword);
            formData.append('confirm_password', confirmPassword);

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('ajax/auth.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showMessage('Password updated successfully! Redirecting to login...', 'success');
                    setTimeout(() => {
                        window.location.href = '?page=login';
                    }, 2000);
                } else {
                    showMessage(data.message || 'Failed to update password', 'error');
                }
            } catch(error) {
                console.error('Error:', error);
                showMessage('Network error. Please try again.', 'error');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
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
    </script>
</body>
</html>