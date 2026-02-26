<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
     <?php include 'header.php'; ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --purple: #7B5EA7;
            --purple-light: #9B7CC7;
            --purple-dark: #5C3D8F;
            --purple-bg: #F3EEFF;
            --mint: #4CAF82;
            --mint-light: #E8F7F0;
            --gray-bg: #F7F8FC;
            --text-dark: #1A1A2E;
            --text-mid: #555;
            --text-light: #999;
            --card-shadow: 0 4px 20px rgba(123,94,167,0.12);
            --radius: 18px;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: var(--gray-bg);
            color: var(--text-dark);
        }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            padding: 18px 20px 10px;
            background: white;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 0 rgba(0,0,0,0.06);
        }
        .header .back-btn {
            font-size: 20px;
            color: var(--text-dark);
            cursor: pointer;
            margin-right: 12px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.2s;
        }
        .header .back-btn:hover { background: var(--purple-bg); }
        .header h1 { font-size: 20px; font-weight: 800; color: var(--text-dark); }

        

        /* Balance Card */
        .balance-card {
            background: linear-gradient(135deg, var(--purple) 0%, var(--purple-dark) 100%);
            border-radius: var(--radius);
            padding: 24px 22px;
            color: white;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        .balance-card::before {
            content: '';
            position: absolute;
            top: -30px; right: -30px;
            width: 140px; height: 140px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }
        .balance-card::after {
            content: '';
            position: absolute;
            bottom: -50px; right: 30px;
            width: 100px; height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }
        .balance-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            opacity: 0.85;
            margin-bottom: 8px;
        }
        .balance-label .icon-wrap {
            width: 28px; height: 28px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px;
        }
        .balance-amount {
            font-size: 42px;
            font-weight: 900;
            letter-spacing: -1px;
            margin-bottom: 6px;
        }
        .balance-sub {
            font-size: 12px;
            opacity: 0.75;
            font-weight: 600;
        }

        /* Amount Input Section */
        .section-card {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: var(--card-shadow);
        }
        .section-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .amount-input-wrap {
            position: relative;
            margin-bottom: 14px;
        }
        .amount-input-wrap .rupee-sign {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            font-weight: 800;
            color: var(--purple);
        }
        .amount-input-wrap input {
            width: 100%;
            padding: 16px 16px 16px 36px;
            border: 2px solid var(--purple-bg);
            border-radius: 14px;
            font-size: 22px;
            font-weight: 800;
            font-family: 'Nunito', sans-serif;
            color: var(--text-dark);
            outline: none;
            transition: border-color 0.2s;
            background: var(--gray-bg);
        }
        .amount-input-wrap input:focus {
            border-color: var(--purple);
            background: white;
        }
        .amount-input-wrap input::placeholder { color: #ccc; font-weight: 700; }

        /* Quick Amount Chips */
        .quick-amounts {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .quick-amounts .chip {
            padding: 8px 16px;
            border-radius: 100px;
            border: 2px solid var(--purple-bg);
            background: var(--purple-bg);
            color: var(--purple);
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Nunito', sans-serif;
        }
        .quick-amounts .chip:hover, .quick-amounts .chip.active {
            background: var(--purple);
            border-color: var(--purple);
            color: white;
            transform: translateY(-1px);
        }

        /* Withdraw To Section */
        .withdraw-to-label {
            font-size: 16px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 12px;
        }
        .payment-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }
        .payment-option {
            border: 2px solid #EBEBF0;
            border-radius: 14px;
            padding: 14px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            background: white;
        }
        .payment-option.selected {
            border-color: var(--purple);
            background: var(--purple-bg);
        }
        .payment-option.disabled {
            opacity: 0.45;
            cursor: not-allowed;
            pointer-events: none;
        }
        .payment-option .check-icon {
            position: absolute;
            top: 10px; right: 10px;
            width: 20px; height: 20px;
            border-radius: 50%;
            background: var(--purple);
            color: white;
            font-size: 11px;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .payment-option.selected .check-icon { display: flex; }

        .payment-option .opt-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            margin-bottom: 8px;
        }
        .payment-option.upi-opt .opt-icon { background: #E8F0FE; color: #4285F4; }
        .payment-option.ff-opt .opt-icon { background: #FFF0E0; color: #FF7A00; }
        .payment-option .opt-name { font-size: 14px; font-weight: 800; color: var(--text-dark); }
        .payment-option .opt-desc { font-size: 11px; color: var(--text-light); font-weight: 600; margin-top: 2px; }

        /* UPI Linked Badge */
        .upi-linked-badge {
            display: none;
            margin-top: 5px;
        }
        .upi-linked-badge span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #E8F7F0;
            color: #4CAF82;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 100px;
            border: 1.5px solid #C3E8D5;
        }

        /* UPI ID display */
        .upi-connected {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            background: var(--mint-light);
            border-radius: 12px;
            border: 1.5px solid #C3E8D5;
        }
        .upi-connected .upi-logo {
            width: 32px; height: 32px;
            background: #E8F0FE;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; color: #4285F4; font-weight: 800;
        }
        .upi-connected .upi-info { flex: 1; }
        .upi-connected .upi-id-text { font-size: 13px; font-weight: 800; color: var(--text-dark); }
        .upi-connected .connected-badge {
            display: flex; align-items: center; gap: 4px;
            font-size: 11px; font-weight: 700; color: var(--mint);
        }

        /* Mode Selection */
        .mode-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .mode-opt {
            border: 2px solid #EBEBF0;
            border-radius: 12px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .mode-opt.selected { border-color: var(--purple); background: var(--purple-bg); }
        .mode-opt.disabled { opacity: 0.4; cursor: not-allowed; pointer-events: none; }
        .mode-opt .mode-title { font-size: 13px; font-weight: 800; color: var(--text-dark); }
        .mode-opt .mode-sub { font-size: 11px; color: var(--text-light); font-weight: 600; margin-top: 3px; }
        .mode-opt.selected .mode-title { color: var(--purple); }

        /* Charge info */
        .charge-info-box {
            background: var(--purple-bg);
            border-radius: 12px;
            padding: 14px 16px;
            margin-top: 14px;
        }
        .charge-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-mid);
            margin-bottom: 6px;
        }
        .charge-row:last-child { margin-bottom: 0; }
        .charge-row .val { font-weight: 800; color: var(--text-dark); }
        .charge-row .val.red { color: #E53935; }
        .charge-row .val.green { color: var(--mint); font-size: 15px; }
        .divider-row { border-top: 1.5px dashed #D5C5F0; margin: 8px 0; }

        /* Meta info row */
        .meta-info {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
            padding: 10px 14px;
            background: var(--gray-bg);
            border-radius: 10px;
            margin-top: 12px;
        }
        .meta-pill {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-mid);
        }
        .meta-sep { color: #D0D0D0; font-size: 16px; }

        /* Notice */
        .notice-card {
            background: #FFFBF0;
            border-left: 4px solid #FFB800;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 16px;
        }
        .notice-title {
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; font-weight: 800; color: #7A5C00;
            margin-bottom: 10px;
        }
        .notice-list { list-style: none; }
        .notice-list li {
            font-size: 12px;
            font-weight: 600;
            color: #7A5C00;
            margin-bottom: 6px;
            padding-left: 14px;
            position: relative;
            line-height: 1.5;
        }
        .notice-list li::before {
            content: '•';
            position: absolute;
            left: 0;
            color: #FFB800;
        }
        .notice-list li.red { color: #C0392B; }
        .notice-list li.red::before { color: #C0392B; }

        /* Withdrawal History */
        .history-header {
            display: flex; align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .history-title { font-size: 17px; font-weight: 800; }
        .refresh-btn {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: var(--purple-bg);
            border: none;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--purple);
            font-size: 14px;
            transition: all 0.2s;
        }
        .refresh-btn:hover { background: var(--purple); color: white; }

        .history-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            background: var(--gray-bg);
            border-radius: 14px;
            margin-bottom: 10px;
            transition: background 0.2s;
        }
        .history-item:hover { background: var(--purple-bg); }
        .history-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .history-icon.upi { background: #E8F0FE; color: #4285F4; }
        .history-icon.ff { background: #FFF0E0; color: #FF7A00; }
        .history-details { flex: 1; }
        .history-name { font-size: 14px; font-weight: 800; color: var(--text-dark); }
        .history-date { font-size: 11px; color: var(--text-light); font-weight: 600; margin-top: 2px; }
        .history-sub { font-size: 11px; color: var(--text-light); font-weight: 600; margin-top: 1px; }
        .history-right { text-align: right; }
        .history-amount { font-size: 17px; font-weight: 900; color: var(--text-dark); }
        .history-charge { font-size: 11px; color: #E53935; font-weight: 700; }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 800;
            margin-top: 4px;
        }
        .status-badge.pending { background: #FFF9C4; color: #F57F17; }
        .status-badge.approved { background: var(--mint-light); color: var(--mint); }
        .status-badge.failed { background: #FFEBEE; color: #C62828; }
        .status-badge.refunded { background: #E3F2FD; color: #1565C0; }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, var(--purple) 0%, var(--purple-dark) 100%);
            color: white;
            border: none;
            border-radius: 100px;
            font-size: 16px;
            font-weight: 800;
            font-family: 'Nunito', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 6px;
            box-shadow: 0 6px 20px rgba(123,94,167,0.35);
        }
        .submit-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(123,94,167,0.45);
        }
        .submit-btn:active:not(:disabled) { transform: translateY(0); }
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }
        .empty-state i { font-size: 40px; margin-bottom: 10px; opacity: 0.3; }
        .empty-state p { font-size: 14px; font-weight: 700; }

        /* Toast */
        #toastContainer {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: calc(100% - 32px);
            max-width: 440px;
        }
        .toast {
            padding: 14px 18px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: toastIn 0.3s ease;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .toast.success { background: var(--mint); }
        .toast.error { background: #E53935; }
        @keyframes toastIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* Free Fire form */
        .ff-cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .ff-card {
            border: 2px solid #EBEBF0;
            border-radius: 14px;
            padding: 12px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .ff-card.selected {
            border-color: var(--purple);
            background: var(--purple-bg);
        }
        .ff-card img { width: 44px; height: 44px; object-fit: cover; margin-bottom: 6px; }
        .ff-card .ff-price { font-size: 16px; font-weight: 900; color: var(--purple); }
        .ff-card .ff-diamonds { font-size: 11px; font-weight: 700; color: var(--text-mid); }

        .uid-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--purple-bg);
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Nunito', sans-serif;
            color: var(--text-dark);
            outline: none;
            transition: border-color 0.2s;
            background: var(--gray-bg);
        }
        .uid-input:focus { border-color: var(--purple); background: white; }

        /* UPI Loading Skeleton */
        .upi-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.2s infinite;
            border-radius: 10px;
            height: 48px;
            margin-bottom: 14px;
        }
        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .bottom-spacer { height: 24px; }
    </style>
</head>
<body>

<!-- Header -->
<!-- <div class="header">
    <div class="back-btn" onclick="history.back()"><i class="fas fa-chevron-left"></i></div>
    <h1>Withdraw</h1>
</div> -->

<div class="page-content">

    <!-- Balance Card -->
    <div class="balance-card">
        <div class="balance-label">
            <div class="icon-wrap"><i class="fas fa-wallet"></i></div>
            Available Balance
        </div>
        <div class="balance-amount" id="availableBalance">₹0</div>
        <div class="balance-sub">Withdraw anytime to your UPI or Free Fire</div>
    </div>

    <!-- Withdraw To -->
    <div class="section-card">
        <div class="withdraw-to-label">Withdraw To</div>
        <div class="payment-options">
            <!-- UPI Option Card -->
            <div class="payment-option upi-opt selected" id="upiOption" onclick="selectPaymentType('upi')">
                <div class="check-icon"><i class="fas fa-check"></i></div>
                <div class="opt-icon"><i class="fas fa-university"></i></div>
                <div class="opt-name">UPI</div>
                <div class="opt-desc" id="upiCardDesc">Instant &amp; Duration</div>
                <!-- Green linked badge — shown only when UPI is saved -->
                <div class="upi-linked-badge" id="upiSavedBadge">
                
                </div>
            </div>

            <!-- Free Fire Option Card -->
            <div class="payment-option ff-opt" id="ffOption" onclick="selectPaymentType('free_fire')">
                <div class="check-icon"><i class="fas fa-check"></i></div>
                <div class="opt-icon"><i class="fas fa-gamepad"></i></div>
                <div class="opt-name">Free Fire</div>
                <div class="opt-desc" id="ffCardDesc">Get Diamonds</div>
            </div>
        </div>

        <!-- UPI Form -->
        <div id="upiForm">

            <!-- hidden span for JS reference -->
            <span id="savedUpiDisplay" style="display:none;"></span>

            <!-- Skeleton loader shown while API loads -->
            <div id="upiLoadingSkeleton" class="upi-skeleton"></div>

            <!-- CASE 1: UPI already saved — show linked info box -->
            <div id="upiConnectedBox" style="display:none; margin-bottom:14px;">
                <div class="upi-connected">
                    <div class="upi-logo"><i class="fas fa-university"></i></div>
                    <div class="upi-info">
                        <div class="upi-id-text" id="upiConnectedId"></div>
                    </div>
                    <div class="connected-badge"><i class="fas fa-check-circle"></i> Linked</div>
                </div>
            </div>

            <!-- CASE 2: First time — UPI not set, show input field -->
            <div id="upiFirstTimeBox" style="display:none; margin-bottom:14px;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <div class="section-label" style="margin-bottom:0;">Your UPI ID</div>
                    <span style="font-size:11px; background:#FFF9F0; border:1.5px solid #FFE0B0; color:#D67800; font-weight:700; padding:2px 8px; border-radius:100px;">First time setup</span>
                </div>
                <div style="position:relative;">
                    <input type="text" id="upiNewInput" class="uid-input" placeholder="yourname@upi (e.g. 9876543210@paytm)" style="padding-right:44px;">
                    <span style="position:absolute; right:14px; top:50%; transform:translateY(-50%); font-size:18px; color:var(--purple);">@</span>
                </div>
                <div style="font-size:12px; color:var(--text-light); font-weight:600; margin-top:6px;">
                    <i class="fas fa-lock" style="color:var(--purple);"></i> 
                    Once saved, your UPI ID will be permanently linked and <strong>cannot be changed</strong>.
                </div>
            </div>

            <!-- Amount Input -->
            <div class="section-label">Enter Amount</div>
            <div class="amount-input-wrap">
                <span class="rupee-sign">₹</span>
                <input type="number" id="upiAmount" placeholder="0" min="5" step="5" oninput="calculateCharges()">
            </div>

            <!-- Quick Amounts -->
            <div class="quick-amounts" style="margin-bottom:16px;">
                <div class="chip" onclick="setAmount(10, event)">₹10</div>
                <div class="chip" onclick="setAmount(20, event)">₹20</div>
                <div class="chip" onclick="setAmount(50, event)">₹50</div>
                <div class="chip" onclick="setAmount(100, event)">₹100</div>
                <div class="chip" onclick="setAmount(200, event)">₹200</div>
                <div class="chip" onclick="setAmount(500, event)">₹500</div>
            </div>

            <!-- Withdrawal Mode -->
            <div class="section-label">Withdrawal Mode</div>
            <div class="mode-options" style="margin-bottom:14px;">
                <div class="mode-opt selected" id="instantMode" onclick="selectMode('instant')">
                    <div class="mode-title">⚡ Instant</div>
                    <div class="mode-sub">20% charge</div>
                    <div class="mode-sub">Min ₹5 • 1-7 days</div>
                </div>
                <div class="mode-opt" id="durationMode" onclick="selectMode('duration')">
                    <div class="mode-title">🗓 Duration</div>
                    <div class="mode-sub">No charge</div>
                    <div class="mode-sub" id="durationAvailText">10th–17th only</div>
                </div>
            </div>

            <!-- Charge Preview -->
            <div class="charge-info-box" id="chargeBox" style="display:none;">
                <div class="charge-row"><span>Amount</span><span class="val" id="calcAmt">₹0</span></div>
                <div class="charge-row"><span>Charge (20%)</span><span class="val red" id="calcCharge">₹0</span></div>
                <div class="divider-row"></div>
                <div class="charge-row"><span>You Receive</span><span class="val green" id="calcFinal">₹0</span></div>
            </div>

            <!-- Meta -->
            <div class="meta-info">
                <div class="meta-pill"><i class="fas fa-info-circle" style="color:var(--purple)"></i> Min: ₹5</div>
                <span class="meta-sep">|</span>
                <div class="meta-pill">Max: ₹10,000</div>
                <span class="meta-sep">|</span>
                <div class="meta-pill">Charge: 20% (Instant)</div>
            </div>

            <button class="submit-btn" style="margin-top:16px;" id="upiSubmitBtn" onclick="submitUPIWithdrawal()" disabled>
                <i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i> Loading...
            </button>
        </div>

        <!-- Free Fire Form -->
        <div id="ffForm" style="display:none;">
            <div class="section-label" style="margin-bottom:10px;">Select Diamond Pack</div>
            <div class="ff-cards-grid" id="ffCardsGrid">
                <div style="grid-column:1/-1; text-align:center; padding:20px; color:var(--text-light); font-size:13px;">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            </div>

            <div style="margin-top:16px;">
                <div class="section-label">Free Fire UID</div>
                <div id="ffUIDSavedBox" class="upi-connected" style="margin-bottom:14px; display:none;">
                    <div class="upi-logo" style="background:#FFF0E0; color:#FF7A00;"><i class="fas fa-gamepad"></i></div>
                    <div class="upi-info">
                        <div class="upi-id-text" id="savedFFDisplay"></div>
                    </div>
                    <div class="connected-badge"><i class="fas fa-check-circle"></i> Saved</div>
                </div>
                <input type="text" id="ffUID" class="uid-input" placeholder="Enter your 8-10 digit Free Fire UID" maxlength="10">
                <div style="font-size:11px; color:var(--text-light); font-weight:600; margin-top:6px;">
                    <i class="fas fa-info-circle"></i> Processing time: 5-7 days
                </div>
            </div>

            <button class="submit-btn" style="margin-top:16px; background:linear-gradient(135deg,#FF7A00,#E05A00);" onclick="submitFFWithdrawal()">
                <i class="fas fa-gem" style="margin-right:8px;"></i> Get Diamonds
            </button>
        </div>
    </div>

    <!-- Important Notice -->
    <div class="notice-card">
        <div class="notice-title"><i class="fas fa-exclamation-triangle" style="color:#FFB800;"></i> Important Notice</div>
        <ul class="notice-list">
            <li>Entering incorrect UPI ID or Free Fire UID may result in permanent loss of funds.</li>
            <li>Fake or duplicate accounts are not eligible for payment.</li>
            <li>Account name must match the UPI holder's name. Mismatch will lead to rejection.</li>
            <li class="red">Rejected payments are <strong>non-refundable</strong>.</li>
            <li>Multiple accounts withdrawing to the same UPI ID will be permanently banned.</li>
            <li>Instant withdrawals have a <strong>20% charge</strong>.</li>
            <li>Standard withdrawals available only <strong>10th–17th of each month</strong>.</li>
        </ul>
    </div>

    <!-- Withdrawal History -->
    <div class="section-card">
        <div class="history-header">
            <div class="history-title">Withdrawal History</div>
            <button class="refresh-btn" onclick="loadHistory()"><i class="fas fa-sync-alt"></i></button>
        </div>
        <div id="withdrawalHistory">
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin" style="opacity:0.5;"></i>
                <p style="margin-top:8px;">Loading...</p>
            </div>
        </div>
    </div>

    <div class="bottom-spacer"></div>
</div>

<!-- Toast Container -->
<div id="toastContainer"></div>

<script>
let currentType = 'upi';
let currentMode = 'instant';
let selectedFFAmount = 0;
let savedUpiId = null;   // null = not loaded yet, '' = no UPI saved, 'xyz@upi' = saved
let savedFFUid = null;
let upiLoaded = false;

document.addEventListener('DOMContentLoaded', () => {
    // On page load: keep everything hidden, show skeleton loader
    // upiFirstTimeBox and upiConnectedBox both hidden — API will decide
    // Button starts disabled with spinner
    loadBalance();
    loadHistory();
    loadFFCards();
    loadSavedPaymentMethods();
    initDurationAvailability();
});

function initDurationAvailability() {
    const day = new Date().getDate();
    const available = (day >= 10 && day <= 17);
    const durationEl = document.getElementById('durationMode');
    const durationText = document.getElementById('durationAvailText');
    if (!available) {
        durationEl.classList.add('disabled');
        durationText.textContent = 'Available 10th–17th';
        durationText.style.color = '#E53935';
    } else {
        durationText.textContent = 'Min ₹10 • 5-7 days';
    }
}

function loadBalance() {
    fetch('ajax/wallet.php?action=get_balance')
        .then(r => r.json())
        .then(d => {
            if (d.success && d.wallet) {
                document.getElementById('availableBalance').textContent = '₹' + d.wallet.wallet_balance;
            }
        }).catch(() => {});
}

function loadSavedPaymentMethods() {
    fetch('ajax/wallet.php?action=get_saved_payment_methods')
        .then(r => r.json())
        .then(d => {
            upiLoaded = true;

            // Hide skeleton loader
            document.getElementById('upiLoadingSkeleton').style.display = 'none';

            if (d.success && d.upi_id) {
                // ✅ UPI IS SAVED — show linked box, hide input
                savedUpiId = d.upi_id;

                document.getElementById('savedUpiDisplay').textContent = d.upi_id;

                // Show the green connected info box
                document.getElementById('upiConnectedBox').style.display = 'block';
                document.getElementById('upiConnectedId').textContent = d.upi_id;

                // Hide first-time input box
                document.getElementById('upiFirstTimeBox').style.display = 'none';

                // Update UPI card desc + show green linked badge
                document.getElementById('upiCardDesc').textContent = d.upi_id;
                document.getElementById('upiCardDesc').style.color = 'var(--purple)';
                document.getElementById('upiCardDesc').style.fontWeight = '800';
                document.getElementById('upiSavedBadge').style.display = 'block';

                // Button ready to submit withdrawal
                document.getElementById('upiSubmitBtn').disabled = false;
                document.getElementById('upiSubmitBtn').innerHTML = '<i class="fas fa-paper-plane" style="margin-right:8px;"></i> Submit Withdrawal';

            } else {
                // ❌ NO UPI SAVED — show first time input box
                savedUpiId = '';

                document.getElementById('upiConnectedBox').style.display = 'none';
                document.getElementById('upiFirstTimeBox').style.display = 'block';

                // Reset UPI card desc
                document.getElementById('upiCardDesc').textContent = 'Instant & Duration';
                document.getElementById('upiCardDesc').style.color = '';
                document.getElementById('upiCardDesc').style.fontWeight = '';
                document.getElementById('upiSavedBadge').style.display = 'none';

                // Button ready to save UPI
                document.getElementById('upiSubmitBtn').disabled = false;
                document.getElementById('upiSubmitBtn').innerHTML = '<i class="fas fa-link" style="margin-right:8px;"></i> Save UPI & Withdraw';
            }

            // Free Fire UID handling
            if (d.success && d.free_fire_uid) {
                savedFFUid = d.free_fire_uid;
                document.getElementById('savedFFDisplay').textContent = 'UID: ' + d.free_fire_uid;
                document.getElementById('ffUID').value = d.free_fire_uid;
                document.getElementById('ffUID').readOnly = true;
                document.getElementById('ffUID').style.background = '#F5F5F5';
                document.getElementById('ffUIDSavedBox').style.display = 'flex';
                document.getElementById('ffCardDesc').textContent = 'UID: ' + d.free_fire_uid;
                document.getElementById('ffCardDesc').style.color = 'var(--purple)';
                document.getElementById('ffCardDesc').style.fontWeight = '800';
            }
        })
        .catch(() => {
            // Network error — fallback: show input box
            upiLoaded = true;
            savedUpiId = '';

            document.getElementById('upiLoadingSkeleton').style.display = 'none';
            document.getElementById('upiFirstTimeBox').style.display = 'block';
            document.getElementById('upiConnectedBox').style.display = 'none';
            document.getElementById('upiSavedBadge').style.display = 'none';

            document.getElementById('upiSubmitBtn').disabled = false;
            document.getElementById('upiSubmitBtn').innerHTML = '<i class="fas fa-link" style="margin-right:8px;"></i> Save UPI & Withdraw';
        });
}

function loadFFCards() {
    fetch('ajax/wallet.php?action=get_free_fire_cards')
        .then(r => r.json())
        .then(d => {
            const grid = document.getElementById('ffCardsGrid');
            if (d.success && d.cards && d.cards.length) {
                let html = '';
                d.cards.forEach((card, i) => {
                    html += `
                        <div class="ff-card ${i === 0 ? 'selected' : ''}" onclick="selectFFCard(${card.rupees}, this)">
                            <img src="https://dukaan.b-cdn.net/700x700/webp/3778160/e5254215-6c9c-4c87-88d1-fcc35eaecb66/1624892361684-0495b49d-93e8-4bdc-98a8-d2d2d87b84a8.jpeg" alt="Diamond">
                            <div class="ff-price">₹${card.rupees}</div>
                            <div class="ff-diamonds">${card.diamonds} 💎</div>
                        </div>
                    `;
                });
                grid.innerHTML = html;
                if (d.cards.length) selectedFFAmount = d.cards[0].rupees;
            } else {
                grid.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:20px; color:var(--text-light); font-size:13px;">No cards available</div>';
            }
        }).catch(() => {});
}

function loadHistory() {
    const container = document.getElementById('withdrawalHistory');
    container.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin" style="opacity:0.5;"></i><p style="margin-top:8px;">Loading...</p></div>';
    
    fetch('ajax/wallet.php?action=get_withdrawal_history')
        .then(r => r.json())
        .then(d => {
            const list = d.withdrawals?.withdrawals || [];
            if (!list.length) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><p>No withdrawal history yet</p></div>';
                return;
            }
            let html = '';
            list.forEach(w => {
                const isUpi = w.type === 'upi';
                const dateStr = new Date(w.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
                const statusClass = { approved: 'approved', pending: 'pending', failed: 'failed', refunded: 'refunded' }[w.status] || 'pending';
                html += `
                    <div class="history-item">
                        <div class="history-icon ${isUpi ? 'upi' : 'ff'}">
                            <i class="fas ${isUpi ? 'fa-university' : 'fa-gamepad'}"></i>
                        </div>
                        <div class="history-details">
                            <div class="history-name">${isUpi ? 'UPI Withdrawal' : 'Free Fire Diamonds'}</div>
                            <div class="history-date">${dateStr}</div>
                            <div class="history-sub">${w.upi_id || (w.free_fire_uid ? 'UID: ' + w.free_fire_uid : '')}</div>
                        </div>
                        <div class="history-right">
                            <div class="history-amount">₹${w.amount}</div>
                            ${w.charge_amount > 0 ? `<div class="history-charge">-₹${w.charge_amount}</div>` : ''}
                            <span class="status-badge ${statusClass}">${w.status}</span>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }).catch(() => {
            container.innerHTML = '<div class="empty-state" style="color:#E53935;"><i class="fas fa-exclamation-circle"></i><p>Failed to load history</p></div>';
        });
}

function selectPaymentType(type) {
    currentType = type;
    document.getElementById('upiOption').classList.toggle('selected', type === 'upi');
    document.getElementById('ffOption').classList.toggle('selected', type === 'free_fire');
    document.getElementById('upiForm').style.display = type === 'upi' ? 'block' : 'none';
    document.getElementById('ffForm').style.display = type === 'free_fire' ? 'block' : 'none';
}

function selectMode(mode) {
    const day = new Date().getDate();
    if (mode === 'duration' && (day < 10 || day > 17)) return;
    currentMode = mode;
    document.getElementById('instantMode').classList.toggle('selected', mode === 'instant');
    document.getElementById('durationMode').classList.toggle('selected', mode === 'duration');
    calculateCharges();
}

function setAmount(amt, e) {
    document.getElementById('upiAmount').value = amt;
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    if (e && e.target) e.target.classList.add('active');
    calculateCharges();
}

function calculateCharges() {
    const amount = parseFloat(document.getElementById('upiAmount').value) || 0;
    const box = document.getElementById('chargeBox');
    if (amount <= 0) { box.style.display = 'none'; return; }
    
    let charge = 0, final = amount;
    if (currentMode === 'instant') { charge = amount * 0.2; final = amount - charge; }
    
    document.getElementById('calcAmt').textContent = '₹' + amount;
    document.getElementById('calcCharge').textContent = '₹' + charge.toFixed(2);
    document.getElementById('calcFinal').textContent = '₹' + final.toFixed(2);
    box.style.display = 'block';
}

function selectFFCard(amount, el) {
    selectedFFAmount = amount;
    document.querySelectorAll('.ff-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
}

async function submitUPIWithdrawal() {
    const amount = parseFloat(document.getElementById('upiAmount').value);
    if (!amount || amount < 5) { showToast('Minimum amount is ₹5', 'error'); return; }

    const btn = document.getElementById('upiSubmitBtn');

    // ✅ CASE 1: UPI already saved in DB — directly submit withdrawal
    if (savedUpiId) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i> Processing...';

        const fd = new FormData();
        fd.append('action', 'request_withdraw');
        fd.append('type', 'upi');
        fd.append('amount', amount);
        fd.append('withdraw_mode', currentMode);
        fd.append('upi_id', savedUpiId);

        try {
            const res = await fetch('ajax/wallet.php', { method: 'POST', body: fd });
            const data = await res.json();
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                loadBalance();
                loadHistory();
                document.getElementById('upiAmount').value = '';
                document.getElementById('chargeBox').style.display = 'none';
                document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
            }
        } catch(e) {
            showToast('Network error. Please try again.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane" style="margin-right:8px;"></i> Submit Withdrawal';
        }
        return;
    }

    // ❌ CASE 2: First time — get UPI from input, save it, then withdraw
    const inputVal = (document.getElementById('upiNewInput')?.value || '').trim();
    if (!inputVal) {
        showToast('Please enter your UPI ID', 'error');
        return;
    }
    if (!inputVal.includes('@')) {
        showToast('Invalid UPI ID. Format: name@bank', 'error');
        return;
    }

    // Step 1: Save UPI ID
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i> Saving UPI...';

    try {
        const saveFd = new FormData();
        saveFd.append('action', 'save_upi_id');
        saveFd.append('upi_id', inputVal);
        const saveRes = await fetch('ajax/wallet.php', { method: 'POST', body: saveFd });
        const saveData = await saveRes.json();

        if (!saveData.success) {
            showToast(saveData.message || 'Failed to save UPI ID', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-link" style="margin-right:8px;"></i> Save UPI & Withdraw';
            return;
        }

        // ✅ UPI saved successfully — update state and UI
        savedUpiId = inputVal;
        document.getElementById('savedUpiDisplay').textContent = inputVal;

        // Hide first time input, show connected box
        document.getElementById('upiFirstTimeBox').style.display = 'none';
        document.getElementById('upiConnectedBox').style.display = 'block';
        document.getElementById('upiConnectedId').textContent = inputVal;

        // Update UPI card with UPI ID and green badge
        document.getElementById('upiCardDesc').textContent = inputVal;
        document.getElementById('upiCardDesc').style.color = 'var(--purple)';
        document.getElementById('upiCardDesc').style.fontWeight = '800';
        document.getElementById('upiSavedBadge').style.display = 'block';

    } catch(e) {
        showToast('Network error while saving UPI. Try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-link" style="margin-right:8px;"></i> Save UPI & Withdraw';
        return;
    }

    // Step 2: Submit withdrawal request
    btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i> Processing...';

    const fd = new FormData();
    fd.append('action', 'request_withdraw');
    fd.append('type', 'upi');
    fd.append('amount', amount);
    fd.append('withdraw_mode', currentMode);
    fd.append('upi_id', savedUpiId);

    try {
        const res = await fetch('ajax/wallet.php', { method: 'POST', body: fd });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            loadBalance();
            loadHistory();
            document.getElementById('upiAmount').value = '';
            document.getElementById('chargeBox').style.display = 'none';
            document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        }
    } catch(e) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane" style="margin-right:8px;"></i> Submit Withdrawal';
    }
}

async function submitFFWithdrawal() {
    if (!selectedFFAmount) { showToast('Please select a diamond pack', 'error'); return; }
    const uid = document.getElementById('ffUID').value.trim();
    if (!uid || uid.length < 8) { showToast('Please enter a valid Free Fire UID', 'error'); return; }
    
    const fd = new FormData();
    fd.append('action', 'request_withdraw');
    fd.append('type', 'free_fire');
    fd.append('amount', selectedFFAmount);
    fd.append('withdraw_mode', 'instant');
    fd.append('free_fire_uid', uid);

    try {
        const res = await fetch('ajax/wallet.php', { method: 'POST', body: fd });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) { loadBalance(); loadHistory(); }
    } catch(e) { showToast('Network error. Please try again.', 'error'); }
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.4s';
        setTimeout(() => { if (container.contains(toast)) container.removeChild(toast); }, 400);
    }, 3000);
}
</script>
</body>
</html>