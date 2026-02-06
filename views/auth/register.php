<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="asserts/js/toast.js"></script>
    <title>Register - EarnApp</title>
</head>
<body>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-500 to-purple-600 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white text-center">
            <img src="https://res.cloudinary.com/dlg5fygaz/image/upload/v1770007061/logo_v89hgg.png" alt="Logo" class="h-12 mx-auto mb-2">
            <h1 class="text-3xl font-bold">Create Account</h1>
            <p class="opacity-90 mt-2">Start earning money today!</p>
        </div>
        
        <!-- Registration Form -->
        <div class="p-6">
            <form id="registerForm" class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" id="usernameInput"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                           placeholder="Choose a username" required>
                    <p class="text-sm text-gray-600 mt-1">Min 3 characters</p>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Email Address</label>
                    <input type="email" name="email" id="emailInput"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                           placeholder="your@email.com" required>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" name="phone" id="phoneInput"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                           placeholder="10 digit phone number" maxlength="10" required>
                    <p class="text-sm text-gray-600 mt-1">Valid Indian number (10 digits)</p>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="registerPassword"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                               placeholder="Create a password" required>
                        <button type="button" onclick="toggleRegisterPassword()" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-800 transition">
                            <i id="registerPasswordIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Confirm Password</label>
                    <div class="relative">
                        <input type="password" name="confirm_password" id="confirmPassword"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                               placeholder="Confirm your password" required>
                        <button type="button" onclick="toggleConfirmPassword()" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-800 transition">
                            <i id="confirmPasswordIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Terms and Conditions -->
                <div class="flex items-start">
                    <input type="checkbox" id="terms" name="terms" 
                           class="mt-1 mr-3 h-5 w-5 text-blue-600 rounded" required>
                    <label for="terms" class="text-sm text-gray-700">
                        I agree to the <a href="#" class="text-blue-600 hover:underline">Terms of Service</a> 
                        and <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a>. 
                        I confirm that I am at least 18 years old.
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-4 rounded-xl font-bold text-lg hover:from-blue-600 hover:to-purple-700 transition-all">
                    <i class="fas fa-user-plus mr-2"></i> Create Account
                </button>
            </form>
            
            <!-- Login Link -->
            <div class="text-center mt-6 pt-6 border-t">
                <p class="text-gray-600">
                    Already have an account? 
                    <a href="?page=login" class="text-blue-600 font-bold hover:text-blue-800">Sign In</a>
                </p>
            </div>
            
            <div class="text-center mt-4">
                <a href="?page=help"
                class="text-sm text-gray-500 hover:text-blue-600 flex items-center justify-center gap-2">
                <i class="fas fa-circle-question"></i>
                Need help?
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Phone number input - only numbers allowed, max 10 digits
    document.getElementById('phoneInput').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }
    });

    // Phone number paste validation
    document.getElementById('phoneInput').addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        const numbersOnly = pastedText.replace(/[^0-9]/g, '').slice(0, 10);
        this.value = numbersOnly;
    });

    function toggleRegisterPassword() {
        const passwordInput = document.getElementById('registerPassword');
        const icon = document.getElementById('registerPasswordIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function toggleConfirmPassword() {
        const confirmInput = document.getElementById('confirmPassword');
        const icon = document.getElementById('confirmPasswordIcon');
        
        if (confirmInput.type === 'password') {
            confirmInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            confirmInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Basic validation
        const username = this.elements.username.value.trim();
        const email = this.elements.email.value.trim();
        const phone = this.elements.phone.value.trim();
        const password = this.elements.password.value;
        const confirmPassword = this.elements.confirm_password.value;
        
        // Frontend validation
        if (username.length < 3) {
            showToast('Username must be at least 3 characters', 3000, 'error');
            return;
        }
        
        if (!email) {
            showToast('Email is required', 3000, 'error');
            return;
        }
        
        if (!phone || phone.length !== 10) {
            showToast('Phone number must be exactly 10 digits', 3000, 'error');
            return;
        }
        
        // Indian phone number validation (6,7,8,9 start)
        if (!/^[6-9]\d{9}$/.test(phone)) {
            showToast('Please insert valid phone number', 3000, 'error');
            return;
        }
        
        if (password !== confirmPassword) {
            showToast('Passwords do not match!', 3000, 'error');
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
        
        if (!this.elements.terms.checked) {
            showToast('You must accept the terms and conditions', 3000, 'error');
            return;
        }
        
        // Submit form
        const formData = new FormData(this);
        formData.append('action', 'register');
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating Account...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('ajax/auth.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast('✓ ' + (data.message || 'OTP sent to your email!'), 2500, 'success');
                
                // Store email in session storage for verify-otp page
                sessionStorage.setItem('registration_email', email);
                
                // Redirect to OTP verification page after 2 seconds
                setTimeout(() => {
                    window.location.href = '?page=verify-otp&email=' + encodeURIComponent(email);
                }, 2000);
            } else {
                // Handle backend validation errors
                if (data.errors && typeof data.errors === 'object') {
                    Object.keys(data.errors).forEach(field => {
                        const error = data.errors[field];
                        if (Array.isArray(error)) {
                            error.forEach(msg => showToast(msg, 3000, 'error'));
                        } else {
                            showToast(error, 3000, 'error');
                        }
                    });
                } else {
                    showToast(data.message || 'Registration failed', 3000, 'error');
                }
                
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

<style>
input:focus {
    outline: none;
}

button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
</style>
</body>
</html>