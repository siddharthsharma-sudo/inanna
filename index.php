<?php
// inanna/index.php — redirect all requests to public/
// Correctly strips the project prefix from the request path to avoid duplicates.
// Put this file at the project root (parent of public/).

// Prevent accidental output before header()
if (headers_sent()) {
    // We'll still compute a safe target for the meta-refresh fallback below.
}

// Request path + query
$reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$reqQuery = $_SERVER['QUERY_STRING'] ?? '';

// Project URI (based on folder name). Example: '/inanna'
$projectUri = '/' . trim(basename(__DIR__));

// Public segment to detect loops: '/inanna/public'
$publicSegment = $projectUri . '/public';

// If request already targets /<project>/public, do nothing (avoid redirect loop)
// Normalize comparison to ensure trailing slashes don't confuse it
$normalizedReq = rtrim($reqPath, '/');
$normalizedPublic = rtrim($publicSegment, '/');
 
if (strpos($normalizedReq, $normalizedPublic) === 0) {
    // Already inside public; no redirect needed — but normalize to include query if you want:
    $target = $reqPath . ($reqQuery !== '' ? '?' . $reqQuery : '');
} else {
    // Strip leading projectUri from reqPath if present to compute relative path
    $relative = $reqPath;
    if ($projectUri !== '' && strpos($reqPath, $projectUri) === 0) {
        $relative = substr($reqPath, strlen($projectUri));
        if ($relative === '') $relative = '/';
    }
    // Ensure relative path starts with '/'
    if ($relative === '' || $relative[0] !== '/') $relative = '/' . ltrim($relative, '/');

    // Build final target: /<project>/public + relative + optional query
    $target = $publicSegment . $relative . ($reqQuery !== '' ? '?' . $reqQuery : '');
}

// If headers already sent, fallback to meta-refresh (rare)
if (headers_sent()) {
    echo '<!doctype html><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($target, ENT_QUOTES) . '">';
    exit;
}

// Send redirect (302 for testing; change to 301 when ready)
header('Location: ' . $target, true, 302);
exit;
