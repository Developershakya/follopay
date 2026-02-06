<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME ?? 'EarnApp'; ?></title>
    <?php include '../follo/header.php'; ?>
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
        <div class="text-center ">
            <img src="https://res.cloudinary.com/dlg5fygaz/image/upload/v1770007061/logo_v89hgg.png" alt="Logo" class="h-16 mx-auto ">
            <p class="text-gray-600 mt-2">Sign in to your account</p>
        </div>

        <form id="loginForm" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" required 
                       class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                       placeholder="your@email.com">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required 
                           class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                           placeholder="Enter your password">
                    <button type="button" onclick="toggleLoginPassword()" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-800 transition">
                        <i id="loginPasswordIcon" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="h-4 w-4 text-blue-600 rounded">
                    <span class="ml-2 text-sm text-gray-700">Remember me</span>
                </label>
                <a href="?page=forgot-password" class="text-sm text-blue-600 hover:text-blue-800">Forgot password?</a>
            </div>

            <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 rounded-lg font-bold hover:from-blue-600 hover:to-purple-700 transition-all">
                Sign In
            </button>

            <div class="text-center">
                <p class="text-gray-600">Don't have an account? 
                    <a href="?page=register" class="text-blue-600 font-bold">Sign up</a>
                </p>
            </div>
            <div class="text-center mt-4">
            <a href="?page=help"
            class="text-sm text-gray-500 hover:text-blue-600 flex items-center justify-center gap-2">
            <i class="fas fa-circle-question"></i>
            Need help?
            </a>
            </div>

        </form>

        <div id="message" class="mt-4 text-center hidden"></div>
    </div>

    <script>
        function toggleLoginPassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('loginPasswordIcon');
            
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

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'login');

            fetch('ajax/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('message');
                messageDiv.className = 'mt-4 text-center';
                
                if (data.success) {
                    messageDiv.classList.add('text-green-600');
                    messageDiv.innerHTML = '<i class="fas fa-check-circle"></i> Login successful! Redirecting...';
                    
                    // Redirect to dashboard after 1 second
                    setTimeout(() => {
                        window.location.href = '?page=dashboard';
                    }, 1000);
                } else {
                    messageDiv.classList.add('text-red-600');
                    if (data.banned) {
                        messageDiv.innerHTML = `<i class="fas fa-ban"></i> You are banned. Reason: ${data.reason}`;
                    } else {
                        messageDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.message || 'Login failed'}`;
                    }
                }
                messageDiv.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                const messageDiv = document.getElementById('message');
                messageDiv.className = 'mt-4 text-center text-red-600';
                messageDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> An error occurred. Please try again.';
                messageDiv.classList.remove('hidden');
            });
        });
    </script>
</body>
</html>