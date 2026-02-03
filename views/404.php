<?php
require_once 'config/constants.php' ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - EarnApp</title>
    <?php include 'header.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body class="font-sans">
    <div class="min-h-screen w-full flex items-center justify-center p-3 md:p-8">
        <div class="bg-white/90 backdrop-blur-lg rounded-2xl md:rounded-3xl shadow-2xl p-6 md:p-12 w-full max-w-4xl">
            <div class="text-center">
                <!-- 404 -->
                <div class="floating mb-6 md:mb-8">
                    <span class="text-6xl md:text-9xl font-bold text-gray-800">4</span>
                    <span class="text-6xl md:text-9xl font-bold text-purple-600 mx-2 md:mx-4">
                        <i class="fas fa-search"></i>
                    </span>
                    <span class="text-6xl md:text-9xl font-bold text-gray-800">4</span>
                </div>
                
                <h1 class="text-2xl md:text-5xl font-bold text-gray-800 mb-4 md:mb-6">
                    Oops! Page Not Found
                </h1>
                
                <p class="text-sm md:text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                    The page you're looking for seems to have wandered off into the digital void.
                </p>
                
                <!-- Icons -->
                <div class="mb-8 flex justify-center items-center gap-2 md:gap-4 p-4 md:p-6 bg-gradient-to-r from-blue-100 to-purple-100 rounded-xl md:rounded-2xl inline-block">
                    <i class="fas fa-map-signs text-3xl md:text-5xl text-blue-500"></i>
                    <i class="fas fa-arrow-right text-xl md:text-2xl text-gray-400"></i>
                    <i class="fas fa-question-circle text-3xl md:text-5xl text-purple-500"></i>
                </div>
                
                <!-- Buttons -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-6 mb-8">
                    <a href="?page=dashboard" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-3 md:p-4 rounded-lg md:rounded-xl hover:from-blue-600 hover:to-blue-700 transition text-xs md:text-base">
                        <i class="fas fa-home text-xl md:text-2xl mb-2 block"></i>
                        <p class="font-bold">Dashboard</p>
                    </a>
                    
                    <a href="?page=earn" class="bg-gradient-to-r from-green-500 to-green-600 text-white p-3 md:p-4 rounded-lg md:rounded-xl hover:from-green-600 hover:to-green-700 transition text-xs md:text-base">
                        <i class="fas fa-money-bill-wave text-xl md:text-2xl mb-2 block"></i>
                        <p class="font-bold">Earn Money</p>
                    </a>
                    
                    <a href="?page=wallet" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-3 md:p-4 rounded-lg md:rounded-xl hover:from-purple-600 hover:to-purple-700 transition text-xs md:text-base">
                        <i class="fas fa-wallet text-xl md:text-2xl mb-2 block"></i>
                        <p class="font-bold">Wallet</p>
                    </a>
                </div>
                
                <!-- Stats if logged in -->
                <?php if(isset($_SESSION['user_id'])): ?>
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-4 inline-block">
                    <p class="text-gray-700 mb-3 text-sm">Your Stats:</p>
                    <div class="flex gap-6">
                        <div class="text-center">
                            <p class="text-lg md:text-2xl font-bold text-green-600">₹0</p>
                            <p class="text-xs text-gray-600">Balance</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg md:text-2xl font-bold text-blue-600">0</p>
                            <p class="text-xs text-gray-600">Tasks</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg md:text-2xl font-bold text-purple-600">0</p>
                            <p class="text-xs text-gray-600">Earnings</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Background -->
    <div class="fixed inset-0 pointer-events-none z-[-1] overflow-hidden">
        <div class="absolute top-10 left-10 w-20 md:w-32 h-20 md:h-32 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-bounce"></div>
        <div class="absolute bottom-10 right-10 w-20 md:w-32 h-20 md:h-32 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-bounce" style="animation-delay: 1s;"></div>
    </div>
</body>
</html>