<?php
session_start();

// ✅ Step 1: Clear all session variables
$_SESSION = [];
session_unset();

// ✅ Step 2: Destroy the session cookie from the browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '',                   // empty value
        time() - 42000,       // expire in the past = delete it
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// ✅ Step 3: Destroy the session on the server
session_destroy();

// ✅ Step 4: Prevent browser from caching — this stops the back button from restoring protected pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// ✅ Step 5: Redirect to login
header("Location: ../index.php");
exit();
?>