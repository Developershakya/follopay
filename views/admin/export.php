<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Advanced Data Export - FolloPay Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
           
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ── HEADER ── */
        .header {
            text-align: center;
            margin-bottom: 16px;
            color: white;
            padding: 8px 0;
        }

        .header h1 {
            font-size: clamp(1.4em, 5vw, 2.5em);
            margin: 0 0 6px;
            font-weight: 700;
        }

        .header p {
            font-size: clamp(0.8em, 3vw, 1em);
            margin: 0;
            opacity: 0.85;
        }

        /* ── CARD ── */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: clamp(14px, 4vw, 30px);
            margin-bottom: 20px;
        }

        .card-title {
            margin: 0 0 16px;
            color: #333;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── FILTERS ── */
        .filters-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        @media (max-width: 480px) {
            .filters-section {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 600;
            color: #555;
            margin-bottom: 6px;
            font-size: 0.88em;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .filter-group select,
        .filter-group input {
            padding: 11px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95em;
            width: 100%;
            background: white;
            color: #333;
            transition: border-color 0.25s;
            -webkit-appearance: none;
            appearance: none;
        }

        .filter-group select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23999' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.12);
        }

        /* ── BUTTONS ── */
        .buttons-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn-reset {
            grid-column: 1 / -1;
        }

        @media (min-width: 480px) {
            .buttons-group {
                grid-template-columns: repeat(3, 1fr);
            }

            .btn-reset {
                grid-column: auto;
            }
        }

        button {
            padding: 12px 16px;
            border: none;
            border-radius: 10px;
            font-size: 0.92em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            touch-action: manipulation;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:active {
            transform: scale(0.97);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #444;
        }

        .btn-secondary:active {
            background: #ddd;
        }

        .btn-success {
            background: linear-gradient(135deg, #51cf66, #2f9e44);
            color: white;
        }

        .btn-success:active {
            transform: scale(0.97);
        }

        /* ── SUMMARY STATS ── */
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        @media (min-width: 600px) {
            .summary-stats {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 10px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 0.75em;
            opacity: 0.85;
            margin: 0 0 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .stat-card .value {
            font-size: clamp(1.4em, 4vw, 2em);
            font-weight: 700;
            line-height: 1;
        }

        /* ── TABLE (scrollable on mobile) ── */
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 8px;
            border: 1px solid #eee;
            margin-top: 4px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
            font-size: 0.88em;
        }

        .data-table thead {
            background: #f5f5f5;
            position: sticky;
            top: 0;
        }

        .data-table th {
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
            color: #444;
            border-bottom: 2px solid #ddd;
            white-space: nowrap;
        }

        .data-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        .data-table tbody tr:hover {
            background: #f9f9f9;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ── STATUS BADGES ── */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.82em;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-submitted { background: #fff3cd; color: #856404; }
        .status-approved  { background: #d4edda; color: #155724; }
        .status-rejected  { background: #f8d7da; color: #721c24; }

        /* ── LOADING ── */
        .loading {
            text-align: center;
            padding: 40px 20px;
        }

        .spinner {
            display: inline-block;
            width: 36px;
            height: 36px;
            border: 4px solid rgba(102, 126, 234, 0.25);
            border-radius: 50%;
            border-top-color: #667eea;
            animation: spin 0.9s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── TOAST ── */
        .toast {
            position: fixed;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 20px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            font-size: 0.9em;
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);
            z-index: 9999;
            max-width: calc(100vw - 32px);
            text-align: center;
            animation: toastIn 0.3s ease;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @keyframes toastIn {
            from { opacity: 0; transform: translate(-50%, 20px); }
            to   { opacity: 1; transform: translate(-50%, 0); }
        }

        .toast.success { background: #40c057; }
        .toast.error   { background: #fa5252; }
        .toast.info    { background: #667eea; }

        /* ── NO DATA ── */
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #aaa;
        }

        .no-data i { font-size: 2.5em; margin-bottom: 12px; display: block; }

        /* ── MODAL ── */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            display: flex;
            align-items: flex-end;
            justify-content: center;
            z-index: 1000;
            padding: 0;
        }

        @media (min-width: 600px) {
            .modal {
                align-items: center;
                padding: 20px;
            }
        }

        .modal-content {
            background: white;
            border-radius: 20px 20px 0 0;
            padding: 24px 20px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 -4px 30px rgba(0,0,0,0.2);
            max-height: 88vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
        }

        @media (min-width: 600px) {
            .modal-content {
                border-radius: 16px;
                max-height: 82vh;
                animation: fadeScale 0.25s ease;
            }
        }

        @keyframes slideUp {
            from { transform: translateY(100%); }
            to   { transform: translateY(0); }
        }

        @keyframes fadeScale {
            from { opacity: 0; transform: scale(0.96); }
            to   { opacity: 1; transform: scale(1); }
        }

        /* Drag handle indicator for mobile modal */
        .modal-handle {
            width: 40px;
            height: 4px;
            background: #ddd;
            border-radius: 2px;
            margin: 0 auto 16px;
        }

        @media (min-width: 600px) { .modal-handle { display: none; } }

        .modal h2 {
            margin: 0 0 16px;
            color: #333;
            font-size: 1.1em;
        }

        .column-item {
            display: flex;
            align-items: center;
            padding: 11px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .column-item:last-child { border-bottom: none; }

        .column-item input[type="checkbox"] {
            margin-right: 12px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #667eea;
            flex-shrink: 0;
        }

        .column-item label {
            cursor: pointer;
            flex: 1;
            margin: 0;
            font-size: 0.95em;
            color: #333;
        }

        .preset-section {
            background: #f7f7f7;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 16px;
        }

        .preset-section h3 {
            font-size: 0.88em;
            color: #555;
            margin: 0 0 10px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .preset-buttons {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .preset-btn {
            padding: 6px 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.83em;
            font-weight: 600;
            transition: all 0.2s;
            color: #555;
        }

        .preset-btn:hover,
        .preset-btn:active { background: #667eea; color: white; border-color: #667eea; }

        .preset-input {
            display: flex;
            gap: 8px;
        }

        .preset-input input {
            flex: 1;
            padding: 9px 12px;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 0.9em;
            min-width: 0;
        }

        .preset-input button {
            padding: 9px 14px;
            font-size: 0.88em;
            flex-shrink: 0;
        }

        .modal-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 16px;
        }

        .modal-buttons button {
            padding: 12px;
            border-radius: 10px;
            font-size: 0.95em;
        }

        .columns-scroll {
            max-height: 35vh;
            overflow-y: auto;
            margin-bottom: 4px;
            -webkit-overflow-scrolling: touch;
        }

        /* Scroll hint on table */
        .scroll-hint {
            font-size: 0.78em;
            color: #aaa;
            text-align: right;
            margin-bottom: 4px;
            display: none;
        }

        @media (max-width: 600px) {
            .scroll-hint { display: block; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-download"></i> Data Export</h1>
            <p>Export with custom columns, presets & filters</p>
        </div>

        <div class="card">
            <h2 class="card-title"><i class="fas fa-filter" style="color:#667eea"></i> Filters</h2>

            <div class="filters-section">
                <div class="filter-group">
                    <label>Filter Type</label>
                    <select id="filterType" onchange="updateFilterInput()">
                        <option value="all">All Data</option>
                        <option value="date">By Date</option>
                        <option value="week">By Week</option>
                        <option value="month">By Month</option>
                    </select>
                </div>

                <div class="filter-group" id="filterValueGroup" style="display:none;">
                    <label id="filterValueLabel">Select Date</label>
                    <input type="date" id="filterValue">
                </div>

                <div class="filter-group">
                    <label>App / Post</label>
                    <select id="postFilter">
                        <option value="">All Posts</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>User</label>
                    <select id="userFilter">
                        <option value="">All Users</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Status</label>
                    <select id="statusFilter">
                        <option value="all">All Status</option>
                        <option value="submitted">Submitted</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <div class="buttons-group">
                <button class="btn-primary" onclick="fetchData()">
                    <i class="fas fa-search"></i> Load Data
                </button>
                <button class="btn-success" onclick="showColumnModal()">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
                <button class="btn-secondary btn-reset" onclick="resetFilters()">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>

            <!-- Summary Stats -->
            <div id="summaryStats" class="summary-stats" style="display:none;">
                <div class="stat-card">
                    <h3>Total</h3>
                    <div class="value" id="statTotal">0</div>
                </div>
                <div class="stat-card">
                    <h3>Pending</h3>
                    <div class="value" id="statPending">0</div>
                </div>
                <div class="stat-card">
                    <h3>Approved</h3>
                    <div class="value" id="statApproved">0</div>
                </div>
                <div class="stat-card">
                    <h3>Paid (₹)</h3>
                    <div class="value" id="statPaid">0</div>
                </div>
            </div>

            <!-- Table -->
            <div id="dataContainer">
                <div class="no-data">
                    <i class="fas fa-info-circle"></i>
                    <p>Filters select karein aur "Load Data" dabayein</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let API_PATH = 'ajax/export_api.php';
        let allData = [];
        let filterOptions = {};

        const allColumns = [
            { id: 'user_id',          label: 'User ID' },
            { id: 'user_name',        label: 'User Name' },
            { id: 'email',            label: 'Email' },
            { id: 'phone',            label: 'Phone' },
            { id: 'app_name',         label: 'App Name' },
            { id: 'app_link',         label: 'App Link' },
            { id: 'price',            label: 'Price' },
            { id: 'assigned_time',    label: 'Assigned Time' },
            { id: 'submitted_time',   label: 'Submitted Time' },
            { id: 'status',           label: 'Status' },
            { id: 'days_taken',       label: 'Days Taken' },
            { id: 'assigned_comment', label: 'Comment' },
            { id: 'screenshot_path',  label: 'Screenshot URL' },
            { id: 'reject_reason',    label: 'Reject Reason' }
        ];

        const defaultPreset = ['user_id','user_name','email','app_name','price','assigned_time','submitted_time','status','screenshot_path'];
        let selectedColumns = JSON.parse(localStorage.getItem('selectedColumns')) || defaultPreset;
        let presets = JSON.parse(localStorage.getItem('exportPresets')) || {};

        document.addEventListener('DOMContentLoaded', loadFilterOptions);

        function updateFilterInput() {
            const v = document.getElementById('filterType').value;
            const grp = document.getElementById('filterValueGroup');
            const lbl = document.getElementById('filterValueLabel');
            const inp = document.getElementById('filterValue');

            if (v === 'all') {
                grp.style.display = 'none';
                inp.value = '';
            } else {
                grp.style.display = 'flex';
                if (v === 'date')       { lbl.textContent = 'Select Date';  inp.type = 'date'; }
                else if (v === 'week')  { lbl.textContent = 'Select Week';  inp.type = 'week'; }
                else if (v === 'month') { lbl.textContent = 'Select Month'; inp.type = 'month'; }
            }
        }

        function loadFilterOptions() {
            fetch(`${API_PATH}?action=get_filter_options`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    filterOptions = data;

                    const postSelect = document.getElementById('postFilter');
                    data.posts.forEach(p => {
                        postSelect.innerHTML += `<option value="${p.id}">${p.app_name}</option>`;
                    });

                    const userSelect = document.getElementById('userFilter');
                    data.users.forEach(u => {
                        userSelect.innerHTML += `<option value="${u.id}">${u.username}</option>`;
                    });

                    if (data.date_range?.min_date) {
                        document.getElementById('filterValue').min = data.date_range.min_date;
                        document.getElementById('filterValue').max = data.date_range.max_date;
                    }

                    showToast('✅ Ready! Filters load ho gaye', 'success');
                })
                .catch(err => showToast('Error: ' + err.message, 'error'));
        }

        function fetchData() {
            const params = new URLSearchParams({
                action:       'get_submission_data',
                filter_type:  document.getElementById('filterType').value,
                filter_value: document.getElementById('filterValue').value,
                post_id:      document.getElementById('postFilter').value  || 0,
                user_id:      document.getElementById('userFilter').value  || 0,
                status:       document.getElementById('statusFilter').value
            });

            showLoading();

            fetch(`${API_PATH}?${params}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) throw new Error(data.message);
                    allData = data.data;
                    renderTable(data.data);
                    fetchSummary(
                        document.getElementById('filterType').value,
                        document.getElementById('filterValue').value,
                        document.getElementById('postFilter').value,
                        document.getElementById('userFilter').value
                    );
                    showToast(`✅ ${data.total} records mile`, 'success');
                })
                .catch(err => {
                    showToast('Error: ' + err.message, 'error');
                    renderNoData();
                });
        }

        function fetchSummary(filterType, filterValue, postId, userId) {
            const params = new URLSearchParams({
                action: 'get_summary',
                filter_type: filterType,
                filter_value: filterValue,
                post_id: postId || 0,
                user_id: userId || 0
            });

            fetch(`${API_PATH}?${params}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success || !data.summary) return;
                    const s = data.summary;
                    document.getElementById('summaryStats').style.display = 'grid';
                    document.getElementById('statTotal').textContent   = s.total_submissions || 0;
                    document.getElementById('statPending').textContent  = s.pending || 0;
                    document.getElementById('statApproved').textContent = s.approved || 0;
                    document.getElementById('statPaid').textContent     = (parseFloat(s.total_paid) || 0).toFixed(2);
                })
                .catch(() => {});
        }

        function renderTable(data) {
            const container = document.getElementById('dataContainer');
            if (!data || data.length === 0) { renderNoData(); return; }

            let html = `
                <p class="scroll-hint"><i class="fas fa-arrows-left-right"></i> Scroll to see all columns</p>
                <div class="table-wrapper">
                <table class="data-table"><thead><tr>`;

            selectedColumns.forEach(colId => {
                const col = allColumns.find(c => c.id === colId);
                html += `<th>${col ? col.label : colId}</th>`;
            });
            html += `</tr></thead><tbody>`;

            data.forEach(row => {
                html += `<tr>`;
                selectedColumns.forEach(colId => {
                    let value = row[colId] || '-';

                    if ((colId === 'assigned_time' || colId === 'submitted_time') && value && value !== '-') {
                        try {
                            value = new Date(value).toLocaleDateString('en-IN', {
                                day: '2-digit', month: '2-digit', year: '2-digit',
                                hour: '2-digit', minute: '2-digit', hour12: true
                            });
                        } catch(e) {}
                    } else if (colId === 'status') {
                        value = `<span class="status-badge status-${value}">${value.charAt(0).toUpperCase() + value.slice(1)}</span>`;
                    } else if (colId === 'price' && value !== '-') {
                        value = `₹${parseFloat(value).toFixed(2)}`;
                    } else if (colId === 'screenshot_path') {
                        value = (value && value !== '-')
                            ? `<a href="${value}" target="_blank" style="color:#667eea;font-size:1.2em;" title="Screenshot"><i class="fas fa-image"></i></a>`
                            : `<span style="color:#ccc;"><i class="fas fa-image"></i></span>`;
                    }

                    html += `<td>${value}</td>`;
                });
                html += `</tr>`;
            });

            html += `</tbody></table></div>`;
            container.innerHTML = html;
        }

        function renderNoData() {
            document.getElementById('dataContainer').innerHTML = `
                <div class="no-data">
                    <i class="fas fa-inbox"></i>
                    <p>Koi data nahi mila</p>
                </div>`;
        }

        function showColumnModal() {
            if (allData.length === 0) { showToast('Pehle data load karein', 'error'); return; }

            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.id = 'columnModal';

            let columnsHtml = '';
            allColumns.forEach(col => {
                const checked = selectedColumns.includes(col.id);
                columnsHtml += `
                    <div class="column-item">
                        <input type="checkbox" id="col_${col.id}" ${checked ? 'checked' : ''}>
                        <label for="col_${col.id}">${col.label}</label>
                    </div>`;
            });

            let presetsHtml = '<div class="preset-buttons">';
            Object.keys(presets).forEach(name => {
                presetsHtml += `<button class="preset-btn" onclick="loadPreset('${name}')">${name}</button>`;
            });
            presetsHtml += '</div>';

            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-handle"></div>
                    <h2><i class="fas fa-columns" style="color:#667eea"></i> Columns Select Karein</h2>

                    <div class="preset-section">
                        <h3>📌 My Presets</h3>
                        ${presetsHtml}
                        <div class="preset-input">
                            <input type="text" id="presetName" placeholder="Preset ka naam...">
                            <button class="btn-secondary" onclick="savePreset()">Save</button>
                        </div>
                    </div>

                    <div class="columns-scroll">${columnsHtml}</div>

                    <div class="modal-buttons">
                        <button class="btn-secondary" onclick="closeModal()">Cancel</button>
                        <button class="btn-success" onclick="exportData()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>`;

            // Close on backdrop tap
            modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
            document.body.appendChild(modal);
        }

        function closeModal() {
            const modal = document.getElementById('columnModal');
            if (modal) modal.remove();
        }

        function loadPreset(presetName) {
            selectedColumns = [...presets[presetName]];
            localStorage.setItem('selectedColumns', JSON.stringify(selectedColumns));
            showToast(`✅ Preset loaded: ${presetName}`, 'success');
            closeModal();
            setTimeout(showColumnModal, 120);
        }

        function savePreset() {
            const name = document.getElementById('presetName').value.trim();
            if (!name) { showToast('Preset naam likhein', 'error'); return; }

            const current = [];
            allColumns.forEach(col => {
                const cb = document.getElementById(`col_${col.id}`);
                if (cb && cb.checked) current.push(col.id);
            });

            presets[name] = current;
            localStorage.setItem('exportPresets', JSON.stringify(presets));
            showToast(`✅ Preset save: ${name}`, 'success');
            closeModal();
            setTimeout(showColumnModal, 120);
        }

        function exportData() {
            selectedColumns = [];
            allColumns.forEach(col => {
                const cb = document.getElementById(`col_${col.id}`);
                if (cb && cb.checked) selectedColumns.push(col.id);
            });

            if (selectedColumns.length === 0) { showToast('Kam se kam ek column select karein', 'error'); return; }
            localStorage.setItem('selectedColumns', JSON.stringify(selectedColumns));

            let csv = '\uFEFF';
            const headers = selectedColumns.map(id => {
                const col = allColumns.find(c => c.id === id);
                return col ? col.label : id;
            });
            csv += headers.map(h => `"${h}"`).join(',') + '\n';

            allData.forEach(row => {
                const values = selectedColumns.map(colId => {
                    let v = row[colId] || '';
                    if ((colId === 'assigned_time' || colId === 'submitted_time') && v) {
                        try {
                            v = new Date(v).toLocaleDateString('en-IN', {
                                day: '2-digit', month: '2-digit', year: '2-digit',
                                hour: '2-digit', minute: '2-digit', hour12: true
                            });
                        } catch(e) {}
                    } else if (colId === 'price' && v) {
                        v = '₹' + parseFloat(v).toFixed(2);
                    }
                    return `"${String(v).replace(/"/g, '""')}"`;
                });
                csv += values.join(',') + '\n';
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `export_${new Date().toISOString().split('T')[0]}.csv`;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            closeModal();
            renderTable(allData);
            showToast('✅ File export ho gayi!', 'success');
        }

        function resetFilters() {
            document.getElementById('filterType').value = 'all';
            document.getElementById('filterValue').value = '';
            document.getElementById('postFilter').value = '';
            document.getElementById('userFilter').value = '';
            document.getElementById('statusFilter').value = 'all';
            document.getElementById('filterValueGroup').style.display = 'none';
            document.getElementById('summaryStats').style.display = 'none';
            renderNoData();
            allData = [];
        }

        function showLoading() {
            document.getElementById('dataContainer').innerHTML = `
                <div class="loading">
                    <div class="spinner"></div>
                    <p style="margin-top:16px;color:#aaa;font-size:0.9em;">Loading...</p>
                </div>`;
        }

        function showToast(message, type = 'info') {
            // Remove existing toast
            document.querySelectorAll('.toast').forEach(t => t.remove());
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>