<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Wallet - FolloPay</title>
    <?php include 'header.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="max-w-6xl mx-auto p-4">
                <!-- Statistics -->
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Earning Statistics</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Total Earned</p>
                    <p id="totalEarned" class="text-2xl font-bold text-green-600">₹0</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Tasks Completed</p>
                    <p id="tasksCompleted" class="text-2xl font-bold text-blue-600">0</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Total Withdrawn</p>
                    <p id="totalWithdrawn" class="text-2xl font-bold text-purple-600">₹0</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Success Rate</p>
                    <p id="successRate" class="text-2xl font-bold text-yellow-600">0%</p>
                </div>
            </div>
        </div>

        
        <!-- Wallet Balance Card -->
        <div class="bg-gradient-to-r from-green-400 to-green-500 rounded-xl p-6 text-white mb-6 mt-6 shadow-lg">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm opacity-90">Wallet Balance</p>
                    <p id="walletBalance" class="text-4xl font-bold">₹0</p>
                    <p class="text-sm opacity-90 mt-2">Available for withdrawal</p>
                </div>
                <i class="fas fa-wallet text-5xl opacity-80" aria-hidden="true"></i>
            </div>
        </div>

        <!-- Pending Earnings -->
        <div class="bg-gradient-to-r from-blue-400 to-blue-500 rounded-xl p-6 text-white mb-6 shadow-lg">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm opacity-90">Today Earnings</p>
                    <p id="pendingEarnings" class="text-3xl font-bold">₹0</p>
                    <p class="text-sm opacity-90 mt-2">Today's completed tasks</p>
                </div>
                <i class="fas fa-clock text-4xl opacity-80" aria-hidden="true"></i>
            </div>
        </div>


        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <a href="?page=withdraw" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6 rounded-xl text-center hover:from-purple-600 hover:to-purple-700 transition shadow-md hover:shadow-lg">
                <i class="fas fa-credit-card text-3xl mb-3 block" aria-hidden="true"></i>
                <p class="font-bold text-lg">Withdraw Money</p>
                <p class="text-sm opacity-90">Transfer to UPI or Bank</p>
            </a>
            
            <a href="?page=profile" class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white p-6 rounded-xl text-center hover:from-yellow-600 hover:to-yellow-700 transition shadow-md hover:shadow-lg">
                <i class="fas fa-history text-3xl mb-3 block" aria-hidden="true"></i>
                <p class="font-bold text-lg">Transaction History</p>
                <p class="text-sm opacity-90">View all transactions</p>
            </a>
        </div>

        <!-- Transaction History -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-bold mb-4">Recent Transactions</h2>
            <div id="transactionHistory" class="space-y-3">
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-3xl text-gray-400" aria-hidden="true"></i>
                    <p class="mt-2 text-gray-600">Loading transactions...</p>
                </div>
            </div>
        </div>

    </div>

    <script>
        // ============================================
        // 🎯 OPTIMIZED - SINGLE API CALL ONLY
        // ============================================
        
        let walletData = null;
        let isLoading = false;
        
        document.addEventListener('DOMContentLoaded', function() {
            loadAllData();
        });
        
        /**
         * Single API call - Get all wallet data
         */
        function loadAllData() {
            if (isLoading) return;
            
            isLoading = true;
            console.log('📊 Fetching wallet data...');
            
            fetch('ajax/wallet.php?action=get_balance')
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch');
                    return response.json();
                })
                .then(data => {
                    console.log('✅ Wallet data loaded:', data);
                    isLoading = false;
                    
                    if (data.success && data.wallet) {
                        walletData = data.wallet;
                        
                        // Update all sections with single data
                        updateBalance();
                        updatePendingEarnings();
                        updateStatistics();
                        renderTransactions();
                    }
                })
                .catch(error => {
                    console.error('❌ Error:', error);
                    isLoading = false;
                });
        }
        
        /**
         * Update wallet balance from API data
         */
        function updateBalance() {
            const balance = parseFloat(walletData.wallet_balance || 0);
            document.getElementById('walletBalance').textContent = '₹' + balance.toFixed(2);
        }
        
        /**
         * Update pending earnings - Today's completed tasks
         */
        function updatePendingEarnings() {
            const transactions = walletData.transactions || [];
            const today = new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD format
            
            let todayEarned = 0;
            
            transactions.forEach(tx => {
                // Check if transaction is from today and is a completed credit
                const txDate = new Date(tx.created_at).toLocaleDateString('en-CA');
                
                if (txDate === today && 
                    tx.type === 'credit' && 
                    tx.status === 'completed' && 
                    tx.reference_type.includes('post')) {
                    todayEarned += parseFloat(tx.amount || 0);
                }
            });
            
            document.getElementById('pendingEarnings').textContent = '₹' + todayEarned.toFixed(2);
        }
        
        /**
         * Update statistics from transactions
         * Total Earned: Sum of all completed credit transactions
         * Tasks Completed: Count of completed credit transactions
         * Total Withdrawn: Sum of approved withdrawal debits
         * Success Rate: (completed payments / total payments) * 100
         */
        function updateStatistics() {
            const transactions = walletData.transactions || [];
            
            let totalEarned = 0;
            let tasksCompleted = 0;
            let totalWithdrawn = 0;
            let successfulPayments = 0;
            let totalPaymentAttempts = 0;
            
            transactions.forEach(tx => {
                // Total Earned - Completed credit transactions (approved)
                if (tx.type === 'credit' && tx.status === 'completed' && tx.reference_type.includes('post')) {
                    totalEarned += parseFloat(tx.amount || 0);
                    tasksCompleted++;
                }
                
                // Total Withdrawn - Approved withdrawal debits
                if (tx.type === 'debit' && 
                    tx.withdraw_type && 
                    tx.withdraw_status === 'approved') {
                    totalWithdrawn += parseFloat(tx.amount || 0);
                }
                
                // Success Rate - Count payment attempts
                if (tx.reference_type === 'withdrawal' || tx.type === 'debit') {
                    totalPaymentAttempts++;
                    if (tx.status === 'completed' && tx.withdraw_status === 'approved') {
                        successfulPayments++;
                    }
                }
            });
            
            // Calculate success rate
            const successRate = totalPaymentAttempts > 0 
                ? Math.round((successfulPayments / totalPaymentAttempts) * 100) 
                : 0;
            
            // Update UI
            document.getElementById('totalEarned').textContent = '₹' + totalEarned.toFixed(2);
            document.getElementById('tasksCompleted').textContent = tasksCompleted;
            document.getElementById('totalWithdrawn').textContent = '₹' + totalWithdrawn.toFixed(2);
            document.getElementById('successRate').textContent = successRate + '%';
        }
        
        /**
         * Render transaction history
         */
        function renderTransactions() {
            const transactions = walletData.transactions || [];
            const container = document.getElementById('transactionHistory');
            
            if (!Array.isArray(transactions) || transactions.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-wallet text-3xl text-gray-300" aria-hidden="true"></i>
                        <p class="mt-2 text-gray-600">No transactions yet</p>
                        <p class="text-sm text-gray-500">Complete tasks to see transactions here</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = '';
            
            transactions.forEach(tx => {
                const typeClass = tx.type === 'credit' ? 'text-green-600' : 'text-red-600';
                const icon = tx.type === 'credit' ? 'fa-arrow-down' : 'fa-arrow-up';
                const sign = tx.type === 'credit' ? '+' : '-';
                const bgClass = tx.type === 'credit' ? 'bg-green-100' : 'bg-red-100';
                
                const date = new Date(tx.created_at).toLocaleDateString('en-IN', {
                    day: '2-digit',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                const statusClass = getStatusClass(tx.status);
                
                const txHtml = `
                    <div class="bg-gray-50 border rounded-lg p-4 hover:bg-white transition-colors">
                        <div class="flex justify-between items-start md:items-center gap-4 flex-col md:flex-row">
                            <div class="flex items-center flex-1">
                                <div class="w-10 h-10 rounded-full ${bgClass} flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas ${icon} ${typeClass}" aria-hidden="true"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-gray-800 break-words">${escapeHtml(tx.description || 'Transaction')}</p>
                                    <p class="text-sm text-gray-600">${date}</p>
                                    ${tx.app_name ? `<p class="text-xs text-gray-500">${escapeHtml(tx.app_name)}</p>` : ''}
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="font-bold ${typeClass}">
                                    ${sign}₹${parseFloat(tx.amount || 0).toFixed(2)}
                                </p>
                                <p class="text-sm text-gray-600">Balance: ₹${parseFloat(tx.balance_after || 0).toFixed(2)}</p>
                                <span class="text-xs px-2 py-1 rounded-full inline-block mt-1 ${statusClass}">
                                    ${capitalizeFirst(tx.status)}
                                </span>
                            </div>
                        </div>
                    </div>
                `;
                
                container.innerHTML += txHtml;
            });
        }
        
        /**
         * Get status badge class
         */
        function getStatusClass(status) {
            switch(status) {
                case 'completed': return 'bg-green-100 text-green-800 font-semibold';
                case 'pending': return 'bg-yellow-100 text-yellow-800 font-semibold';
                case 'failed': return 'bg-red-100 text-red-800 font-semibold';
                default: return 'bg-gray-100 text-gray-800 font-semibold';
            }
        }
        
        /**
         * Capitalize first letter
         */
        function capitalizeFirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
        
        /**
         * Escape HTML to prevent XSS
         */
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    </script>

</body>
</html>