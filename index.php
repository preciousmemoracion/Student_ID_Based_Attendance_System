<?php
session_start();
include "db_connect.php";

// If already logged in, redirect to dashboard immediately
if (isset($_SESSION['admin'])) {
    header("Location: admin/dashboard.php");
    exit();
}

// Aggressive no-cache headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

$error = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $query = $conn->query("SELECT * FROM admins WHERE username='$username' AND password='$password'");

    if ($query && $query->num_rows > 0) {
        $admin = $query->fetch_assoc();

        session_regenerate_id(true);
        $_SESSION['admin'] = $admin['id'];

        header("Location: admin/dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
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

    // ══════════════════════════════════════════════
    //  If session still exists and user lands here
    //  via back button — bounce them to dashboard
    // ══════════════════════════════════════════════
    function bounceIfLoggedIn() {
        fetch('admin/check_session.php?_=' + Date.now(), {
            method: 'GET',
            cache: 'no-store',
            credentials: 'same-origin'
        })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            if (data && data.loggedIn) {
                // Replace so login page is removed from history
                window.location.replace('admin/dashboard.php');
            }
        })
        .catch(function () {});
    }

    // Fire on every normal page load
    window.addEventListener('load', function () {
        // Check back_forward navigation type (Chrome / Edge)
        try {
            var nav = performance.getEntriesByType('navigation');
            if (nav.length > 0 && nav[0].type === 'back_forward') {
                bounceIfLoggedIn();
            }
        } catch (e) {}

        // Also check on every load — catches direct URL access while logged in
        bounceIfLoggedIn();
    });

    // Fire on bfcache restore (Safari / Firefox back button)
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) bounceIfLoggedIn();
    });

    // Fire on popstate — catches back button press while on this page
    window.addEventListener('popstate', function () {
        bounceIfLoggedIn();
    });

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

/* ══ CARD ══ */
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

/* ══ LOGO ══ */
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

/* ══ FORM ══ */
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

/* ══ BUTTON ══ */
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

/* ══ ERROR ALERT ══ */
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

/* ══ DIVIDER ══ */
.login-divider {
    border: none;
    height: 1px;
    background: rgba(255,255,255,0.1);
    margin: 1.5rem 0 1.2rem;
}

/* ══ FOOTER TEXT ══ */
.login-footer {
    text-align: center;
    font-size: 0.78rem;
    color: rgba(255,255,255,0.3);
    margin-top: 1.2rem;
}

/* ══ ANIMATIONS ══ */
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

    <!-- LOGO -->
    <img src="img/icas_logo.jpeg" alt="ICAS Logo" class="login-logo">

    <!-- TITLE -->
    <div class="login-title">Welcome Back</div>
    <div class="login-subtitle">Sign in to your instructor account</div>

    <!-- FORM -->
    <form method="POST" autocomplete="off">

        <div class="mb-3">
            <label class="form-label">Username</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-user fa-sm"></i></span>
                <input
                    type="text"
                    name="username"
                    class="form-control"
                    placeholder="Enter username"
                    required
                    autocomplete="off"
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
                    class="form-control"
                    placeholder="Enter password"
                    required
                    autocomplete="off"
                >
            </div>
        </div>

        <button type="submit" name="login" class="btn-login">
            <i class="fa fa-right-to-bracket"></i> Sign In
        </button>

    </form>

    <!-- ERROR MESSAGE -->
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

</body>
</html>