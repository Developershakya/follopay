/**
 * Withdrawals Management Page
 */

let allWithdrawals = [];
let filteredWithdrawals = [];
let currentFilter = 'all';

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadWithdrawals();
    setupEventListeners();
});

function setupEventListeners() {
    // Search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterWithdrawals, 300));
    }
    
    // Type filter
    const typeFilter = document.getElementById('typeFilter');
    if (typeFilter) {
        typeFilter.addEventListener('change', filterWithdrawals);
    }
    
    // Date filter
    const dateFilter = document.getElementById('dateFilter');
    if (dateFilter) {
        dateFilter.addEventListener('change', filterWithdrawals);
    }
    
    // Filter dropdown toggle
    const filterDropdown = document.getElementById('filterDropdown');
    const filterMenu = document.getElementById('filterMenu');
    if (filterDropdown && filterMenu) {
        filterDropdown.addEventListener('click', () => {
            filterMenu.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!filterDropdown.contains(e.target) && !filterMenu.contains(e.target)) {
                filterMenu.classList.add('hidden');
            }
        });
    }
}

async function loadWithdrawals(status = 'all') {
    try {
        showLoading(true);
        currentFilter = status;
        
        const result = await AdminAPI.getWithdrawals(status);
        
        if (result.success) {
            allWithdrawals = result.withdrawals || [];
            filteredWithdrawals = [...allWithdrawals];
            
            // Update stats
            updateStats(result.stats || calculateStats(allWithdrawals));
            
            // Display withdrawals
            displayWithdrawals(filteredWithdrawals);
        } else {
            showToast(result.message || 'Failed to load withdrawals', 'error');
        }
    } catch (error) {
        console.error('Error loading withdrawals:', error);
        showToast('Failed to load withdrawals: ' + error.message, 'error');
        displayWithdrawals([]);
    } finally {
        showLoading(false);
    }
}

function calculateStats(withdrawals) {
    const stats = {
        total: withdrawals.length,
        pending: 0,
        approved: 0,
        failed: 0,
        total_amount: 0,
        pending_amount: 0,
        approved_amount: 0
    };
    
    withdrawals.forEach(w => {
        if (w.status === 'pending') {
            stats.pending++;
            stats.pending_amount += parseFloat(w.amount || 0);
        } else if (w.status === 'approved') {
            stats.approved++;
            stats.approved_amount += parseFloat(w.final_amount || 0);
        } else if (w.status === 'failed') {
            stats.failed++;
        }
        stats.total_amount += parseFloat(w.amount || 0);
    });
    
    return stats;
}

function updateStats(stats) {
    // Update stat cards
    document.getElementById('pendingCount').textContent = stats.pending || 0;
    document.getElementById('approvedCount').textContent = stats.approved || 0;
    document.getElementById('failedCount').textContent = stats.failed || 0;
    document.getElementById('totalAmount').textContent = formatCurrency(stats.total_amount || 0);
    
    // Update table count
    document.getElementById('totalCount').textContent = stats.total || 0;
    document.getElementById('startCount').textContent = stats.total > 0 ? '1' : '0';
    document.getElementById('endCount').textContent = stats.total || 0;
}

function displayWithdrawals(withdrawals) {
    const tbody = document.getElementById('withdrawalsTable');
    
    if (!withdrawals || withdrawals.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-8 text-center">
                    <i class="fas fa-inbox text-4xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500">No withdrawals found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = withdrawals.map(w => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4">
                <div>
                    <p class="font-medium text-gray-900">${escapeHtml(w.username)}</p>
                    <p class="text-sm text-gray-500">${escapeHtml(w.email)}</p>
                    ${w.phone ? `<p class="text-xs text-gray-400">${escapeHtml(w.phone)}</p>` : ''}
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="flex items-center">
                    ${w.type === 'upi' ? 
                        '<i class="fas fa-university text-blue-500 mr-2"></i>' : 
                        '<i class="fas fa-gamepad text-purple-500 mr-2"></i>'}
                    <span class="font-medium">${w.type === 'upi' ? 'UPI' : 'Free Fire'}</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    ${w.withdraw_mode === 'instant' ? 'Instant' : 'Duration'}
                </p>
            </td>
            <td class="px-6 py-4">
                <div>
                    <p class="font-medium text-gray-900">${formatCurrency(w.amount)}</p>
                    ${w.charge_amount > 0 ? 
                        `<p class="text-xs text-red-500">-${formatCurrency(w.charge_amount)} (${w.charge_percent}%)</p>` : ''}
                    ${w.final_amount != w.amount ? 
                        `<p class="text-sm font-medium text-green-600">${formatCurrency(w.final_amount)}</p>` : ''}
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm">
                    ${w.type === 'upi' ? 
                        `<p class="text-gray-700">${escapeHtml(w.upi_id || 'N/A')}</p>` : 
                        `<p class="text-gray-700">UID: ${escapeHtml(w.free_fire_uid || 'N/A')}</p>`}
                </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
                <div>
                    <p>${formatDate(w.created_at)}</p>
                    ${w.processed_at ? 
                        `<p class="text-xs">Processed: ${formatDate(w.processed_at)}</p>` : ''}
                </div>
            </td>
            <td class="px-6 py-4">
                ${getStatusBadge(w.status)}
                ${w.admin_notes ? 
                    `<p class="text-xs text-gray-500 mt-1">${escapeHtml(w.admin_notes)}</p>` : ''}
            </td>
            <td class="px-6 py-4">
                <div class="flex space-x-2">
                    ${w.status === 'pending' ? `
                        <button onclick="openProcessModal(${w.id}, 'approve')" 
                                class="text-green-600 hover:text-green-900" 
                                title="Approve">
                            <i class="fas fa-check-circle text-lg"></i>
                        </button>
                        <button onclick="openProcessModal(${w.id}, 'reject')" 
                                class="text-red-600 hover:text-red-900" 
                                title="Reject">
                            <i class="fas fa-times-circle text-lg"></i>
                        </button>
                    ` : ''}
                    <button onclick="viewWithdrawalDetails(${w.id})" 
                            class="text-blue-600 hover:text-blue-900" 
                            title="View Details">
                        <i class="fas fa-eye text-lg"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function filterWithdrawals() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const typeFilter = document.getElementById('typeFilter')?.value || '';
    const dateFilter = document.getElementById('dateFilter')?.value || '';
    
    filteredWithdrawals = allWithdrawals.filter(w => {
        const matchesSearch = !searchTerm || 
            w.username.toLowerCase().includes(searchTerm) ||
            w.email.toLowerCase().includes(searchTerm) ||
            (w.upi_id && w.upi_id.toLowerCase().includes(searchTerm)) ||
            (w.free_fire_uid && w.free_fire_uid.includes(searchTerm));
        
        const matchesType = !typeFilter || w.type === typeFilter;
        
        const matchesDate = !dateFilter || 
            w.created_at.startsWith(dateFilter);
        
        return matchesSearch && matchesType && matchesDate;
    });
    
    const stats = calculateStats(filteredWithdrawals);
    updateStats(stats);
    displayWithdrawals(filteredWithdrawals);
}

function filterWithdrawalsByStatus(status) {
    loadWithdrawals(status);
    document.getElementById('filterMenu').classList.add('hidden');
}

async function viewWithdrawalDetails(id) {
    try {
        showLoading(true);
        const result = await AdminAPI.getWithdrawalDetails(id);
        
        if (result.success) {
            showWithdrawalModal(result.withdrawal);
        } else {
            showToast(result.message || 'Failed to load details', 'error');
        }
    } catch (error) {
        console.error('Error loading withdrawal details:', error);
        showToast('Failed to load details: ' + error.message, 'error');
    } finally {
        showLoading(false);
    }
}

function showWithdrawalModal(withdrawal) {
    const modal = document.getElementById('withdrawalDetailsModal');
    if (!modal) return;
    
    document.getElementById('modalContent').innerHTML = `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">User</label>
                    <p class="mt-1 text-gray-900">${escapeHtml(withdrawal.username)}</p>
                    <p class="text-sm text-gray-600">${escapeHtml(withdrawal.email)}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Type</label>
                    <p class="mt-1 text-gray-900">${withdrawal.type === 'upi' ? 'UPI' : 'Free Fire'}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Amount</label>
                    <p class="mt-1 text-gray-900">${formatCurrency(withdrawal.amount)}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Final Amount</label>
                    <p class="mt-1 text-gray-900">${formatCurrency(withdrawal.final_amount)}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Status</label>
                    <p class="mt-1">${getStatusBadge(withdrawal.status)}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Date</label>
                    <p class="mt-1 text-gray-900">${formatDate(withdrawal.created_at)}</p>
                </div>
                ${withdrawal.type === 'upi' ? `
                    <div class="col-span-2">
                        <label class="text-sm font-medium text-gray-500">UPI ID</label>
                        <p class="mt-1 text-gray-900">${escapeHtml(withdrawal.upi_id)}</p>
                    </div>
                ` : `
                    <div class="col-span-2">
                        <label class="text-sm font-medium text-gray-500">Free Fire UID</label>
                        <p class="mt-1 text-gray-900">${escapeHtml(withdrawal.free_fire_uid)}</p>
                    </div>
                `}
                ${withdrawal.admin_notes ? `
                    <div class="col-span-2">
                        <label class="text-sm font-medium text-gray-500">Admin Notes</label>
                        <p class="mt-1 text-gray-900">${escapeHtml(withdrawal.admin_notes)}</p>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function openProcessModal(withdrawalId, action) {
    const modal = document.getElementById('processModal');
    if (!modal) return;
    
    document.getElementById('processAction').value = action;
    document.getElementById('processWithdrawalId').value = withdrawalId;
    document.getElementById('modalTitle').textContent = 
        action === 'approve' ? 'Approve Withdrawal' : 'Reject Withdrawal';
    document.getElementById('processBtn').textContent = 
        action === 'approve' ? 'Approve' : 'Reject';
    document.getElementById('processBtn').className = 
        action === 'approve' ? 
        'bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg' :
        'bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg';
    
    modal.classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId)?.classList.add('hidden');
}

async function processWithdrawal() {
    const withdrawalId = document.getElementById('processWithdrawalId').value;
    const action = document.getElementById('processAction').value;
    const notes = document.getElementById('processNotes').value;
    const refund = document.getElementById('refundCheckbox')?.checked ?? true;
    
    try {
        showLoading(true);
        const result = await AdminAPI.processWithdrawal(withdrawalId, action, notes, refund);
        
        if (result.success) {
            showToast(`Withdrawal ${action}ed successfully!`, 'success');
            closeModal('processModal');
            loadWithdrawals(currentFilter);
        } else {
            showToast(result.message || 'Failed to process withdrawal', 'error');
        }
    } catch (error) {
        console.error('Error processing withdrawal:', error);
        showToast('Failed to process: ' + error.message, 'error');
    } finally {
        showLoading(false);
    }
}

function exportToExcel() {
    if (filteredWithdrawals.length === 0) {
        showToast('No data to export', 'error');
        return;
    }
    
    const data = filteredWithdrawals.map(w => ({
        'Username': w.username,
        'Email': w.email,
        'Type': w.type === 'upi' ? 'UPI' : 'Free Fire',
        'Amount': w.amount,
        'Charge': w.charge_amount,
        'Final Amount': w.final_amount,
        'Payment Details': w.type === 'upi' ? w.upi_id : w.free_fire_uid,
        'Status': w.status,
        'Date': formatDate(w.created_at)
    }));
    
    const csv = convertToCSV(data);
    downloadCSV(csv, 'withdrawals.csv');
    showToast('Export completed!', 'success');
}

function convertToCSV(data) {
    const headers = Object.keys(data[0]).join(',');
    const rows = data.map(row => 
        Object.values(row).map(val => `"${val}"`).join(',')
    );
    return headers + '\n' + rows.join('\n');
}

function downloadCSV(csv, filename) {
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}