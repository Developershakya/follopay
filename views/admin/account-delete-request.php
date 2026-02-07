<?php
require_once 'config/constants.php';
require_once 'controllers/AuthController.php';
require_once 'helpers/Session.php';

$session = new Session();
$auth = new AuthController();

// Check if admin
if (!$session->get('logged_in') || $session->get('role') !== 'admin') {
    header('Location: ' . BASE_URL . '/?page=login');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Deletion Requests - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .container {
            max-width: 100%;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .filters {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-group label {
            color: #555;
            font-weight: 500;
            font-size: 14px;
            white-space: nowrap;
        }

        select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }

        button {
            padding: 8px 16px;
            background: #3B82F6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        button:hover {
            background: #2563EB;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        /* Desktop Table */
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: none;
        }

        @media (min-width: 768px) {
            .table-container {
                display: block;
            }

            .cards-container {
                display: none;
            }
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }

        th {
            padding: 12px 15px;
            text-align: left;
            color: #555;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid #e9ecef;
            transition: background 0.2s ease;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        td {
            padding: 12px 15px;
            color: #333;
            font-size: 14px;
        }

        /* Mobile Cards */
        .cards-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        @media (min-width: 768px) {
            .cards-container {
                display: none;
            }
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 15px;
            border-left: 4px solid #3B82F6;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            gap: 10px;
        }

        .card-title {
            font-weight: 600;
            color: #333;
            font-size: 15px;
            flex: 1;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .card-info {
            margin-bottom: 12px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
        }

        .info-label {
            color: #666;
            font-weight: 500;
        }

        .info-value {
            color: #333;
            text-align: right;
            word-break: break-word;
            flex: 1;
            margin-left: 10px;
        }

        .card-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
            flex: 1;
            min-width: 80px;
        }

        .btn-view {
            background: #0d6efd;
        }

        .btn-view:hover {
            background: #0b5ed7;
        }

        .btn-approve {
            background: #198754;
        }

        .btn-approve:hover {
            background: #157347;
        }

        .btn-reject {
            background: #dc3545;
        }

        .btn-reject:hover {
            background: #bb2d3b;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: flex-end;
        }

        @media (min-width: 768px) {
            .modal {
                align-items: center;
            }
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 8px 8px 0 0;
            padding: 20px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        @media (min-width: 768px) {
            .modal-content {
                border-radius: 8px;
                width: 90%;
                margin: 20px;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .modal-header h2 {
            color: #333;
            font-size: 18px;
            margin: 0;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            color: #999;
            cursor: pointer;
            padding: 0;
            width: auto;
            margin: 0;
        }

        .close-btn:hover {
            color: #333;
        }

        .info-item {
            margin-bottom: 12px;
        }

        .info-label {
            color: #666;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .info-value {
            color: #333;
            font-size: 14px;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 4px;
            word-break: break-word;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            min-height: 80px;
            resize: vertical;
            font-size: 14px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #e9ecef;
            flex-wrap: wrap;
        }

        .modal-actions button {
            flex: 1;
            min-width: 100px;
        }

        .loading {
            display: none;
            text-align: center;
            color: #3B82F6;
            font-size: 14px;
            padding: 20px;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3B82F6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .pagination button {
            min-width: 40px;
            padding: 8px 12px;
        }

        .pagination button.active {
            background: #3B82F6;
        }

        .pagination button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗑️ Account Deletion Requests</h1>
            <p>Manage user account deletion requests</p>
        </div>

        <div class="filters">
            <div class="filter-group">
                <label for="statusFilter">Status:</label>
                <select id="statusFilter" onchange="loadRequests()">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <button onclick="loadRequests()">🔄 Refresh</button>
        </div>

        <div id="alertContainer"></div>

        <!-- Desktop Table View -->
        <div class="table-container">
            <table id="requestsTable">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="cards-container" id="cardsContainer">
            <div style="text-align: center; padding: 30px; color: #999;">Loading...</div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Request Details</h2>
                <button class="close-btn" onclick="closeModal('viewModal')">✕</button>
            </div>
            <div id="viewModalBody"></div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Approve Deletion</h2>
                <button class="close-btn" onclick="closeModal('approveModal')">✕</button>
            </div>
            <div class="info-item">
                <div class="info-label">User</div>
                <div class="info-value" id="approveUsername"></div>
            </div>
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value" id="approveEmail"></div>
            </div>
            <div class="info-item">
                <div class="info-label">Admin Notes (Optional)</div>
                <textarea id="approveNotes" placeholder="Add notes..."></textarea>
            </div>
            <div class="modal-actions">
                <button onclick="closeModal('approveModal')">Cancel</button>
                <button class="btn-approve" onclick="confirmApprove()">Approve & Delete</button>
            </div>
            <div class="loading" id="approveLoading">
                <span class="spinner"></span> Processing...
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reject Request</h2>
                <button class="close-btn" onclick="closeModal('rejectModal')">✕</button>
            </div>
            <div class="info-item">
                <div class="info-label">User</div>
                <div class="info-value" id="rejectUsername"></div>
            </div>
            <div class="info-item">
                <div class="info-label">Rejection Reason</div>
                <textarea id="rejectReason" placeholder="Explain why..."></textarea>
            </div>
            <div class="modal-actions">
                <button onclick="closeModal('rejectModal')">Cancel</button>
                <button class="btn-reject" onclick="confirmReject()">Reject Request</button>
            </div>
            <div class="loading" id="rejectLoading">
                <span class="spinner"></span> Processing...
            </div>
        </div>
    </div>

    <script>
        let currentPage = 0;
        let currentRequestId = null;
        const itemsPerPage = 10;

        function loadRequests(page = 0) {
            currentPage = page;
            const status = document.getElementById('statusFilter').value;
            const offset = page * itemsPerPage;

            fetch(`ajax/account.php?action=get-requests&status=${status}&limit=${itemsPerPage}&offset=${offset}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderTable(data.data);
                        renderCards(data.data);
                        renderPagination(data.total);
                    } else {
                        showAlert(data.message || 'Failed to load requests', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred', 'error');
                });
        }

        function renderTable(requests) {
            const tbody = document.getElementById('tableBody');

            if (requests.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 30px;">No requests found</td></tr>';
                return;
            }

            tbody.innerHTML = requests.map(req => `
                <tr>
                    <td><strong>${escapeHtml(req.username)}</strong></td>
                    <td>${escapeHtml(req.email)}</td>
                    <td>${escapeHtml(req.phone)}</td>
                    <td><span class="status-badge status-${req.request_status}">${req.request_status}</span></td>
                    <td>${new Date(req.requested_at).toLocaleDateString()}</td>
                    <td>
                        <button class="btn-small btn-view" onclick="viewRequest(${req.id})">View</button>
                        ${req.request_status === 'pending' ? `
                            <button class="btn-small btn-approve" onclick="openApproveModal(${req.id}, '${escapeHtml(req.username)}', '${escapeHtml(req.email)}')">Approve</button>
                            <button class="btn-small btn-reject" onclick="openRejectModal(${req.id}, '${escapeHtml(req.username)}')">Reject</button>
                        ` : ''}
                    </td>
                </tr>
            `).join('');
        }

        function renderCards(requests) {
            const container = document.getElementById('cardsContainer');

            if (requests.length === 0) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><p>No requests found</p></div>';
                return;
            }

            container.innerHTML = requests.map(req => `
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">${escapeHtml(req.username)}</div>
                        <span class="status-badge status-${req.request_status}">${req.request_status}</span>
                    </div>
                    <div class="card-info">
                        <div class="info-row">
                            <span class="info-label">Email</span>
                            <span class="info-value">${escapeHtml(req.email)}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phone</span>
                            <span class="info-value">${escapeHtml(req.phone)}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date</span>
                            <span class="info-value">${new Date(req.requested_at).toLocaleDateString()}</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="btn-small btn-view" onclick="viewRequest(${req.id})">View Details</button>
                        ${req.request_status === 'pending' ? `
                            <button class="btn-small btn-approve" onclick="openApproveModal(${req.id}, '${escapeHtml(req.username)}', '${escapeHtml(req.email)}')">Approve</button>
                            <button class="btn-small btn-reject" onclick="openRejectModal(${req.id}, '${escapeHtml(req.username)}')">Reject</button>
                        ` : ''}
                    </div>
                </div>
            `).join('');
        }

        function renderPagination(total) {
            const totalPages = Math.ceil(total / itemsPerPage);
            if (totalPages <= 1) return;

            let html = '<div class="pagination">';
            for (let i = 0; i < totalPages; i++) {
                html += `<button onclick="loadRequests(${i})" ${currentPage === i ? 'class="active"' : ''}>${i + 1}</button>`;
            }
            html += '</div>';

            document.querySelector('.pagination')?.remove();
            const container = document.querySelector('.table-container');
            if (container) {
                container.insertAdjacentHTML('afterend', html);
            }
        }

        function viewRequest(requestId) {
            fetch(`ajax/account.php?action=get-request&id=${requestId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const req = data.data;
                        document.getElementById('viewModalBody').innerHTML = `
                            <div class="info-item">
                                <div class="info-label">Username</div>
                                <div class="info-value">${escapeHtml(req.username)}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value">${escapeHtml(req.email)}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Phone</div>
                                <div class="info-value">${escapeHtml(req.phone)}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Status</div>
                                <div class="info-value">
                                    <span class="status-badge status-${req.request_status}">${req.request_status}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Request Reason</div>
                                <div class="info-value">${req.request_reason ? escapeHtml(req.request_reason) : 'No reason provided'}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Requested At</div>
                                <div class="info-value">${new Date(req.requested_at).toLocaleString()}</div>
                            </div>
                            ${req.admin_notes ? `
                                <div class="info-item">
                                    <div class="info-label">Admin Notes</div>
                                    <div class="info-value">${escapeHtml(req.admin_notes)}</div>
                                </div>
                            ` : ''}
                        `;
                        openModal('viewModal');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Failed to load request details', 'error');
                });
        }

        function openApproveModal(requestId, username, email) {
            currentRequestId = requestId;
            document.getElementById('approveUsername').textContent = username;
            document.getElementById('approveEmail').textContent = email;
            document.getElementById('approveNotes').value = '';
            openModal('approveModal');
        }

        function openRejectModal(requestId, username) {
            currentRequestId = requestId;
            document.getElementById('rejectUsername').textContent = username;
            document.getElementById('rejectReason').value = '';
            openModal('rejectModal');
        }

        function confirmApprove() {
            if (!currentRequestId) return;

            const notes = document.getElementById('approveNotes').value;

            if (!confirm('Are you sure? This will permanently delete the account!')) {
                return;
            }

            document.getElementById('approveLoading').style.display = 'block';

            const formData = new FormData();
            formData.append('action', 'approve-deletion');
            formData.append('request_id', currentRequestId);
            formData.append('admin_notes', notes);

            fetch('ajax/account.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('approveLoading').style.display = 'none';
                closeModal('approveModal');
                if (data.success) {
                    showAlert('Account deleted successfully', 'success');
                    loadRequests();
                } else {
                    showAlert(data.message || 'Failed to process', 'error');
                }
            })
            .catch(error => {
                document.getElementById('approveLoading').style.display = 'none';
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            });
        }

        function confirmReject() {
            if (!currentRequestId) return;

            const reason = document.getElementById('rejectReason').value;

            if (!reason.trim()) {
                showAlert('Please provide a rejection reason', 'error');
                return;
            }

            document.getElementById('rejectLoading').style.display = 'block';

            const formData = new FormData();
            formData.append('action', 'reject-deletion');
            formData.append('request_id', currentRequestId);
            formData.append('reason', reason);

            fetch('ajax/account.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('rejectLoading').style.display = 'none';
                closeModal('rejectModal');
                if (data.success) {
                    showAlert('Request rejected', 'success');
                    loadRequests();
                } else {
                    showAlert(data.message || 'Failed to process', 'error');
                }
            })
            .catch(error => {
                document.getElementById('rejectLoading').style.display = 'none';
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            });
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
            currentRequestId = null;
        }

        function showAlert(message, type) {
            const container = document.getElementById('alertContainer');
            const alertId = 'alert-' + Date.now();
            container.insertAdjacentHTML('beforeend', `
                <div id="${alertId}" class="alert alert-${type} show">
                    ${message}
                </div>
            `);

            setTimeout(() => {
                const element = document.getElementById(alertId);
                if (element) element.remove();
            }, 4000);
        }

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

        // Load on page load
        loadRequests();

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>