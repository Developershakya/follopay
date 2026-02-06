<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - EarnApp</title>
     <?php include 'header.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="asserts/js/toast.js"></script>
</head>
<body class="flex items-center justify-center min-h-screen p-4 bg-gradient-to-br from-blue-500 to-purple-600">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white text-center">
            <img src="https://res.cloudinary.com/dlg5fygaz/image/upload/v1770007061/logo_v89hgg.png" alt="Logo" class="h-12 mx-auto mb-2">
            <h1 class="text-2xl font-bold">Reset Password</h1>
            <p class="opacity-90 mt-2">Enter your email to reset your password</p>
        </div>
        
        <!-- Content -->
        <div class="p-8">
            <form id="forgotPasswordForm" class="space-y-6">
                <div>
                    <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="email" id="email" name="email" required 
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="your@email.com">
                    </div>
                    <p class="text-gray-500 text-sm mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        We'll send you a link to reset your password
                    </p>
                </div>
                
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 rounded-lg font-bold hover:from-blue-600 hover:to-purple-700 transition-all disabled:opacity-50"
                        id="submitBtn">
                    <i class="fas fa-paper-plane mr-2"></i> Send Reset Link
                </button>
            </form>
            
            <!-- Back to Login -->
            <div class="text-center mt-6 pt-6 border-t">
                <a href="?page=login" class="text-blue-600 hover:text-blue-800 font-bold flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
            
            <!-- Help -->
            <div class="text-center mt-4">
                <a href="?page=help" class="text-gray-500 hover:text-blue-600 text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-circle-question"></i> Need help?
                </a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value.trim();
            
            if (!email) {
                showToast('Please enter your email address', 3000, 'error');
                return;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('ajax/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        action: 'forgot-password',
                        email: email
                    })
                });
                
                const data = await response.json();
                
                // Always show same message for security
                showToast('If this email exists, you will receive a reset link.', 3000, 'success');
                
                // Clear form
                document.getElementById('forgotPasswordForm').reset();
                
                // Redirect to login after 3 seconds
                setTimeout(() => {
                    window.location.href = '?page=login';
                }, 3000);
                
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