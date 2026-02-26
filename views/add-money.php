<!-- simple-add-money-form.html -->
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>पैसे जोड़ें - Add Money</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
            padding: 30px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .required {
            color: #e74c3c;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group small {
            display: block;
            color: #999;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .amount-input-group {
            display: flex;
            gap: 10px;
        }
        
        .amount-symbol {
            display: flex;
            align-items: center;
            padding: 12px;
            background: #f5f5f5;
            border-radius: 8px;
            font-weight: bold;
            color: #333;
            font-size: 16px;
            min-width: 50px;
            justify-content: center;
        }
        
        .amount-input-group input {
            flex: 1;
        }
        
        .info-box {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #555;
            line-height: 1.6;
        }
        
        .info-box strong {
            color: #667eea;
            display: block;
            margin-bottom: 8px;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            font-size: 14px;
            border-left: 4px solid;
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border-color: #28a745;
            display: block;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
            display: block;
        }
        
        .alert.warning {
            background: #fff3cd;
            color: #856404;
            border-color: #ffeaa7;
            display: block;
        }
        
        .spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .balance-info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .balance-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .balance-amount {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>₹ पैसे जोड़ें</h1>
            <p>अपने wallet में पैसे जोड़ें</p>
        </div>
        
        <!-- Alert -->
        <div id="alert" class="alert"></div>
        
        <!-- Current Balance -->
        <div class="balance-info">
            <div class="balance-label">Current Wallet Balance</div>
            <div class="balance-amount" id="walletBalance">₹0.00</div>
        </div>
        
        <!-- Info -->
        <div class="info-box">
            <strong>📝 कैसे काम करता है:</strong>
            पहले आप अपने बैंक/UPI से payment करें, फिर payment के बाद मिलने वाला <strong>UTR नंबर</strong> और <strong>Amount</strong> यहाँ भरें। हम आपकी payment verify करके wallet में पैसे जोड़ देंगे।
        </div>
        
        <!-- Form -->
        <form id="addMoneyForm">
            <!-- UTR Input -->
            <div class="form-group">
                <label for="utr">
                    UTR नंबर
                    <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    id="utr" 
                    name="utr" 
                    placeholder="12345678901"
                    required
                >
                <small>✓ Payment के बाद आपको मिलने वाला UTR नंबर</small>
            </div>
            
            <!-- Amount Input -->
            <div class="form-group">
                <label for="amount">
                    रकम (Amount)
                    <span class="required">*</span>
                </label>
                <div class="amount-input-group">
                    <div class="amount-symbol">₹</div>
                    <input 
                        type="number" 
                        id="amount" 
                        name="amount" 
                        placeholder="100"
                        min="100"
                        step="1"
                        required
                    >
                </div>
                <small>✓ न्यूनतम ₹100 जोड़ सकते हैं</small>
            </div>
            
            <!-- Buttons -->
            <div class="button-group">
                <button type="reset" class="btn btn-secondary">रीसेट</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span id="btnText">पैसे जोड़ें</span>
                </button>
            </div>
        </form>
        
        <!-- Links -->
        <div style="text-align: center; margin-top: 20px;">
            <a href="transaction-history.html" style="color: #667eea; text-decoration: none; font-size: 14px;">
                📊 Transaction History देखें →
            </a>
        </div>
    </div>
    
    <script>
        const form = document.getElementById('addMoneyForm');
        const submitBtn = document.getElementById('submitBtn');
        const alertDiv = document.getElementById('alert');
        
        // Load wallet balance on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadWalletBalance();
        });
        
        // Load wallet balance
        async function loadWalletBalance() {
            try {
                const response = await fetch('api/simple-add-money-api.php?action=get_wallet');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('walletBalance').textContent = 
                        '₹' + parseFloat(data.wallet_balance).toFixed(2);
                }
            } catch (error) {
                console.error('Error loading balance:', error);
            }
        }
        
        // Form submission
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const utr = document.getElementById('utr').value.trim();
            const amount = parseFloat(document.getElementById('amount').value);
            
            // Validation
            if (!utr) {
                showAlert('कृपया UTR नंबर भरें', 'error');
                document.getElementById('utr').focus();
                return;
            }
            
            if (utr.length < 6) {
                showAlert('UTR नंबर कम से कम 6 अंक का होना चाहिए', 'error');
                return;
            }
            
            if (!amount || amount <= 0) {
                showAlert('कृपया Amount भरें', 'error');
                document.getElementById('amount').focus();
                return;
            }
            
            if (amount < 100) {
                showAlert('न्यूनतम ₹100 जोड़ सकते हैं', 'error');
                return;
            }
            
            // Disable button and show loading
            submitBtn.disabled = true;
            document.getElementById('btnText').innerHTML = 
                '<span class="spinner"></span>प्रोसेस हो रहा है...';
            
            try {
                // Send to API
                const formData = new FormData();
                formData.append('action', 'verify_payment');
                formData.append('utr', utr);
                formData.append('amount', amount);
                
                const response = await fetch('api/simple-add-money-api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    form.reset();
                    
                    // Refresh balance
                    setTimeout(() => {
                        loadWalletBalance();
                    }, 500);
                } else {
                    showAlert(data.message, data.type || 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('कुछ गलती हुई। कृपया दोबारा कोशिश करें।', 'error');
            } finally {
                submitBtn.disabled = false;
                document.getElementById('btnText').textContent = 'पैसे जोड़ें';
            }
        });
        
        // Show alert
        function showAlert(message, type) {
            alertDiv.textContent = message;
            alertDiv.className = 'alert ' + type;
            
            // Auto hide
            setTimeout(() => {
                alertDiv.className = 'alert';
            }, 5000);
        }
    </script>
</body>
</html>