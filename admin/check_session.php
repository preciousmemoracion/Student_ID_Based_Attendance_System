<?php
session_start();

// Only allow XHR requests — block direct browser access
if (
    !isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(403);
    exit('Forbidden');
}

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$timeout = 1800; // 30 minutes

// Check admin session AND inactivity timeout
if (
    isset($_SESSION['admin']) &&
    $_SESSION['admin'] === true &&
    isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) < $timeout
) {
    $_SESSION['last_activity'] = time(); // Renew on activity
    echo json_encode(['loggedIn' => true]);
} else {
    // Destroy stale/invalid session cleanly
    $_SESSION = [];
    session_destroy();
    echo json_encode(['loggedIn' => false]);
}