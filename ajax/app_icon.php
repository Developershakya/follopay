<?php
// =============================================================
//  ajax/app_icon.php
//  Reusable App Icon Fetcher — Play Store + Any URL
//
//  Usage (single):
//    ajax/app_icon.php?pkg=com.whatsapp
//
//  Usage (batch — multiple at once):
//    ajax/app_icon.php?batch=com.whatsapp,com.instagram.android,com.example
//
//  Response (single):
//    { "success": true, "icon": "https://...", "pkg": "com.whatsapp" }
//
//  Response (batch):
//    { "com.whatsapp": "https://...", "com.instagram": "https://..." }
// =============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// ── Cache folder (auto-create) ──
$cacheDir = sys_get_temp_dir() . '/app_icons/';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

// ── Route: batch or single ──
if (!empty($_GET['batch'])) {
    handleBatch($_GET['batch'], $cacheDir);
} else {
    handleSingle($_GET['pkg'] ?? '', $cacheDir);
}

// =============================================================
//  SINGLE
// =============================================================
function handleSingle($pkg, $cacheDir) {
    $pkg = sanitizePkg($pkg);
    if (!$pkg) {
        echo json_encode(['success' => false, 'icon' => '', 'error' => 'Invalid package name']);
        return;
    }
    $icon = getIcon($pkg, $cacheDir);
    echo json_encode([
        'success' => !empty($icon),
        'icon'    => $icon,
        'pkg'     => $pkg,
    ]);
}

// =============================================================
//  BATCH — multiple packages at once (max 20)
// =============================================================
function handleBatch($batchStr, $cacheDir) {
    $pkgs   = array_slice(array_filter(array_map('sanitizePkg', explode(',', $batchStr))), 0, 20);
    $result = [];
    foreach ($pkgs as $pkg) {
        $result[$pkg] = getIcon($pkg, $cacheDir);
    }
    echo json_encode($result);
}

// =============================================================
//  CORE: Get icon for one package
//  Returns icon URL string, or '' if not found
// =============================================================
function getIcon($pkg, $cacheDir) {
    // ── 1. Check file cache (24 hours) ──
    $cacheFile = $cacheDir . md5($pkg) . '.txt';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
        return trim(file_get_contents($cacheFile));
    }

    // ── 2. Known packages — hardcoded icons (instant, no fetch needed) ──
    $known = knownIcons($pkg);
    if ($known) {
        file_put_contents($cacheFile, $known);
        return $known;
    }

    // ── 3. Fetch from Play Store ──
    $icon = fetchFromPlayStore($pkg);

    // ── 4. Cache result (even empty — prevents hammering) ──
    file_put_contents($cacheFile, $icon);

    return $icon;
}

// =============================================================
//  FETCH from Play Store page
// =============================================================
function fetchFromPlayStore($pkg) {
    $url = 'https://play.google.com/store/apps/details?id=' . urlencode($pkg) . '&hl=en';

    $context = stream_context_create([
        'http' => [
            'method'          => 'GET',
            'timeout'         => 8,
            'follow_location' => 1,
            'max_redirects'   => 3,
            'header'          => implode("\r\n", [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept-Language: en-US,en;q=0.9',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Referer: https://play.google.com/',
                'Cache-Control: no-cache',
            ]),
        ],
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ],
    ]);

    $html = @file_get_contents($url, false, $context);
    if (!$html) return '';

    $icon = '';

    // Method A: og:image (standard meta tag)
    if (!$icon && preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/', $html, $m))
        $icon = $m[1];

    // Method B: og:image (content before property)
    if (!$icon && preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/', $html, $m))
        $icon = $m[1];

    // Method C: play-lh.googleusercontent.com direct URL in JSON data
    if (!$icon && preg_match('/"(https:\/\/play-lh\.googleusercontent\.com\/[A-Za-z0-9_\-]{20,})"/', $html, $m))
        $icon = $m[1];

    // Method D: itemprop image
    if (!$icon && preg_match('/itemprop=["\']image["\'][^>]+content=["\']([^"\']+)["\']/', $html, $m))
        $icon = $m[1];

    // ── Upgrade to high resolution ──
    if ($icon) {
        $icon = preg_replace('/=w\d+-h\d+(-rw)?/', '=w512-h512-rw', $icon);
        $icon = preg_replace('/=s\d+(-rw)?/',       '=s512-rw',      $icon);
    }

    return $icon;
}

// =============================================================
//  KNOWN ICONS — Popular apps ke liye hardcoded
//  (No fetch needed — instant response)
// =============================================================
function knownIcons($pkg) {
    $map = [
        'com.whatsapp'                           => 'https://www.google.com/s2/favicons?sz=128&domain=whatsapp.com',
        'com.instagram.android'                  => 'https://www.google.com/s2/favicons?sz=128&domain=instagram.com',
        'com.facebook.katana'                    => 'https://www.google.com/s2/favicons?sz=128&domain=facebook.com',
        'com.facebook.lite'                      => 'https://www.google.com/s2/favicons?sz=128&domain=facebook.com',
        'com.google.android.youtube'             => 'https://www.google.com/s2/favicons?sz=128&domain=youtube.com',
        'com.google.android.apps.maps'           => 'https://www.google.com/s2/favicons?sz=128&domain=maps.google.com',
        'com.twitter.android'                    => 'https://www.google.com/s2/favicons?sz=128&domain=x.com',
        'com.snapchat.android'                   => 'https://www.google.com/s2/favicons?sz=128&domain=snapchat.com',
        'com.linkedin.android'                   => 'https://www.google.com/s2/favicons?sz=128&domain=linkedin.com',
        'com.spotify.music'                      => 'https://www.google.com/s2/favicons?sz=128&domain=spotify.com',
        'com.amazon.mShop.android.shopping'      => 'https://www.google.com/s2/favicons?sz=128&domain=amazon.in',
        'com.flipkart.android'                   => 'https://www.google.com/s2/favicons?sz=128&domain=flipkart.com',
        'com.phonepe.app'                        => 'https://www.google.com/s2/favicons?sz=128&domain=phonepe.com',
        'com.google.android.apps.paymentsearch'  => 'https://www.google.com/s2/favicons?sz=128&domain=pay.google.com',
        'net.one97.paytm'                        => 'https://www.google.com/s2/favicons?sz=128&domain=paytm.com',
        'in.amazon.mShop.android.shopping'       => 'https://www.google.com/s2/favicons?sz=128&domain=amazon.in',
        'com.swiggy.android'                     => 'https://www.google.com/s2/favicons?sz=128&domain=swiggy.com',
        'com.application.zomato'                 => 'https://www.google.com/s2/favicons?sz=128&domain=zomato.com',
        'com.myntra.android'                     => 'https://www.google.com/s2/favicons?sz=128&domain=myntra.com',
        'com.meesho.supply'                      => 'https://www.google.com/s2/favicons?sz=128&domain=meesho.com',
        'com.imo.android.imoim'                  => 'https://www.google.com/s2/favicons?sz=128&domain=imo.im',
        'com.truecaller'                         => 'https://www.google.com/s2/favicons?sz=128&domain=truecaller.com',
        'com.shareit.lite'                       => 'https://www.google.com/s2/favicons?sz=128&domain=ushareit.com',
        'com.google.android.gm'                  => 'https://www.google.com/s2/favicons?sz=128&domain=gmail.com',
        'com.microsoft.teams'                    => 'https://www.google.com/s2/favicons?sz=128&domain=teams.microsoft.com',
    ];
    return $map[$pkg] ?? '';
}

// =============================================================
//  HELPERS
// =============================================================
function sanitizePkg($pkg) {
    $pkg = trim($pkg);
    // Valid Android package name pattern
    return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*(\.[a-zA-Z][a-zA-Z0-9_]*)+$/', $pkg) ? $pkg : '';
}