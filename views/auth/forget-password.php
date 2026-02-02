<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo SITE_NAME ?? 'EarnApp'; ?></title>
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
            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-lock text-yellow-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Forgot Password?</h1>
            <p class="text-gray-600 mt-2">No worries, we'll help you reset it</p>
        </div>

        <form id="forgotForm" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="email" id="email" name="email" required 
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                           placeholder="Enter your email address">
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    We'll send you instructions to reset your password
                </p>
            </div>

            <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 rounded-lg font-bold hover:from-blue-600 hover:to-purple-700 transition-all">
                <i class="fas fa-paper-plane mr-2"></i>Send Reset Link
            </button>
        </form>

        <div class="mt-6 text-center pt-6 border-t">
            <p class="text-gray-600">Remember your password? 
                <a href="?page=login" class="text-blue-600 font-bold hover:text-blue-800">Sign in</a>
            </p>
        </div>

        <div class="mt-6 text-center">
            <a href="?page=register" class="text-gray-600 hover:text-gray-800 text-sm">
                <i class="fas fa-user-plus mr-1"></i>Create new account
            </a>
        </div>

        <div id="message" class="mt-4 hidden text-center p-3 rounded-lg"></div>
    </div>

    <script>
        document.getElementById('forgotForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;

            const formData = new FormData();
            formData.append('action', 'forgot-password-step1');
            formData.append('email', email);

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('ajax/auth.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showMessage('Check your email for the reset code!', 'success');
                    // Redirect to OTP verification
                    setTimeout(() => {
                        window.location.href = '?page=reset-password-verify';
                    }, 2000);
                } else {
                    showMessage(data.message || 'Failed to send reset code', 'error');
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