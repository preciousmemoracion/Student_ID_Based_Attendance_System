<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// ── 1. Not logged in → back to login ──────────────────
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit();
}

// ── 2. Session timeout — 30 minutes of inactivity ─────
$timeout = 1800;
if (isset($_SESSION['last_activity'])) {
    if ((time() - $_SESSION['last_activity']) > $timeout) {
        $_SESSION = [];
        session_destroy();
        header("Location: ../index.php?reason=timeout");
        exit();
    }
}
$_SESSION['last_activity'] = time();

// ── 3. Session fixation guard ─────────────────────────
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if ((time() - $_SESSION['created']) > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// ── 4. Browser fingerprint check ─────────────────────
$fingerprint = md5(
    $_SERVER['HTTP_USER_AGENT'] .
    substr($_SERVER['REMOTE_ADDR'], 0, strrpos($_SERVER['REMOTE_ADDR'], '.'))
);
if (!isset($_SESSION['fingerprint'])) {
    $_SESSION['fingerprint'] = $fingerprint;
} else if ($_SESSION['fingerprint'] !== $fingerprint) {
    // Fingerprint mismatch — possible session hijack
    $_SESSION = [];
    session_destroy();
    header("Location: ../index.php?reason=security");
    exit();
}

// ── 5. No-cache headers on every admin page ───────────
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
?>