<?php
// ajax/app_icon.php
// Real Play Store app icon extractor
// Usage: ajax/app_icon.php?pkg=com.sumit.s.s

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=86400'); // 24 hour cache

$pkg = trim($_GET['pkg'] ?? 'com.sumit.s.s');

// Validate package name (only allow valid Android package names)
if (!$pkg || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]*(\.[a-zA-Z][a-zA-Z0-9_]*)+$/', $pkg)) {
    echo json_encode(['success' => false, 'icon' => '', 'error' => 'Invalid package name']);
    exit;
}

// ── Simple memory cache per request ──
// For production, use file cache or Redis
$cacheFile = sys_get_temp_dir() . '/playicon_' . md5($pkg) . '.json';
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
    echo file_get_contents($cacheFile);
    exit;
}

// ── Fetch Play Store page ──
$url = "https://play.google.com/store/apps/details?id=" . urlencode($pkg) . "&hl=en";

$context = stream_context_create([
    'http' => [
        'method'          => 'GET',
        'timeout'         => 8,
        'follow_location' => 1,
        'header'          => implode("\r\n", [
            // Real Chrome browser headers — Google blocks bots without these
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept-Language: en-US,en;q=0.9',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Referer: https://play.google.com/',
        ])
    ],
    'ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
    ]
]);

$html = @file_get_contents($url, false, $context);
$icon = '';

if ($html) {
    // ── Method 1: og:image meta tag (most reliable) ──
    // <meta property="og:image" content="https://play-lh...">
    if (preg_match('/<meta\s+property="og:image"\s+content="([^"]+)"/i', $html, $m)) {
        $icon = $m[1];
    }

    // ── Method 2: og:image alternate attribute order ──
    if (!$icon && preg_match('/<meta\s+content="([^"]+)"\s+property="og:image"/i', $html, $m)) {
        $icon = $m[1];
    }

    // ── Method 3: JSON-LD or inline data (Google Play embeds icon in JS data) ──
    // Matches: ["https://play-lh.googleusercontent.com/XXXX","image/png",...]
    if (!$icon && preg_match('/"(https:\/\/play-lh\.googleusercontent\.com\/[A-Za-z0-9_\-]{30,})"/', $html, $m)) {
        $icon = $m[1];
    }

    // ── Method 4: itemprop="image" ──
    if (!$icon && preg_match('/itemprop="image"\s+content="([^"]+)"/i', $html, $m)) {
        $icon = $m[1];
    }
}

// ── Upgrade icon to high resolution if found ──
// Google Play icons often come as small size, we can request bigger
if ($icon) {
    // Remove size params and request 512px
    $icon = preg_replace('/=w\d+-h\d+(-rw)?/', '=w512-h512-rw', $icon);
    $icon = preg_replace('/=s\d+/', '=s512', $icon);
}

$result = json_encode([
    'success' => !empty($icon),
    'icon'    => $icon,
    'pkg'     => $pkg,
    'source'  => $icon ? 'playstore' : 'none',
]);

// Save to file cache
if ($icon) {
    @file_put_contents($cacheFile, $result);
}

echo $result;