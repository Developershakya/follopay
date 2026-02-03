<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
     <?php include 'header.php'; ?>
</head>
<body>
    <div class="max-w-6xl mx-auto">
    <!-- Current Balance -->
    <div class="bg-gradient-to-r from-green-400 to-green-500 rounded-xl p-6 text-white mb-6">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm opacity-90">Available for Withdrawal</p>
                <p id="availableBalance" class="text-4xl font-bold">₹0</p>
                <p class="text-sm opacity-90 mt-2">Minimum withdrawal: ₹50</p>
            </div>
            <i class="fas fa-money-bill-wave text-5xl opacity-80"></i>
        </div>
    </div>
      
        <!-- Important Notice -->
<div class="bg-gray-50 py-6">
    <div class="bg-white border-l-8 border-amber-500 rounded-lg p-6  mx-auto shadow-md">
        <div class="flex items-start gap-4">
            <!-- Warning Icon -->
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-amber-500 text-2xl mt-1"></i>
            </div>

            <!-- Notice Text -->
            <div class="flex-1 text-gray-700">
                <h3 class="font-semibold text-gray-800 text-lg mb-3">
                    Important Notice
                </h3>
                <ul class="space-y-2 leading-relaxed text-sm sm:text-base">
                    <li>• Entering incorrect UPI ID or Free Fire UID may result in permanent loss of funds.</li>
                    <li>• Fake or duplicate accounts are not eligible for payment.</li>
                    <li>• Account name must match the UPI holder’s name. Mismatch will lead to rejection.</li>
                    <li class="text-red-600 font-medium">• Rejected payments are <strong>non-refundable</strong>.</li>
                    <li>• Multiple accounts withdrawing to the same UPI ID will be permanently banned.</li>
                    <li>• Instant withdrawals have a <strong>20% charge</strong>.</li>
                    <li>• Standard withdrawals available only <strong>10th–17th of each month</strong>.</li>
                </ul>
            </div>
        </div>
    </div>
</div>




    <!-- Withdrawal Type Selection -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- UPI Card -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white cursor-pointer hover:from-blue-600 hover:to-blue-700 transition-all" onclick="showUPIForm()">
            <div class="flex items-start justify-between">
                <div>
                    <i class="fas fa-university text-4xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">UPI Transfer</h3>
                    <p class="text-sm opacity-90">Instant & Duration options available</p>
                </div>
                <i class="fas fa-chevron-right text-2xl opacity-70"></i>
            </div>
            
            <div class="mt-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span>Instant (20% charge)</span>
                    <span class="font-bold">Min ₹5</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>Duration (No charge)</span>
                    <span class="font-bold">Min ₹10</span>
                </div>
            </div>
        </div>

        <!-- Free Fire Card -->
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white cursor-pointer hover:from-purple-600 hover:to-purple-700 transition-all" onclick="showFreeFireForm()">
            <div class="flex items-start justify-between">
                <div>
                    <i class="fas fa-gamepad text-4xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Free Fire Diamonds</h3>
                    <p class="text-sm opacity-90">Get Free Fire Diamonds</p>
                </div>
                <i class="fas fa-chevron-right text-2xl opacity-70"></i>
            </div>
            
            <div class="mt-4">
                <div class="flex justify-between text-sm mb-1">
                    <span>₹80</span>
                    <span class="font-bold">= 100 Diamonds</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>Processing Time</span>
                    <span class="font-bold">5-7 days</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Withdrawal Form Container -->
    <div id="withdrawalFormContainer" class="mb-8">
        <!-- Form will be loaded here -->
    </div>

    <!-- Withdrawal History -->
    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Withdrawal History</h2>
            <button onclick="refreshWithdrawals()" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
        
        <div id="withdrawalHistory" class="space-y-4">
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i>
                <p class="mt-2 text-gray-600">Loading withdrawal history...</p>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentWithdrawalType = '';
let selectedDiamondAmount = 0;

document.addEventListener('DOMContentLoaded', function() {
    loadAvailableBalance();
    loadWithdrawalHistory();
});

function loadAvailableBalance() {
    fetch('ajax/wallet.php?action=get_balance')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.wallet) {
                // Correct property name
                document.getElementById('availableBalance').textContent = '₹' + data.wallet.wallet_balance;
            } else {
                document.getElementById('availableBalance').textContent = '₹0';
            }
        })
        .catch(err => {
            console.error('Error fetching wallet:', err);
            document.getElementById('availableBalance').textContent = '₹0';
        });
}


function showUPIForm() {
    currentWithdrawalType = 'upi';
    const container = document.getElementById('withdrawalFormContainer');
    
    const dayOfMonth = new Date().getDate();
    const isDurationAvailable = (dayOfMonth >= 10 && dayOfMonth <= 17);
    
    container.innerHTML = `
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-bold mb-6 text-blue-600">UPI Withdrawal</h2>
            
            <form id="upiWithdrawalForm">
                <!-- Mode Selection -->
                <div class="mb-6">
                    <p class="font-bold text-gray-700 mb-3">Select Withdrawal Mode:</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="flex items-center p-4 border-2 border-blue-300 rounded-lg cursor-pointer hover:border-blue-500">
                            <input type="radio" name="withdraw_mode" value="instant" checked class="mr-3 h-5 w-5 text-blue-600">
                            <div>
                                <p class="font-bold">Instant Withdrawal</p>
                                <p class="text-sm text-gray-600">20% deduction</p>
                                <p class="text-sm text-gray-600">Min: ₹5, Processing: 1-7 days</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 border-2 ${isDurationAvailable ? 'border-green-300 hover:border-green-500' : 'border-gray-300 opacity-50'} rounded-lg cursor-pointer ${isDurationAvailable ? '' : 'cursor-not-allowed'}">
                            <input type="radio" name="withdraw_mode" value="duration" ${isDurationAvailable ? '' : 'disabled'} class="mr-3 h-5 w-5 text-green-600">
                            <div>
                                <p class="font-bold">Duration Withdrawal</p>
                                <p class="text-sm text-gray-600">No charges</p>
                                <p class="text-sm ${isDurationAvailable ? 'text-gray-600' : 'text-red-600'}">
                                    ${isDurationAvailable ? 'Min: ₹10, Processing: 5-7 days' : 'Available 10th-17th only'}
                                </p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Amount Input -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-bold mb-2">Amount (₹)</label>
                    <input type="number" id="upiAmount" name="amount" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
                           placeholder="Enter amount" min="5" step="5" required
                           oninput="calculateUPICharges()">
                    
                    <div class="mt-2 flex space-x-4">
                        <button type="button" onclick="setUPIAmount(80)" class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">₹80</button>
                        <button type="button" onclick="setUPIAmount(100)" class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">₹100</button>
                        <button type="button" onclick="setUPIAmount(200)" class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">₹200</button>
                        <button type="button" onclick="setUPIAmount(500)" class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">₹500</button>
                    </div>
                </div>
                
                <!-- UPI ID -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-bold mb-2">UPI ID</label>
                    <input type="text" name="upi_id" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" 
                           placeholder="yourname@upi" required>
                    <p class="text-sm text-gray-600 mt-2">Enter your UPI ID (e.g., username@okicici)</p>
                </div>
                
                <!-- Calculation Preview -->
                <div id="upiCalculation" class="mb-6 p-4 bg-gray-50 rounded-lg hidden">
                    <h4 class="font-bold mb-2">Withdrawal Summary</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Amount:</span>
                            <span id="calcAmount" class="font-bold">₹0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Instant Charge (20%)</span>
<small class="text-gray-500">(applies only to instant withdrawals)</small>

                            <span id="calcCharge" class="font-bold text-red-600">₹0</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span>You will receive:</span>
                            <span id="calcFinal" class="font-bold text-green-600 text-lg">₹0</span>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-4 rounded-xl font-bold text-lg hover:from-blue-600 hover:to-blue-700">
                     Withdrawal
                </button>
            </form>
            
            <div id="upiMessage" class="mt-4 text-center hidden"></div>
        </div>
    `;
    
    // Attach form submit handler
    document.getElementById('upiWithdrawalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitUPIWithdrawal(this);
    });
    
    // Attach mode change handler
    document.querySelectorAll('input[name="withdraw_mode"]').forEach(radio => {
        radio.addEventListener('change', calculateUPICharges);
    });
}

function showFreeFireForm() {
    currentWithdrawalType = 'free_fire';
    const container = document.getElementById('withdrawalFormContainer');

    container.innerHTML = `
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-bold mb-6 text-purple-600">Free Fire Diamonds</h2>
            
            <form id="ffWithdrawalForm">
                <!-- Diamond Cards -->
                <div class="mb-6">
                    <p class="font-bold text-gray-700 mb-3">Select Diamond Card:</p>
                    <div id="freeFireCardsContainer" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center py-4 text-gray-500">Loading cards...</div>
                    </div>
                </div>
                
                <!-- Free Fire UID -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-bold mb-2">Free Fire UID</label>
                    <input type="text" name="free_fire_uid" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" 
                           placeholder="Enter your Free Fire UID" required>
                    <p class="text-sm text-gray-600 mt-2">Enter your 8-10 digit Free Fire UID</p>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-purple-500 to-purple-600 text-white py-4 rounded-xl font-bold text-lg hover:from-purple-600 hover:to-purple-700">
                    Get Diamonds
                </button>
            </form>
            
            <div id="ffMessage" class="mt-4 text-center hidden"></div>
        </div>
    `;

    // Fetch cards from DB
    fetch('ajax/wallet.php?action=get_free_fire_cards')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('freeFireCardsContainer');
            if (data.success && data.cards.length) {
                let html = '';
data.cards.forEach(card => {
    html += `
        <label class="diamond-card p-4 border-2 border-purple-300 rounded-xl cursor-pointer hover:border-purple-500 flex flex-col items-center"
               onclick="selectDiamondCard(${card.rupees}, ${card.diamonds}, event)">
            <input type="radio" name="diamond_card" value="${card.rupees}" class="hidden" required>
            <img src="https://dukaan.b-cdn.net/700x700/webp/3778160/e5254215-6c9c-4c87-88d1-fcc35eaecb66/1624892361684-0495b49d-93e8-4bdc-98a8-d2d2d87b84a8.jpeg" alt="Diamond" class="w-16 h-16 mb-2">
            <p class="text-2xl font-bold text-purple-600">₹${card.rupees}</p>
            <p class="text-lg font-bold">${card.diamonds} Diamonds</p>
        </label>
    `;
});

                container.innerHTML = html;

                // Select first card by default
                selectDiamondCard(data.cards[0].rupees, data.cards[0].diamonds);
            } else {
                container.innerHTML = `<div class="text-center py-4 text-gray-500">No active cards available</div>`;
            }
        })
        .catch(err => {
            console.error('Error fetching Free Fire cards:', err);
        });

    // Form submit
    document.getElementById('ffWithdrawalForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('ajax', 'true');
        formData.append('action', 'request_withdraw');
        formData.append('type', 'free_fire');
        formData.append('amount', selectedDiamondAmount); // from card selection
        formData.append('mode', 'instant');

        submitFreeFireWithdrawal(formData);
    });
}


function setUPIAmount(amount) {
    document.getElementById('upiAmount').value = amount;
    calculateUPICharges();
}

function calculateUPICharges() {
    const amount = parseFloat(document.getElementById('upiAmount')?.value) || 0;
    const mode = document.querySelector('input[name="withdraw_mode"]:checked')?.value;
    const calcDiv = document.getElementById('upiCalculation');
    
    if (amount <= 0 || !mode) {
        calcDiv.classList.add('hidden');
        return;
    }
    
    let charge = 0;
    let final = amount;
    
    if (mode === 'instant') {
        charge = amount * 0.2;
        final = amount - charge;
    }
    
    // Update calculation display
    document.getElementById('calcAmount').textContent = '₹' + amount;
    document.getElementById('calcCharge').textContent = '₹' + charge.toFixed(2);
    document.getElementById('calcFinal').textContent = '₹' + final.toFixed(2);
    
    calcDiv.classList.remove('hidden');
}

function selectDiamondCard(amount, diamonds, event) {
    selectedDiamondAmount = amount;

    // Update card selection UI
    document.querySelectorAll('.diamond-card').forEach(card => {
        card.classList.remove('border-purple-500', 'bg-purple-50');
        card.classList.add('border-purple-300');
        card.querySelector('input[type="radio"]').checked = false;
    });

    const selectedCard = event.currentTarget;
    selectedCard.classList.remove('border-purple-300');
    selectedCard.classList.add('border-purple-500', 'bg-purple-50');
    selectedCard.querySelector('input[type="radio"]').checked = true;
}


async function submitUPIWithdrawal(form) {
    const formData = new FormData(form);
    formData.append('ajax', 'true');
    formData.append('action', 'request_withdraw');
    formData.append('type', 'upi');
    
    try {
        const response = await fetch('ajax/wallet.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        const messageDiv = document.getElementById('upiMessage');
        messageDiv.classList.remove('hidden');
        
        if (data.success) {
showToast(data.message, data.success ? 'success' : 'error');

            form.reset();
            loadAvailableBalance();
            loadWithdrawalHistory();
        } else {
showToast(data.message, data.success ? 'success' : 'error');

        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function submitFreeFireWithdrawal(formData) {
    try {
        const response = await fetch('ajax/wallet.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        const messageDiv = document.getElementById('ffMessage');
        messageDiv.classList.remove('hidden');
        
        if (data.success) {
showToast(data.message, data.success ? 'success' : 'error');
            document.getElementById('ffWithdrawalForm').reset();
            loadAvailableBalance();
            loadWithdrawalHistory();
        } else {
showToast(data.message, data.success ? 'success' : 'error');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}


function loadWithdrawalHistory() {
    const container = document.getElementById('withdrawalHistory');
    container.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i>
            <p class="mt-2 text-gray-600">Loading withdrawal history...</p>
        </div>
    `;

    fetch('ajax/wallet.php?action=get_withdrawal_history')
        .then(res => res.json())
        .then(data => {
            // Check if data.withdrawals.withdrawals exists and is array
            const withdrawalsArray = data.withdrawals?.withdrawals || [];
            
            if (withdrawalsArray.length) {
                renderWithdrawalHistory(withdrawalsArray);
            } else {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-inbox text-3xl text-gray-300"></i>
                        <p class="mt-2 text-gray-600">No withdrawal history</p>
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error('Error fetching withdrawals:', err);
            container.innerHTML = `
                <div class="text-center py-8 text-red-600">
                    <p>Error loading withdrawal history.</p>
                </div>
            `;
        });
}


function renderWithdrawalHistory(withdrawals) {
    const container = document.getElementById('withdrawalHistory');
    
    if (!withdrawals || withdrawals.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-inbox text-3xl text-gray-300"></i>
                <p class="mt-2 text-gray-600">No withdrawal history</p>
                <p class="text-sm text-gray-500">Make your first withdrawal to see history here</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    withdrawals.forEach(withdrawal => {
        const statusClass = getWithdrawalStatusClass(withdrawal.status);
        const typeIcon = withdrawal.type === 'upi' ? 'fa-university' : 'fa-gamepad';
        const typeColor = withdrawal.type === 'upi' ? 'text-blue-600' : 'text-purple-600';
        
        html += `
            <div class="bg-gray-50 border rounded-lg p-4 hover:bg-white transition-colors">
                <div class="flex justify-between items-start">
                    <div class="flex items-start">
                        <div class="w-12 h-12 rounded-full ${typeColor} bg-opacity-20 flex items-center justify-center mr-4">
                            <i class="fas ${typeIcon} ${typeColor}"></i>
                        </div>
                        <div>
                            <p class="font-bold">${withdrawal.type === 'upi' ? 'UPI Withdrawal' : 'Free Fire Diamonds'}</p>
                            <p class="text-sm text-gray-600">
                                ${new Date(withdrawal.created_at).toLocaleDateString('en-IN', { 
                                    day: 'numeric', 
                                    month: 'short', 
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}
                            </p>
                            ${withdrawal.upi_id ? `<p class="text-sm text-gray-600">UPI: ${withdrawal.upi_id}</p>` : ''}
                            ${withdrawal.free_fire_uid ? `<p class="text-sm text-gray-600">UID: ${withdrawal.free_fire_uid}</p>` : ''}
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-800">₹${withdrawal.amount}</p>
                        ${withdrawal.charge_amount > 0 ? 
                            `<p class="text-sm text-red-600">Charge: ₹${withdrawal.charge_amount}</p>` : 
                            `<p class="text-sm text-green-600">No charges</p>`
                        }
                        <span class="inline-block mt-2 px-3 py-1 rounded-full text-sm font-bold ${statusClass}">
                            ${withdrawal.status}
                        </span>
                    </div>
                </div>
                
                ${withdrawal.admin_notes ? `
                    <div class="mt-3 p-2 bg-yellow-50 border border-yellow-100 rounded">
                        <p class="text-sm text-yellow-800"><strong>Note:</strong> ${withdrawal.admin_notes}</p>
                    </div>
                ` : ''}
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function getWithdrawalStatusClass(status) {
    switch(status) {
        case 'approved': return 'bg-green-100 text-green-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'failed': return 'bg-red-100 text-red-800';
        case 'refunded': return 'bg-blue-100 text-blue-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function refreshWithdrawals() {
    loadWithdrawalHistory();
}
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    
    // Toast div
    const toast = document.createElement('div');
    toast.className = `
        flex items-center px-4 py-3 rounded-lg shadow-lg text-white font-bold
        ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}
        animate-slide-in
    `;
    toast.textContent = message;
    
    container.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
        setTimeout(() => container.removeChild(toast), 500);
    }, 3000);
}

</script>

<style>
.diamond-card {
    transition: all 0.3s ease;
}
@keyframes slide-in {
  0% { transform: translateX(100%); opacity: 0; }
  100% { transform: translateX(0); opacity: 1; }
}

.animate-slide-in {
  animation: slide-in 0.5s ease forwards;
}

.diamond-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
</style>
</body>
<!-- Toast Notifications -->
<div id="toastContainer" class="fixed top-5 right-5 space-y-2 z-50"></div>

</html>