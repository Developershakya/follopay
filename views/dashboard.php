<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
     <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    
<!-- Dashboard Content -->
<div class="w-full">
    <!-- Welcome Section -->
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Welcome, <?php echo $_SESSION['username']; ?>!</h1>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-8">
        <!-- Wallet Balance Card -->
        <div class="bg-gradient-to-br from-green-400 to-green-600 rounded-xl shadow-lg p-5 md:p-6 text-white overflow-hidden">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <p class="text-sm md:text-base opacity-90 font-medium">Wallet Balance</p>
                    <p id="dashboardBalance" class="text-3xl md:text-4xl font-bold mt-2">₹0</p>
                </div>
                <i class="fas fa-wallet text-4xl md:text-5xl opacity-20"></i>
            </div>
            <div class="mt-3 pt-3 border-t border-white border-opacity-30">
                <p class="text-xs md:text-sm opacity-90">Available for withdrawal</p>
            </div>
        </div>

        <!-- Total Earned Card -->
        <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl shadow-lg p-5 md:p-6 text-white overflow-hidden">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <p class="text-sm md:text-base opacity-90 font-medium">Total Earned</p>
                    <p id="dashboardEarned" class="text-3xl md:text-4xl font-bold mt-2">₹0</p>
                </div>
                <i class="fas fa-money-bill-wave text-4xl md:text-5xl opacity-20"></i>
            </div>
            <div class="mt-3 pt-3 border-t border-white border-opacity-30">
                <p class="text-xs md:text-sm opacity-90">Lifetime earnings</p>
            </div>
        </div>

        <!-- Available Tasks Card -->
        <div class="bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl shadow-lg p-5 md:p-6 text-white overflow-hidden sm:col-span-2 lg:col-span-1">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <p class="text-sm md:text-base opacity-90 font-medium">Available Tasks</p>
                    <p id="dashboardTasks" class="text-3xl md:text-4xl font-bold mt-2">0</p>
                </div>
                <i class="fas fa-tasks text-4xl md:text-5xl opacity-20"></i>
            </div>
            <div class="mt-3 pt-3 border-t border-white border-opacity-30">
                <p class="text-xs md:text-sm opacity-90">Ready to earn</p>
            </div>
        </div>
    </div>
     <!-- Today's Tasks Section -->
<div class="bg-white rounded-xl shadow-lg p-5 md:p-6 mb-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center space-x-2">
            <i class="fas fa-calendar-day text-green-500"></i>
            <span>Today's Tasks</span>
        </h2>
        <div class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
            <span id="todayTaskCount">0</span> Tasks
        </div>
    </div>

    <!-- Tasks Container -->
    <div id="todayTasksContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        <!-- Tasks will be injected here by JS -->
    </div>
</div>

    <!-- Available Tasks Section -->
    <div class="bg-white rounded-xl shadow-lg p-5 md:p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center space-x-2">
                <i class="fas fa-briefcase text-green-500"></i>
                <span>Available Tasks</span>
            </h2>
            <div class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                <span id="taskCount">0</span> Available
            </div>
        </div>

        <!-- Posts Container -->
        <div id="postsContainer">
            <!-- Loading State -->
            <div class="text-center py-12">
                <div class="inline-block">
                    <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-600 font-medium">Loading available tasks...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Load dashboard data
document.addEventListener('DOMContentLoaded', function () {
    loadDashboardStats();
    loadAvailablePosts();
    loadTodayTasks(); // ✅ NEW
});


    function loadDashboardStats() {
        fetch('ajax/wallet.php?action=get_balance')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const wallet = data.wallet;
                    
                    // Update balance
                    const balance = parseFloat(wallet.wallet_balance) || 0;
                    document.getElementById('dashboardBalance').textContent = '₹' + balance.toFixed(2);

                    // Calculate total earned
                    let totalEarned = 0;
                    if (wallet.transactions && Array.isArray(wallet.transactions)) {
                        wallet.transactions.forEach(transaction => {
                            if (transaction.type === 'credit' && transaction.status === 'completed') {
                                totalEarned += parseFloat(transaction.amount) || 0;
                            }
                        });
                    }
                    document.getElementById('dashboardEarned').textContent = '₹' + totalEarned.toFixed(2);
                }
            })
            .catch(err => console.error('Error loading stats:', err));
    }

    function loadAvailablePosts() {
        fetch('ajax/posts.php?action=get_available')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const posts = data.posts || [];
                    const postsContainer = document.getElementById('postsContainer');

                    // Update task count
                    document.getElementById('dashboardTasks').textContent = posts.length;
                    document.getElementById('taskCount').textContent = posts.length;

                    if (posts.length === 0) {
                        postsContainer.innerHTML = `
                            <div class="text-center py-12">
                                <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
                                <p class="text-gray-600 text-lg font-medium">No available tasks at the moment</p>
                                <p class="text-gray-500 text-sm mt-2">Check back later for new earning opportunities</p>
                            </div>
                        `;
                    } else {
                        // Create responsive grid
                        let html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">';
                        
                        posts.forEach(post => {
                            const appName = post.app_name || 'Unknown App';
                            const appLink = post.app_link || '#';
                            const price = parseFloat(post.price) || 0;
                            const commentsAvailable = post.available_comments || 0;
                            const postId = post.id;

                            html += `
                                <div class="bg-white border border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden flex flex-col">
                                    <!-- Card Header -->
                                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-4 md:p-5 border-b border-gray-200">
                                        <div class="flex justify-between items-start gap-3 mb-2">
                                            <div class="flex-1 min-w-0">
                                                <h3 class="font-bold text-lg md:text-base text-gray-800 truncate">${appName}</h3>
                                                <a href="${appLink}" target="_blank" class="inline-block mt-1">
    <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" 
         alt="Play Store" 
         class="h-8 md:h-10 hover:scale-105 transition-transform duration-200">
</a>

                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-2xl md:text-2xl font-bold text-green-600">₹${price.toFixed(2)}</span>
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">Active</span>
                                        </div>
                                    </div>

                                    <!-- Card Body -->
                                    <div class="p-4 md:p-5 flex-1">
                                        <div class="space-y-3">
                                            <div class="flex items-center text-gray-700">
                                                <i class="fas fa-clock text-orange-500 mr-3"></i>
                                                <span class="text-sm md:text-base">Quick task • ~5-10 min</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card Footer -->
                                    <div class="p-4 md:p-5 bg-gray-50 border-t border-gray-200">
                                        <button onclick="startEarning(${postId})" 
                                                class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-2 md:py-3 px-4 rounded-lg transition-all duration-300 transform hover:scale-105 flex items-center justify-center space-x-2 shadow-md hover:shadow-lg">
                                            <i class="fas fa-play"></i>
                                            <span class="text-sm md:text-base">Start Earning</span>
                                        </button>
                                    </div>
                                </div>
                            `;
                        });

                        html += '</div>';
                        postsContainer.innerHTML = html;
                    }
                }
            })
            .catch(err => {
                console.error('Error loading posts:', err);
                document.getElementById('postsContainer').innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-3"></i>
                        <p class="text-red-600 font-medium">Failed to load tasks</p>
                        <p class="text-gray-600 text-sm mt-2">Please refresh the page</p>
                    </div>
                `;
            });
    }
function loadTodayTasks() {
    fetch('ajax/posts.php?action=get_today_tasks')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('todayTasksContainer');
            const countBadge = document.getElementById('todayTaskCount');
            
            if (!data.success || data.tasks.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12 col-span-full">
                        <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
                        <p class="text-gray-600 text-lg font-medium">No tasks completed today</p>
                    </div>
                `;
                countBadge.textContent = 0;
                return;
            }

            countBadge.textContent = data.tasks.length;
            let html = '';

            data.tasks.forEach(task => {
                const appName = task.app_name || 'Unknown App';
                const appLink = task.app_link || '#';
                const price = parseFloat(task.price) || 0;

                // Status badge
                let badge = '';
                let badgeColor = '';
                if (task.status === 'approved') {
                    badgeColor = 'from-green-400 to-green-600 text-white';
                    badge = 'Approved';
                } else if (task.status === 'submitted') {
                    badgeColor = 'from-yellow-400 to-yellow-600 text-white';
                    badge = 'Pending';
                } else if (task.status === 'rejected') {
                    badgeColor = 'from-red-400 to-red-600 text-white';
                    badge = 'Rejected';
                }

                html += `
                    <div class="bg-white border border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 flex flex-col overflow-hidden">
                        <!-- Card Header -->
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-4 md:p-5 border-b border-gray-200">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-lg md:text-base text-gray-800 truncate">${appName}</h3>
                                 <a href="${appLink}" target="_blank" class="inline-block mt-1">
    <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" 
         alt="Play Store" 
         class="h-8 md:h-10 hover:scale-105 transition-transform duration-200">
</a>

                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-2xl md:text-2xl font-bold text-green-600">₹${price.toFixed(2)}</span>
                                <span class="bg-gradient-to-br ${badgeColor} px-2 py-1 rounded text-xs font-semibold">${badge}</span>
                            </div>
                        </div>
                        <!-- Card Body -->
                        <div class="p-4 md:p-5 flex-1">
                            <p class="text-gray-700 text-sm md:text-base">Task submitted on: ${task.submitted_time.split(' ')[0]}</p>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        })
        .catch(err => {
            console.error('Error loading today tasks:', err);
            const container = document.getElementById('todayTasksContainer');
            container.innerHTML = `
                <div class="text-center py-12 col-span-full">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-3"></i>
                    <p class="text-red-600 font-medium">Failed to load today’s tasks</p>
                    <p class="text-gray-600 text-sm mt-2">Please refresh the page</p>
                </div>
            `;
        });
}


    function startEarning(postId) {
        if (!postId) {
            alert('Invalid task');
            return;
        }

        // Disable button during request
        const buttons = document.querySelectorAll(`button[onclick="startEarning(${postId})"]`);
        buttons.forEach(btn => {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        });

        fetch('ajax/posts.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=assign_comment&post_id=${postId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to earn page
                window.location.href = '?page=earn';
            } else {
                alert(data.message || 'Failed to start task. Please try again.');
                // Re-enable button
                buttons.forEach(btn => {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                });
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('An error occurred. Please try again.');
            // Re-enable button
            buttons.forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            });
        });
    }

    // Refresh dashboard every 60 seconds
    setInterval(() => {
        loadDashboardStats();
        loadAvailablePosts();
    }, 60000);
</script>
</body>
</html>