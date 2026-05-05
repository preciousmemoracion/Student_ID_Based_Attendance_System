<?php
session_start();
include "db_connect.php";

// ── If already logged in, go to dashboard ──
if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Location: admin/dashboard.php");
    exit();
}

// ── Nuclear no-cache headers ──
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Surrogate-Control: no-store");
header("Vary: *");

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    // ── Sanitize inputs ──
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please fill in all fields.";
    } else {
        // ── Prepared statement — prevents SQL injection ──
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            // ── Verify password ──
            // Supports both password_hash() (bcrypt) AND legacy MD5
            // Once you rehash passwords with password_hash(), remove the MD5 branch
            $validPassword = false;

            if (password_verify($password, $admin['password'])) {
                // Modern bcrypt hash — correct
                $validPassword = true;
            } elseif ($admin['password'] === md5($password)) {
                // Legacy MD5 — still works, but auto-upgrades hash on login
                $validPassword = true;

                // ── AUTO-UPGRADE: rehash with bcrypt immediately ──
                $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $upd = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $upd->bind_param("si", $newHash, $admin['id']);
                $upd->execute();
                $upd->close();
            }

            if ($validPassword) {
                // ── Regenerate session ID before setting session ──
                session_regenerate_id(true);
                $_SESSION['admin']      = true;
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_user'] = $admin['username'];
                $_SESSION['login_time'] = time();
                // Tie session to this browser fingerprint
                $_SESSION['ip']         = $_SERVER['REMOTE_ADDR'];
                $_SESSION['ua']         = $_SERVER['HTTP_USER_AGENT'];

                header("Location: admin/dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            // Intentional: same error message whether user not found or wrong password
            $error = "Invalid username or password.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<title>Instructor Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<script>
(function () {
    'use strict';

    var LOGIN_URL     = window.location.href;
    var DASHBOARD_URL = 'admin/dashboard.php';
    var FLOOD_COUNT   = 80;

    // ── 1. Replace current history entry ──
    // Overwrites whatever was before login in the stack
    try { history.replaceState({ page: 'login', i: 0 }, '', LOGIN_URL); } catch(e) {}

    // ── 2. Flood history FORWARD so the forward button is also dead ──
    function floodHistory() {
        try {
            for (var i = 1; i <= FLOOD_COUNT; i++) {
                history.pushState({ page: 'login', i: i }, '', LOGIN_URL);
            }
        } catch(e) {}
    }
    floodHistory();

    // ── 3. Intercept every back/forward press ──
    window.addEventListener('popstate', function () {
        // Push immediately so URL never changes
        try { history.pushState({ page: 'login' }, '', LOGIN_URL); } catch(e) {}
        // Refill buffer
        floodHistory();
        // Check if somehow still logged in
        bounceIfLoggedIn();
    });

    // ── 4. On every load: if still logged in, bounce to dashboard ──
    window.addEventListener('load', function () {
        try {
            var nav = performance.getEntriesByType('navigation');
            if (nav.length > 0 && nav[0].type === 'back_forward') {
                bounceIfLoggedIn();
            }
        } catch(e) {}
        bounceIfLoggedIn();
    });

    // ── 5. bfcache restore (Safari / Firefox) ──
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            floodHistory();
            bounceIfLoggedIn();
        }
    });

    // ── 6. Tab focus / visibility restore ──
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            bounceIfLoggedIn();
        }
    });

    // ── Check session and redirect to dashboard if still logged in ──
    function bounceIfLoggedIn() {
        fetch('admin/check_session.php?_=' + Date.now(), {
            method: 'GET',
            cache: 'no-store',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            if (data && data.loggedIn) {
                window.location.replace(DASHBOARD_URL);
            }
        })
        .catch(function () {});
    }

})();
</script>

<style>
:root {
    --blue:    #2563EB;
    --blue-lt: #3B82F6;
    --border:  rgba(255,255,255,0.18);
    --radius:  20px;
}

* { box-sizing: border-box; }

body {
    min-height: 100vh;
    margin: 0;
    background: url('img/icas.jpeg') no-repeat center center / cover;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'DM Sans', sans-serif;
}

body::before {
    content: '';
    position: fixed;
    inset: 0;
    background: linear-gradient(135deg,
        rgba(3,10,35,0.55) 0%,
        rgba(10,30,80,0.48) 60%,
        rgba(3,10,35,0.58) 100%);
    z-index: 0;
}

body > * { position: relative; z-index: 1; }

.login-card {
    width: 100%;
    max-width: 420px;
    background: rgba(8, 20, 60, 0.55);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    box-shadow: 0 20px 60px rgba(0,0,0,0.45);
    padding: 2.5rem 2rem;
    color: #fff;
    animation: fadeInUp 0.7s ease both;
}

.login-logo {
    width: 82px;
    height: 82px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.25);
    box-shadow: 0 0 0 5px rgba(37,99,235,0.25);
    display: block;
    margin: 0 auto 1rem;
}

.login-title {
    font-family: 'Syne', sans-serif;
    font-size: 1.55rem;
    font-weight: 800;
    text-align: center;
    margin-bottom: 0.25rem;
    color: #fff;
    text-shadow: 0 2px 12px rgba(0,0,0,0.5);
}

.login-subtitle {
    text-align: center;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.55);
    margin-bottom: 1.8rem;
    font-weight: 500;
}

.form-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: rgba(255,255,255,0.75);
    margin-bottom: 5px;
    letter-spacing: 0.4px;
    text-transform: uppercase;
}

.input-group-text {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    border-right: none;
    color: rgba(255,255,255,0.5);
    border-radius: 10px 0 0 10px;
}

.form-control {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    border-left: none;
    color: #fff;
    border-radius: 0 10px 10px 0;
    padding: 0.6rem 0.9rem;
    font-family: 'DM Sans', sans-serif;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control::placeholder { color: rgba(255,255,255,0.3); }

.form-control:focus {
    background: rgba(255,255,255,0.12);
    border-color: var(--blue-lt);
    box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
    color: #fff;
    outline: none;
}

/* ── RATE LIMIT WARNING ── */
.alert-warning-login {
    background: rgba(180,83,9,0.2);
    border: 1px solid rgba(251,191,36,0.35);
    border-radius: 10px;
    color: #fcd34d;
    font-size: 0.875rem;
    font-weight: 500;
    padding: 0.65rem 1rem;
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-login {
    width: 100%;
    padding: 0.65rem;
    border: none;
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #1d4ed8, #3b82f6);
    box-shadow: 0 6px 20px rgba(29,78,216,0.4);
    cursor: pointer;
    transition: transform 0.18s ease, filter 0.18s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 0.5rem;
}

.btn-login:hover  { transform: translateY(-2px); filter: brightness(1.1); }
.btn-login:active { transform: translateY(0); }
.btn-login:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    filter: none;
}

.alert-login {
    background: rgba(185,28,28,0.2);
    border: 1px solid rgba(248,113,113,0.35);
    border-radius: 10px;
    color: #fca5a5;
    font-size: 0.875rem;
    font-weight: 500;
    padding: 0.65rem 1rem;
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
    animation: shake 0.4s ease;
}

.login-divider {
    border: none;
    height: 1px;
    background: rgba(255,255,255,0.1);
    margin: 1.5rem 0 1.2rem;
}

.login-footer {
    text-align: center;
    font-size: 0.78rem;
    color: rgba(255,255,255,0.3);
    margin-top: 1.2rem;
}

/* ── Attempt counter ── */
.attempt-indicator {
    text-align: center;
    font-size: 0.76rem;
    color: rgba(248,113,113,0.7);
    margin-top: 0.5rem;
    min-height: 1rem;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(36px); }
    to   { opacity: 1; transform: translateY(0); }
}

@keyframes shake {
    0%   { transform: translateX(0); }
    25%  { transform: translateX(-6px); }
    50%  { transform: translateX(6px); }
    75%  { transform: translateX(-6px); }
    100% { transform: translateX(0); }
}
</style>
</head>

<body>

<div class="login-card">

    <img src="img/icas_logo.jpeg" alt="ICAS Logo" class="login-logo">
    <div class="login-title">Welcome Back</div>
    <div class="login-subtitle">Sign in to your instructor account</div>

    <form method="POST" autocomplete="off" id="login-form">

        <div class="mb-3">
            <label class="form-label">Username</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-user fa-sm"></i></span>
                <input
                    type="text"
                    name="username"
                    id="username"
                    class="form-control"
                    placeholder="Enter username"
                    required
                    autocomplete="off"
                    maxlength="80"
                >
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-lock fa-sm"></i></span>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    placeholder="Enter password"
                    required
                    autocomplete="off"
                    maxlength="128"
                >
            </div>
        </div>

        <button type="submit" name="login" class="btn-login" id="login-btn">
            <i class="fa fa-right-to-bracket"></i> Sign In
        </button>

        <div class="attempt-indicator" id="attempt-msg"></div>

    </form>

    <?php if ($error): ?>
        <div class="alert-login">
            <i class="fa fa-circle-exclamation"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <hr class="login-divider">
    <div class="login-footer">
        ICAS Attendance System &mdash; Admin Portal
    </div>

</div>

<script>
// ── Client-side brute-force throttle ──
// Adds a delay after repeated failed attempts IN THIS TAB.
// Server-side rate limiting (fail2ban / DB counter) is the real guard.
(function () {
    var KEY      = 'login_fails';
    var LOCKOUT  = 'login_lockout';
    var MAX_FAST = 3;       // attempts before slowdown
    var DELAY_MS = 5000;    // 5-second cooldown after MAX_FAST fails

    var form    = document.getElementById('login-form');
    var btn     = document.getElementById('login-btn');
    var msg     = document.getElementById('attempt-msg');

    // Read stored fail count (sessionStorage so it clears on tab close)
    function getFails()    { return parseInt(sessionStorage.getItem(KEY)  || '0'); }
    function getLockout()  { return parseInt(sessionStorage.getItem(LOCKOUT) || '0'); }

    function updateUI() {
        var fails   = getFails();
        var lockout = getLockout();
        var now     = Date.now();

        if (lockout > now) {
            var secs = Math.ceil((lockout - now) / 1000);
            btn.disabled = true;
            msg.textContent = 'Too many attempts. Wait ' + secs + 's\u2026';
            setTimeout(updateUI, 1000);
        } else {
            btn.disabled = false;
            if (fails >= MAX_FAST) {
                msg.textContent = 'Warning: ' + fails + ' failed attempt(s).';
            } else {
                msg.textContent = '';
            }
        }
    }

    // On page load with a PHP error — count the failed attempt
    <?php if ($error): ?>
    (function () {
        var fails = getFails() + 1;
        sessionStorage.setItem(KEY, fails);
        if (fails >= MAX_FAST) {
            sessionStorage.setItem(LOCKOUT, Date.now() + DELAY_MS);
        }
        updateUI();
    })();
    <?php endif; ?>

    updateUI();

    // Disable submit while locked out
    form.addEventListener('submit', function (e) {
        if (getLockout() > Date.now()) {
            e.preventDefault();
        }
    });

    // Clear on successful navigation away (login worked)
    window.addEventListener('pagehide', function () {
        // Only clear if navigating TO dashboard (not refresh)
        // We leave the counter in place — server already accepted login
    });
})();
</script>

</body>
</html>