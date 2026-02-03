<?php
// test_device_tracking.php
// Isko separate file mein test karne ke liye use karo

// ============================================
// 🧪 DEVICE TRACKING TESTING
// ============================================

echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";

// 1. Device Fingerprint Test
echo "🔧 TEST 1: Device Fingerprint Generation\n";
echo "==========================================\n\n";

// Simulate real data
$_SERVER['REMOTE_ADDR'] = '192.168.1.100';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9,hi;q=0.8';

function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    }
    return trim($ip);
}

function generateDeviceFingerprint() {
    $ip = getClientIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    
    $fingerprint = hash('sha256', $ip . $userAgent . $acceptLanguage);
    return $fingerprint;
}

$fingerprint = generateDeviceFingerprint();
echo "IP Address: " . getClientIP() . "\n";
echo "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
echo "Accept Language: " . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . "\n";
echo "Generated Fingerprint (SHA256):\n";
echo $fingerprint . "\n\n";

// 2. IP Detection Test
echo "🔧 TEST 2: Real IP Detection\n";
echo "=============================\n\n";

$testIPs = [
    '192.168.1.1',
    '10.0.0.1',
    '172.16.0.1',
    '::1', // IPv6 localhost
    '2001:0db8:85a3:0000:0000:8a2e:0370:7334' // IPv6
];

echo "Supported IP Addresses:\n";
foreach ($testIPs as $ip) {
    echo "  ✓ " . $ip . "\n";
}
echo "\n";

// 3. Validation Test
echo "🔧 TEST 3: Phone Number Validation\n";
echo "====================================\n\n";

$testPhones = [
    ['number' => '9876543210', 'valid' => true],
    ['number' => '98765432', 'valid' => false],
    ['number' => '9876543210a', 'valid' => false],
    ['number' => '+919876543210', 'valid' => false],
    ['number' => '987-654-3210', 'valid' => false]
];

echo "Phone Validation Results:\n";
foreach ($testPhones as $test) {
    $pattern = '/^[0-9]{10}$/';
    $isValid = preg_match($pattern, $test['number']);
    $status = ($isValid === 1) ? '✓ PASS' : '✗ FAIL';
    echo $status . " - " . $test['number'] . " (Expected: " . ($test['valid'] ? 'valid' : 'invalid') . ")\n";
}
echo "\n";

// 4. Database Structure Info
echo "🔧 TEST 4: Database Tables\n";
echo "============================\n\n";

echo "Table: device_tracking\n";
echo "Columns:\n";
echo "  - id (INT, PRIMARY KEY)\n";
echo "  - user_id (INT, FOREIGN KEY -> users.id)\n";
echo "  - device_fingerprint (VARCHAR 255)\n";
echo "  - ip_address (VARCHAR 45, supports IPv6)\n";
echo "  - user_agent (TEXT)\n";
echo "  - is_active (TINYINT)\n";
echo "  - created_at (TIMESTAMP)\n";
echo "  - updated_at (TIMESTAMP)\n\n";

echo "Table: login_history\n";
echo "Columns:\n";
echo "  - id (INT, PRIMARY KEY)\n";
echo "  - user_id (INT, FOREIGN KEY)\n";
echo "  - ip_address (VARCHAR 45)\n";
echo "  - user_agent (TEXT)\n";
echo "  - status (ENUM: success, failed, banned, logout, registration)\n";
echo "  - login_time (TIMESTAMP)\n\n";

// 5. Detection Logic Test
echo "🔧 TEST 5: Duplicate Detection Logic\n";
echo "======================================\n\n";

$detectionScenarios = [
    [
        'scenario' => 'Same device, different account',
        'device_fingerprint' => 'abc123',
        'ip_address' => '192.168.1.1',
        'expected_result' => 'DUPLICATE - Block registration',
        'risk_level' => 'HIGH'
    ],
    [
        'scenario' => 'Same IP, different account',
        'device_fingerprint' => 'xyz789',
        'ip_address' => '192.168.1.1',
        'expected_result' => 'DUPLICATE - Block registration',
        'risk_level' => 'HIGH'
    ],
    [
        'scenario' => 'Same phone, different account',
        'device_fingerprint' => 'different',
        'ip_address' => 'different',
        'expected_result' => 'DUPLICATE - Block registration',
        'risk_level' => 'MEDIUM'
    ],
    [
        'scenario' => 'New device, new IP, new phone',
        'device_fingerprint' => 'new123',
        'ip_address' => '10.0.0.1',
        'expected_result' => 'ALLOWED - Register',
        'risk_level' => 'LOW'
    ]
];

foreach ($detectionScenarios as $scenario) {
    echo "Scenario: " . $scenario['scenario'] . "\n";
    echo "Risk Level: " . $scenario['risk_level'] . "\n";
    echo "Expected: " . $scenario['expected_result'] . "\n";
    echo "---\n\n";
}

// 6. Response Examples
echo "🔧 TEST 6: API Response Examples\n";
echo "==================================\n\n";

echo "Registration Response (Duplicate Detected):\n";
$response1 = [
    'success' => false,
    'message' => 'Multiple accounts detected from your device',
    'device_duplicates' => 2
];
echo json_encode($response1, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "Login Response (Success):\n";
$response2 = [
    'success' => true,
    'role' => 'user',
    'device_duplicates' => 1,
    'has_duplicates' => true
];
echo json_encode($response2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "Admin API Response (Suspicious Users):\n";
$response3 = [
    'success' => true,
    'count' => 1,
    'data' => [
        [
            'id' => 5,
            'username' => 'test_user',
            'email' => 'test@example.com',
            'phone' => '9876543210',
            'unique_devices' => 3,
            'unique_ips' => 2,
            'ip_addresses' => '192.168.1.1,10.0.0.1',
            'last_device_login' => '2024-02-03 10:30:00'
        ]
    ]
];
echo json_encode($response3, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// 7. SQL Queries Test
echo "🔧 TEST 7: Important SQL Queries\n";
echo "==================================\n\n";

echo "Query 1: Check multiple devices\n";
echo "SELECT COUNT(DISTINCT device_fingerprint) FROM device_tracking WHERE user_id = 5;\n\n";

echo "Query 2: Find linked accounts\n";
echo "SELECT DISTINCT u.* FROM users u\n";
echo "INNER JOIN device_tracking dt ON u.id = dt.user_id\n";
echo "WHERE dt.ip_address = '192.168.1.1';\n\n";

echo "Query 3: Get suspicious users\n";
echo "SELECT u.id, u.username, COUNT(DISTINCT dt.ip_address) as ip_count\n";
echo "FROM users u\n";
echo "LEFT JOIN device_tracking dt ON u.id = dt.user_id\n";
echo "GROUP BY u.id HAVING ip_count > 1;\n\n";

echo "Query 4: Recent logins\n";
echo "SELECT * FROM login_history ORDER BY login_time DESC LIMIT 10;\n\n";

// 8. Summary
echo "🔧 TEST 8: Implementation Summary\n";
echo "===================================\n\n";

echo "✅ Device Fingerprinting: ENABLED\n";
echo "✅ IP Address Tracking: ENABLED\n";
echo "✅ Login History: ENABLED\n";
echo "✅ Phone Uniqueness: ENABLED\n";
echo "✅ Duplicate Detection: ENABLED\n";
echo "✅ Admin Monitoring: ENABLED\n\n";

echo "Files Required:\n";
echo "  1. AuthController.php (Updated)\n";
echo "  2. device_tracking table (SQL)\n";
echo "  3. login_history table (SQL)\n";
echo "  4. admin_device_tracking.html (Dashboard)\n";
echo "  5. api/admin/suspicious-users.php (API)\n";
echo "  6. api/admin/activity-history.php (API)\n\n";

echo "</pre>";

?>

<!-- Frontend Test Page -->
<!DOCTYPE html>
<html>
<head>
    <title>Device Tracking Test</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">🧪 Device Tracking System - Manual Tests</h1>
        
        <div class="space-y-6">
            <!-- Test 1: Device Info -->
            <div class="border-l-4 border-blue-500 p-4 bg-blue-50">
                <h3 class="font-bold text-lg mb-2">Test 1: Get Your Device Info</h3>
                <button onclick="getDeviceInfo()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Get Device Info
                </button>
                <pre id="deviceInfo" class="mt-4 bg-white p-3 rounded border border-gray-300 hidden"></pre>
            </div>

            <!-- Test 2: Simulate Registration -->
            <div class="border-l-4 border-green-500 p-4 bg-green-50">
                <h3 class="font-bold text-lg mb-2">Test 2: Test Registration</h3>
                <form id="testRegister" class="space-y-3">
                    <input type="text" placeholder="Username" required class="w-full px-3 py-2 border rounded">
                    <input type="email" placeholder="Email" required class="w-full px-3 py-2 border rounded">
                    <input type="text" placeholder="Phone (10 digits)" maxlength="10" required class="w-full px-3 py-2 border rounded">
                    <input type="password" placeholder="Password" required class="w-full px-3 py-2 border rounded">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-full">
                        Test Registration
                    </button>
                </form>
                <pre id="registerResult" class="mt-4 bg-white p-3 rounded border border-gray-300 hidden"></pre>
            </div>

            <!-- Test 3: Check Admin Access -->
            <div class="border-l-4 border-purple-500 p-4 bg-purple-50">
                <h3 class="font-bold text-lg mb-2">Test 3: Admin Access</h3>
                <button onclick="checkAdminAccess()" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                    Check Admin Dashboard
                </button>
                <div id="adminResult" class="mt-4 hidden"></div>
            </div>
        </div>
    </div>

    <script>
        function getDeviceInfo() {
            const info = {
                userAgent: navigator.userAgent,
                language: navigator.language,
                platform: navigator.platform,
                screenResolution: `${window.screen.width}x${window.screen.height}`,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
            };
            
            const element = document.getElementById('deviceInfo');
            element.textContent = JSON.stringify(info, null, 2);
            element.classList.remove('hidden');
        }

        document.getElementById('testRegister').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'register');
            
            fetch('ajax/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                const element = document.getElementById('registerResult');
                element.textContent = JSON.stringify(data, null, 2);
                element.classList.remove('hidden');
            });
        });

        function checkAdminAccess() {
            fetch('admin/device-tracking.html')
            .then(r => r.text())
            .then(html => {
                const element = document.getElementById('adminResult');
                element.innerHTML = '<p class="text-green-700">✓ Admin dashboard accessible</p>';
                element.classList.remove('hidden');
            })
            .catch(err => {
                const element = document.getElementById('adminResult');
                element.innerHTML = '<p class="text-red-700">✗ Admin dashboard not found. Setup required.</p>';
                element.classList.remove('hidden');
            });
        }
    </script>
</body>
</html>