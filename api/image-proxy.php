<?php
// Simple image proxy to relay external images and avoid hotlinking/403
// GET parameter: url (raw URL)

header('Access-Control-Allow-Origin: *');

// Basic helper: send JSON error and exit
function send_error($code, $message) {
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if (!isset($_GET['url']) || !$_GET['url']) {
    send_error(400, 'Missing url parameter');
}

$url = $_GET['url'];
// Disallow data: or file: schemes
if (!preg_match('#^https?://#i', $url)) {
    send_error(400, 'Only HTTP/HTTPS URLs are allowed');
}

// Prevent SSRF: resolve host and check IP not private
$host = parse_url($url, PHP_URL_HOST);
if (!$host) send_error(400, 'Invalid URL');

$ips = [];
$records = dns_get_record($host, DNS_A + DNS_AAAA);
if ($records !== false) {
    foreach ($records as $r) {
        if (isset($r['ip'])) $ips[] = $r['ip'];
        if (isset($r['ipv6'])) $ips[] = $r['ipv6'];
    }
}
// Also try gethostbynamel
$g = gethostbynamel($host);
if ($g !== false) {
    foreach ($g as $ip) $ips[] = $ip;
}

function is_private_ip($ip) {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        // IPv4 ranges
        $private_patterns = [
            '/^10\./',
            '/^127\./',
            '/^169\.254\./',
            '/^172\.(1[6-9]|2[0-9]|3[0-1])\./',
            '/^192\.168\./'
        ];
        foreach ($private_patterns as $p) if (preg_match($p, $ip)) return true;
        return false;
    } else {
        // IPv6 checks (simple)
        if ($ip === '::1') return true;
        if (stripos($ip, 'fc') === 0 || stripos($ip, 'fd') === 0) return true;
        return false;
    }
}

foreach ($ips as $ip) {
    if (is_private_ip($ip)) {
        send_error(403, 'Forbidden host');
    }
}

// Caching: cache downloaded images into uploads/proxy/
$cacheDir = __DIR__ . '/../uploads/proxy/';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

$cacheTtl = 60 * 60 * 24; // 24 hours
$hash = sha1($url);
$ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
$ext = $ext ? preg_replace('/[^A-Za-z0-9]/', '', $ext) : '';
$cacheFile = $cacheDir . $hash . ($ext ? ('.' . $ext) : '');

// If cached and fresh, serve it
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
    $mime = mime_content_type($cacheFile) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=' . $cacheTtl);
    readfile($cacheFile);
    exit;
}

// Fetch remote image using cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
// Set a browser-like UA
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (ImageProxy)');
// Don't verify peer to avoid issues with local certs? Prefer enabling.
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

$data = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($data === false || $httpStatus >= 400) {
    send_error(502, 'Failed to fetch remote image' . ($curlErr ? ': ' . $curlErr : ''));
}

// Validate content-type
if (!$contentType || stripos($contentType, 'image/') !== 0) {
    send_error(400, 'Remote resource is not an image');
}

// Save to cache file (atomic)
$temp = $cacheFile . '.' . uniqid('tmp_', true);
if (@file_put_contents($temp, $data) === false) {
    // Can't cache, but still serve directly
    header('Content-Type: ' . $contentType);
    header('Cache-Control: no-cache, no-store, must-revalidate');
    echo $data;
    exit;
}

rename($temp, $cacheFile);

// Serve cached file
header('Content-Type: ' . $contentType);
header('Cache-Control: public, max-age=' . $cacheTtl);
readfile($cacheFile);
exit;
