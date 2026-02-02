<?php
require_once 'config/constants.php';
require_once 'controllers/AuthController.php';

$auth = new AuthController();
$isLoggedIn = $auth->checkAuth();
$isAdmin = $auth->isAdmin();

$page = $_GET['page'] ?? 'dashboard';

$publicPages = ['login', 'register', 'forgot-password'];

if (!$isLoggedIn && !in_array($page, $publicPages)) {
    $page = 'login';
}

if ($isLoggedIn && in_array($page, ['login', 'register'])) {
    $page = 'dashboard';
}

if ($isLoggedIn) {
    $user = $auth->getCurrentUser();
    if ($user && $user['is_banned']) {
        $page = 'banned';
    }
}

$seoData = [
    'dashboard' => [
        'title' => 'Dashboard - FolloPay | Earn Money from Reviews',
        'description' => 'Access your FolloPay dashboard to track earnings, manage reviews, and monitor your wallet balance in real-time.',
        'keywords' => 'review earnings dashboard, money tracking app, wallet management'
    ],
    'earn' => [
        'title' => 'Earn Money From Reviews - FolloPay',
        'description' => 'Earn genuine money by writing reviews on Google Play Store and app reviews. Get paid for authentic feedback on apps and services.',
        'keywords' => 'earn money from reviews, app reviews payment, review rewards, get paid for reviews'
    ],
    'wallet' => [
        'title' => 'Digital Wallet - FolloPay | Manage Your Earnings',
        'description' => 'Check your wallet balance, track transactions, and manage all your review earnings in one secure place.',
        'keywords' => 'digital wallet app, earning wallet, money management, transaction tracking'
    ],
    'withdraw' => [
        'title' => 'Withdraw Your Earnings - FolloPay',
        'description' => 'Withdraw your hard-earned money from FolloPay to your bank account. Fast, secure, and hassle-free withdrawal process.',
        'keywords' => 'withdraw earnings, cash out, bank transfer, payment withdrawal'
    ],
    'profile' => [
        'title' => 'My Profile - FolloPay | Manage Your Account',
        'description' => 'Update your profile information, verify your identity, and manage your FolloPay account settings.',
        'keywords' => 'profile management, account settings, user verification'
    ],
    'login' => [
        'title' => 'Login to FolloPay - Start Earning from Reviews',
        'description' => 'Sign in to your FolloPay account to start earning money by writing genuine app and service reviews.',
        'keywords' => 'login FolloPay, review earning app login, sign in'
    ],
    'register' => [
        'title' => 'Join FolloPay - Earn Money Writing Reviews',
        'description' => 'Register for free on FolloPay and start earning money immediately by writing honest reviews for apps and services.',
        'keywords' => 'register FolloPay, sign up earning app, create review account'
    ],
    'admin' => [
        'title' => 'Admin Dashboard - FolloPay Management',
        'description' => 'Admin panel for managing users, reviews, withdrawals, and platform activities.',
        'keywords' => 'admin panel, platform management, user administration'
    ]
];

$currentSeo = $seoData[$page] ?? [
    'title' => SITE_NAME . ' - Earn Money from Reviews',
    'description' => 'FolloPay is a legitimate platform where you can earn real money by writing genuine reviews for apps, games, and services.',
    'keywords' => 'earn money reviews, app reviews payment, review rewards'
];

$viewFiles = [
    'dashboard' => 'views/dashboard.php',
    'earn' => 'views/earn.php',
    'wallet' => 'views/wallet.php',
    'withdraw' => 'views/withdraw.php',
    'profile' => 'views/profile.php',
    'help' => 'views/help.php',
    'login' => 'views/auth/login.php',
    'register' => 'views/auth/register.php',
    'banned' => 'views/banned.php',
    'admin' => 'views/admin/dashboard.php',
    'admin-posts' => 'views/admin/posts.php',
    'admin-withdrawals' => 'views/admin/withdrawals.php',
    'admin-users' => 'views/admin/users.php',
    'admin-post-comments' => 'views/admin/admin-post-comments.php',
    'admin-screenshot-verification' => 'views/admin/screenshot-verification.php',
    'admin-post-edit' => 'views/admin/admin-post-edit.php',
    'manage-images' => 'views/admin/manage-images.php',
    'verify-otp' => 'views/auth/verify-otp.php',
    'reset-password-verify' => 'views/auth/reset-password-verify.php',
    'reset-password-form' => 'views/auth/reset-password-form.php'
];

$viewFile = $viewFiles[$page] ?? 'views/404.php';

if (in_array($page, ['login', 'register'])) {
    require_once $viewFile;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo $currentSeo['title']; ?></title>
    <meta name="title" content="<?php echo htmlspecialchars($currentSeo['title']); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($currentSeo['description']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($currentSeo['keywords']); ?>">
    <meta name="author" content="FolloPay">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="language" content="English">
    <meta name="revisit-after" content="7 days">
    
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($currentSeo['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($currentSeo['description']); ?>">
    <meta property="og:image" content="https://res.cloudinary.com/dlg5fygaz/image/upload/v1770007061/logo_v89hgg.png">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($currentSeo['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($currentSeo['description']); ?>">
    <meta name="twitter:image" content="https://res.cloudinary.com/dlg5fygaz/image/upload/v1770007061/logo_v89hgg.png">
    
    <link rel="canonical" href="<?php echo isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"; ?>://<?php echo $_SERVER['HTTP_HOST']; ?><?php echo $_SERVER['REQUEST_URI']; ?>">
    
    <meta name="theme-color" content="#3B82F6">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="FolloPay">
    
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "<?php echo SITE_NAME; ?>",
        "url": "<?php echo isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>",
        "logo": "<?php echo isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>asserts/logo/logo.png",
        "description": "FolloPay is a trusted platform for earning money by writing genuine reviews.",
        "sameAs": [
            "https://www.facebook.com/follopay",
            "https://twitter.com/follopay",
            "https://instagram.com/follopay"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "Customer Service",
            "email": "support@follopay.com"
        }
    }
    </script>
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?php echo SITE_NAME; ?>",
        "url": "<?php echo isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>",
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "<?php echo isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>?page={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Home",
                "item": "<?php echo isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "<?php echo ucfirst(str_replace('-', ' ', $page)); ?>",
                "item": "<?php echo isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>?page=<?php echo $page; ?>"
            }
        ]
    }
    </script>
</head>
<body class="bg-gray-50">

    <!-- Top Navbar (Mobile + Desktop) -->
    <nav class="bg-white shadow sticky top-0 z-40" role="navigation" aria-label="Main Navigation">
        <div class="px-4 h-14 md:h-16 flex items-center justify-between">
            
            <!-- Logo -->
            <div class="flex items-center justify-center select-none pointer-events-none overflow-hidden">
                <a href="?page=dashboard" title="FolloPay - Earn Money from Reviews">
                    <img 
                        src="https://res.cloudinary.com/dlg5fygaz/image/upload/v1770007061/logo_v89hgg.png"
                        alt="FolloPay Logo - Earn Money Writing Reviews"
                        class="h-28 w-auto object-contain"
                        loading="lazy"
                    >
                </a>
            </div>

            <!-- Desktop Nav Items -->
            <div class="hidden md:flex items-center space-x-6 lg:space-x-8">
                <!-- Wallet Icon - Large & Clickable -->
                <a href="?page=wallet" title="View your wallet and balance" class="flex items-center justify-center hover:text-green-700 transition duration-200">
                    <i class="fas fa-wallet text-green-500 text-3xl hover:scale-110 transition-transform" aria-hidden="true"></i>
                </a>
                
                <!-- Profile -->
                <a href="?page=profile" title="View your profile" class="flex items-center space-x-2 hover:text-blue-600 transition">
                    <i class="fas fa-user-circle text-gray-600 text-xl" aria-hidden="true"></i>
                    <span class="hidden lg:inline text-gray-700 font-medium"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                </a>
                
                <!-- Logout -->
                <button onclick="logout()" title="Logout from FolloPay" class="bg-red-500 text-white px-3 lg:px-4 py-2 rounded-lg hover:bg-red-600 transition text-sm lg:text-base flex items-center space-x-2">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    <span class="hidden lg:inline">Logout</span>
                </button>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden flex items-center space-x-3">
                <a href="?page=wallet" title="View your wallet" class="text-green-600 text-2xl hover:text-green-700 transition">
                    <i class="fas fa-wallet" aria-hidden="true"></i>
                </a>
                <button onclick="toggleMobileMenu()" title="Toggle mobile menu" class="text-2xl text-gray-700" aria-label="Open mobile menu">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="flex flex-col md:flex-row min-h-screen">
        
        <!-- Sidebar (Desktop Only) -->
        <aside class="hidden md:block fixed md:relative left-0 top-14 md:top-0 w-full md:w-64 h-[calc(100vh-56px)] md:h-screen bg-white shadow overflow-y-auto z-30" role="navigation" aria-label="Sidebar Navigation">
            <nav class="p-4 space-y-2">
                <a href="?page=dashboard" title="Go to dashboard" class="sidebar-link flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-50 text-blue-600 transition">
                    <i class="fas fa-home w-5" aria-hidden="true"></i>
                    <span>Dashboard</span>
                </a>
                <a href="?page=earn" title="Earn money from reviews" class="sidebar-link flex items-center space-x-3 p-3 rounded-lg hover:bg-green-50 text-gray-700 transition">
                    <i class="fas fa-money-bill-wave w-5" aria-hidden="true"></i>
                    <span>Earn Money</span>
                </a>
                <a href="?page=wallet" title="View your wallet" class="sidebar-link flex items-center space-x-3 p-3 rounded-lg hover:bg-yellow-50 text-gray-700 transition">
                    <i class="fas fa-wallet w-5" aria-hidden="true"></i>
                    <span>Wallet</span>
                </a>
                <a href="?page=withdraw" title="Withdraw your earnings" class="sidebar-link flex items-center space-x-3 p-3 rounded-lg hover:bg-purple-50 text-gray-700 transition">
                    <i class="fas fa-credit-card w-5" aria-hidden="true"></i>
                    <span>Withdraw</span>
                </a>
                <a href="?page=profile" title="View your profile settings" class="sidebar-link flex items-center space-x-3 p-3 rounded-lg hover:bg-purple-50 text-gray-700 transition">
                    <i class="fas fa-user w-5" aria-hidden="true"></i>
                    <span>Profile</span>
                </a>
                <?php if ($isAdmin): ?>
                <div class="pt-4 border-t">
                    <p class="text-xs text-gray-500 uppercase px-3 mb-2">Admin Panel</p>
                    <a href="?page=admin" title="Admin dashboard" class="sidebar-link flex items-center space-x-3 p-3 rounded-lg hover:bg-red-50 text-red-600 transition">
                        <i class="fas fa-cog w-5" aria-hidden="true"></i>
                        <span>Admin Dashboard</span>
                    </a>
                </div>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Mobile Menu (Mobile Only) -->
        <div id="mobileMenu" class="hidden fixed inset-0 top-14 bg-black bg-opacity-50 z-20 md:hidden">
            <nav class="bg-white w-full shadow-lg p-4 space-y-2 max-h-[calc(100vh-56px)] overflow-y-auto" role="navigation" aria-label="Mobile Navigation">
                <a href="?page=dashboard" onclick="closeMobileMenu()" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-50 text-blue-600 transition">
                    <i class="fas fa-home w-5" aria-hidden="true"></i>
                    <span>Dashboard</span>
                </a>
                <a href="?page=earn" onclick="closeMobileMenu()" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-50 text-gray-700 transition">
                    <i class="fas fa-money-bill-wave w-5" aria-hidden="true"></i>
                    <span>Earn Money</span>
                </a>
                <a href="?page=wallet" onclick="closeMobileMenu()" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-yellow-50 text-gray-700 transition">
                    <i class="fas fa-wallet w-5" aria-hidden="true"></i>
                    <span>Wallet</span>
                </a>
                <a href="?page=withdraw" onclick="closeMobileMenu()" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-purple-50 text-gray-700 transition">
                    <i class="fas fa-credit-card w-5" aria-hidden="true"></i>
                    <span>Withdraw</span>
                </a>
                <hr class="my-3">
                <a href="?page=profile" onclick="closeMobileMenu()" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 text-gray-700 transition">
                    <i class="fas fa-user-circle w-5" aria-hidden="true"></i>
                    <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                </a>
                <button onclick="logout()" class="w-full bg-red-500 text-white p-3 rounded-lg hover:bg-red-600 transition flex items-center justify-center space-x-2">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    <span>Logout</span>
                </button>
            </nav>
        </div>

        <!-- Main Content -->
        <main class="flex-1 pt-4 md:pt-6 pb-4 md:pb-6 px-4 overflow-y-auto" role="main">
            <div class="max-w-6xl mx-auto">
                <div id="content">
                    <?php require_once $viewFile; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Bottom Navigation (Mobile Only) -->
<?php
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white shadow border-t border-gray-200 z-40" role="navigation" aria-label="Mobile Bottom Navigation">
    <div class="flex justify-around items-center h-16">

        <?php if ($isAdmin): ?>

            <a href="?page=admin" title="Admin dashboard" class="nav-item flex flex-col items-center justify-center flex-1 h-full text-blue-600 hover:bg-blue-50">
                <i class="fas fa-user-shield text-xl" aria-hidden="true"></i>
                <span class="text-xs mt-1">Admin</span>
            </a>

            <a href="?page=admin-screenshot-verification" title="Verify screenshots" class="nav-item flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:bg-gray-50">
                <i class="fas fa-check-circle text-xl" aria-hidden="true"></i>
                <span class="text-xs mt-1">Verify</span>
            </a>

            <a href="?page=admin-posts" title="Manage posts" class="nav-item flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:bg-gray-50">
                <i class="fas fa-file-alt text-xl" aria-hidden="true"></i>
                <span class="text-xs mt-1">Posts</span>
            </a>

            <a href="?page=manage-images" title="Verify images" class="nav-item flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:bg-gray-50">
                <i class="fas fa-image text-xl" aria-hidden="true"></i>
                <span class="text-xs mt-1">Images</span>
            </a>

            <a href="?page=admin-users" title="Manage users" class="nav-item flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:bg-gray-50">
                <i class="fas fa-users text-xl" aria-hidden="true"></i>
                <span class="text-xs mt-1">Users</span>
            </a>

            <a href="?page=admin-withdrawals" title="Manage withdrawals" class="nav-item flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:bg-gray-50">
                <i class="fas fa-money-check-alt text-xl" aria-hidden="true"></i>
                <span class="text-xs mt-1">Withdraw</span>
            </a>

        <?php else: ?>

            <a href="?page=dashboard" title="Dashboard" class="nav-item flex flex-col items-center justify-center flex-1 h-full text-blue-600 hover:bg-blue-50">
                <i class="fas fa-home text-xl" aria-hidden="true"></i>
                <span class="text-xs mt-1">Home</span>
            </a>

            <a href="?page=earn" title="Earn money from reviews" class="nav-item flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:bg-gray-50">
                <i class="fas fa-money-bill-wave text-xl" aria-hidden="true"></i>
                <span class="text-xs mt-1">Earn</span>
            </a>

            <a href="?page=wallet" title="Your wallet" class="nav-item flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:bg-gray-50">
                <i class="fas fa-wallet text-xl" aria-hidden="true"></i>
                <span class="text-xs mt-1">Wallet</span>
            </a>

            <a href="?page=profile" title="Your profile" class="nav-item flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:bg-gray-50">
                <i class="fas fa-user text-xl" aria-hidden="true"></i>
                <span class="text-xs mt-1">Profile</span>
            </a>

        <?php endif; ?>

    </div>
</nav>

    <!-- Content Area Mobile Padding (for bottom nav) -->
    <div class="md:hidden h-16"></div>

    <script>
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        }

        function closeMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.add('hidden');
        }

        document.getElementById('mobileMenu')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeMobileMenu();
            }
        });

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                fetch('ajax/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=logout'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '?page=login';
                    }
                });
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
            
            // Set active nav item based on current page
            document.querySelectorAll('.nav-item').forEach(item => {
                const href = item.getAttribute('href');
                if (href.includes('page=' + currentPage)) {
                    item.classList.remove('text-gray-600');
                    item.classList.add('text-blue-600');
                } else {
                    item.classList.remove('text-blue-600');
                    item.classList.add('text-gray-600');
                }
            });
        });

        // Update active nav item when clicking
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                closeMobileMenu();
                document.querySelectorAll('.nav-item').forEach(nav => {
                    nav.classList.remove('text-blue-600');
                    nav.classList.add('text-gray-600');
                });
                this.classList.remove('text-gray-600');
                this.classList.add('text-blue-600');
            });
        });
    </script>

</body>
</html>