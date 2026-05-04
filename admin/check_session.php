<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Content-Type: application/json");

echo json_encode([
    'loggedIn' => isset($_SESSION['admin'])
]);
exit();