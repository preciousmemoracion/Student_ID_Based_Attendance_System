<?php
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Location: ../index.php");
    exit();
}

session_regenerate_id(true);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Surrogate-Control: no-store");
header("Vary: *");

include "../db_connect.php";

$students   = $conn->query("SELECT * FROM students")->num_rows;
$attendance = $conn->query("SELECT * FROM attendance WHERE DATE(date) = CURDATE()")->num_rows;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<title>Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<script>
(function () {
    'use strict';
    var DASHBOARD_URL = window.location.href;
    var isRedirecting = false;
    var FLOOD_COUNT   = 80;
    try { history.replaceState({ page: 'dashboard', i: 0 }, '', DASHBOARD_URL); } catch(e) {}
    function floodHistory() {
        try { for (var i = 1; i <= FLOOD_COUNT; i++) history.pushState({ page: 'dashboard', i: i }, '', DASHBOARD_URL); } catch(e) {}
    }
    floodHistory();
    window.addEventListener('popstate', function () {
        if (isRedirecting) return;
        try { history.pushState({ page: 'dashboard' }, '', DASHBOARD_URL); } catch(e) {}
        floodHistory(); verifySession();
    });
    window.addEventListener('pageshow', function (e) {
        if (isRedirecting) return;
        if (e.persisted) { floodHistory(); verifySession(); }
    });
    document.addEventListener('visibilitychange', function () {
        if (isRedirecting) return;
        if (document.visibilityState === 'visible') { floodHistory(); verifySession(); }
    });
    window.addEventListener('DOMContentLoaded', function () {
        try {
            var nav = performance.getEntriesByType('navigation');
            if (nav.length > 0 && nav[0].type === 'back_forward') { floodHistory(); verifySession(); }
        } catch (e) {}
    });
    window.addEventListener('load', function () { verifySession(); });
    setInterval(function () { if (!isRedirecting) verifySession(); }, 20000);
    function verifySession() {
        fetch('check_session.php?_=' + Date.now(), {
            method: 'GET', cache: 'no-store', credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { if (!r.ok) { showOverlay(); return null; } return r.json(); })
        .then(function (data) { if (!data) return; if (!data.loggedIn) showOverlay(); })
        .catch(function () { showOverlay(); });
    }
    function showOverlay() {
        if (isRedirecting) return;
        isRedirecting = true;
        document.body.style.pointerEvents = 'none';
        var el = document.getElementById('session-overlay');
        if (el) el.classList.add('show');
        setTimeout(function () { window.location.replace('../index.php'); }, 3000);
    }
    window.__showOverlay = showOverlay;
})();
</script>

<style>
/* ══════════════════════════════════════
   CSS CUSTOM PROPERTIES
══════════════════════════════════════ */
:root {
    --blue:      #2563EB;
    --blue-lt:   #3B82F6;
    --green:     #059669;
    --green-lt:  #10B981;
    --gold:      #F59E0B;
    --border:    rgba(255,255,255,0.14);
    --radius:    16px;

    /* Fluid type scale */
    --fs-xs:   clamp(0.68rem,  1.2vw,  0.76rem);
    --fs-sm:   clamp(0.76rem,  1.4vw,  0.86rem);
    --fs-base: clamp(0.86rem,  1.6vw,  0.96rem);
    --fs-lg:   clamp(0.96rem,  1.8vw,  1.1rem);
    --fs-xl:   clamp(1.1rem,   2.2vw,  1.35rem);
    --fs-2xl:  clamp(1.35rem,  3vw,    1.9rem);
    --fs-stat: clamp(1.8rem,   4.5vw,  2.8rem);

    /* Fluid spacing */
    --sp-xs:  clamp(0.3rem,  0.8vw,  0.5rem);
    --sp-sm:  clamp(0.5rem,  1.2vw,  0.8rem);
    --sp-md:  clamp(0.75rem, 1.8vw,  1.1rem);
    --sp-lg:  clamp(1rem,    2.5vw,  1.5rem);
    --sp-xl:  clamp(1.25rem, 3vw,    2rem);
    --sp-2xl: clamp(1.5rem,  4vw,    2.8rem);
}

/* ── RESET & BASE ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html { scroll-behavior: smooth; }

body {
    min-height: 100vh;
    background: url('../img/icas.jpeg') no-repeat center center / cover fixed;
    font-family: 'DM Sans', sans-serif;
    font-size: var(--fs-base);
    color: #fff;
    -webkit-font-smoothing: antialiased;
}

body::before {
    content: ''; position: fixed; inset: 0;
    background: linear-gradient(135deg,
        rgba(3,10,35,0.48) 0%,
        rgba(10,30,80,0.40) 60%,
        rgba(3,10,35,0.52) 100%);
    z-index: 0;
}

body > * { position: relative; z-index: 1; }

/* ══════════════════════════════════════
   NAVBAR
══════════════════════════════════════ */
.navbar {
    background: rgba(5,12,40,0.72);
    backdrop-filter: blur(22px) saturate(180%);
    -webkit-backdrop-filter: blur(22px) saturate(180%);
    border-bottom: 1px solid var(--border);
    padding: clamp(0.45rem, 1.2vw, 0.7rem) 0;
    position: sticky; top: 0; z-index: 200;
}

.navbar-inner {
    max-width: min(1200px, 96vw);
    margin: 0 auto;
    padding: 0 clamp(0.75rem, 3vw, 1.5rem);
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem;
}

.brand {
    display: flex; align-items: center;
    gap: clamp(7px, 1.5vw, 12px);
    text-decoration: none; flex-shrink: 0;
}

.brand-logo {
    width: clamp(32px, 5vw, 42px);
    height: clamp(32px, 5vw, 42px);
    object-fit: cover; border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.25);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.22);
    flex-shrink: 0;
}

.brand-name {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: clamp(0.85rem, 2vw, 1.1rem);
    color: #fff; letter-spacing: 0.3px;
    white-space: nowrap;
}
.brand-name span { color: #60A5FA; }

.nav-welcome {
    display: flex; align-items: center;
    gap: clamp(5px, 1vw, 8px);
    font-size: var(--fs-sm);
    font-weight: 600;
    color: rgba(255,255,255,0.9);
    white-space: nowrap;
}

.nav-dot {
    width: clamp(6px, 1vw, 8px);
    height: clamp(6px, 1vw, 8px);
    border-radius: 50%;
    background: var(--green-lt);
    box-shadow: 0 0 8px var(--green-lt);
    animation: pulse 2s infinite;
    flex-shrink: 0;
}

@keyframes pulse {
    0%,100% { opacity:1; transform:scale(1); }
    50%      { opacity:0.5; transform:scale(1.4); }
}

/* ══════════════════════════════════════
   MAIN LAYOUT
══════════════════════════════════════ */
.main-wrap {
    max-width: min(1200px, 96vw);
    margin: 0 auto;
    padding: var(--sp-xl) clamp(0.75rem, 3vw, 1.5rem) var(--sp-2xl);
}

/* ══════════════════════════════════════
   PAGE HEADING
══════════════════════════════════════ */
.page-heading {
    display: flex; align-items: center;
    gap: clamp(10px, 2vw, 16px);
    margin-bottom: var(--sp-xl);
}

.icon-badge {
    width: clamp(40px, 6vw, 52px);
    height: clamp(40px, 6vw, 52px);
    border-radius: clamp(10px, 1.8vw, 14px);
    background: linear-gradient(135deg, var(--blue), var(--blue-lt));
    display: flex; align-items: center; justify-content: center;
    font-size: clamp(1rem, 2vw, 1.3rem);
    box-shadow: 0 4px 20px rgba(37,99,235,0.45);
    flex-shrink: 0;
}

.page-heading h3 {
    font-family: 'Syne', sans-serif;
    font-size: var(--fs-2xl);
    font-weight: 800; letter-spacing: -0.3px;
    color: #fff; line-height: 1.15;
    text-shadow: 0 2px 14px rgba(0,0,0,0.6);
}

.page-heading p {
    font-size: var(--fs-sm); font-weight: 500;
    color: rgba(255,255,255,0.75);
    margin-top: 2px;
    text-shadow: 0 1px 6px rgba(0,0,0,0.5);
}

/* ══════════════════════════════════════
   STAT CARDS
══════════════════════════════════════ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(260px, 100%), 1fr));
    gap: clamp(0.6rem, 2vw, 1rem);
    margin-bottom: var(--sp-md);
}

.stat-card {
    border-radius: var(--radius);
    padding: clamp(1rem, 3vw, 1.6rem) clamp(1rem, 3vw, 1.75rem);
    display: flex; align-items: center;
    gap: clamp(0.75rem, 2vw, 1.25rem);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    position: relative; overflow: hidden;
}

.stat-card::after {
    content: ''; position: absolute; top: -30%; right: -12%;
    width: clamp(90px, 14vw, 150px);
    height: clamp(90px, 14vw, 150px);
    border-radius: 50%;
    background: rgba(255,255,255,0.07); pointer-events: none;
}

.stat-card:hover { transform: translateY(-4px); }
.stat-card.blue  { background: linear-gradient(135deg,#1d4ed8,#3b82f6); box-shadow: 0 8px 28px rgba(29,78,216,0.45); }
.stat-card.green { background: linear-gradient(135deg,#047857,#10b981); box-shadow: 0 8px 28px rgba(4,120,87,0.45); }
.stat-card:hover.blue  { box-shadow: 0 16px 40px rgba(29,78,216,0.6); }
.stat-card:hover.green { box-shadow: 0 16px 40px rgba(4,120,87,0.6); }

.stat-icon {
    width: clamp(42px, 6.5vw, 58px);
    height: clamp(42px, 6.5vw, 58px);
    border-radius: clamp(10px, 1.5vw, 14px);
    background: rgba(255,255,255,0.18);
    display: flex; align-items: center; justify-content: center;
    font-size: clamp(1.1rem, 2.2vw, 1.5rem);
    flex-shrink: 0;
}

.stat-label {
    font-size: var(--fs-xs); font-weight: 700;
    text-transform: uppercase; letter-spacing: 1.2px;
    opacity: 0.9; margin-bottom: 2px;
}

.stat-number {
    font-family: 'Syne', sans-serif;
    font-size: var(--fs-stat);
    font-weight: 800; line-height: 1; letter-spacing: -1px;
}

.stat-sub { font-size: var(--fs-xs); opacity: 0.8; margin-top: 3px; }

/* ══════════════════════════════════════
   DIVIDER
══════════════════════════════════════ */
.custom-divider {
    border: none; height: 1px;
    background: var(--border);
    margin: var(--sp-lg) 0 var(--sp-md);
}

/* ══════════════════════════════════════
   ACTION BUTTONS
══════════════════════════════════════ */
.actions-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: clamp(0.4rem, 1.2vw, 0.65rem);
}

.btn-action {
    display: flex; align-items: center; justify-content: center;
    gap: clamp(5px, 1vw, 8px);
    padding: clamp(0.55rem, 1.5vw, 0.75rem) clamp(0.75rem, 2vw, 1.1rem);
    border-radius: clamp(8px, 1.5vw, 12px);
    border: none; cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    font-size: clamp(0.76rem, 1.4vw, 0.88rem);
    font-weight: 700; letter-spacing: 0.2px;
    text-decoration: none; color: #fff;
    white-space: normal;
    text-align: center;
    transition: transform 0.18s ease, filter 0.18s ease, box-shadow 0.18s ease;
    width: 100%;
}

.btn-action:hover  { transform: translateY(-2px); filter: brightness(1.1); color: #fff; }
.btn-action:active { transform: translateY(0); }

.btn-action i { font-size: clamp(0.8rem, 1.4vw, 0.95rem); flex-shrink: 0; }

.btn-success { background: linear-gradient(135deg,#059669,#34d399); box-shadow: 0 4px 14px rgba(5,150,105,0.35); }
.btn-primary { background: linear-gradient(135deg,#1d4ed8,#60a5fa); box-shadow: 0 4px 14px rgba(29,78,216,0.35); }
.btn-warning { background: linear-gradient(135deg,#b45309,#fbbf24); box-shadow: 0 4px 14px rgba(180,83,9,0.35); }
.btn-info    { background: linear-gradient(135deg,#0369a1,#38bdf8); box-shadow: 0 4px 14px rgba(3,105,161,0.35); }
.btn-danger  { background: linear-gradient(135deg,#b91c1c,#f87171); box-shadow: 0 4px 14px rgba(185,28,28,0.35); }

/* ══════════════════════════════════════
   VALUE CARDS
══════════════════════════════════════ */
.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(220px, 100%), 1fr));
    gap: clamp(0.6rem, 2vw, 1rem);
    margin-top: var(--sp-lg);
}

.value-card {
    background: rgba(8,20,60,0.38);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    padding: clamp(1.1rem, 3vw, 1.8rem) clamp(1rem, 2.5vw, 1.4rem);
    text-align: center;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    position: relative; overflow: hidden;
}

.value-card::before {
    content: ''; position: absolute; top: 0; left: 50%;
    transform: translateX(-50%); width: 55%; height: 2px;
    border-radius: 0 0 4px 4px;
    background: linear-gradient(90deg, transparent, var(--accent, #3B82F6), transparent);
}

.value-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 45px rgba(0,0,0,0.4);
    border-color: rgba(255,255,255,0.28);
}

.v-icon {
    width: clamp(44px, 6.5vw, 56px);
    height: clamp(44px, 6.5vw, 56px);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: clamp(1.1rem, 2vw, 1.35rem);
    margin: 0 auto clamp(0.6rem, 1.5vw, 0.9rem);
}

.value-card h5 {
    font-family: 'Syne', sans-serif;
    font-size: clamp(0.82rem, 1.6vw, 1rem);
    font-weight: 800; letter-spacing: 2px;
    text-transform: uppercase; margin-bottom: clamp(0.4rem, 1vw, 0.6rem);
    color: #fff;
}

.value-card p {
    font-size: clamp(0.78rem, 1.3vw, 0.88rem);
    color: rgba(255,255,255,0.82); line-height: 1.75;
    font-weight: 500; font-style: italic; letter-spacing: 0.2px;
}

.vc-blue { --accent: #60a5fa; }
.vc-blue .v-icon { background: rgba(59,130,246,0.18); color: #60a5fa; }
.vc-gold { --accent: #fbbf24; }
.vc-gold .v-icon { background: rgba(251,191,36,0.18);  color: #fbbf24; }
.vc-rose { --accent: #fb7185; }
.vc-rose .v-icon { background: rgba(251,113,133,0.18); color: #fb7185; }

/* ══════════════════════════════════════
   SESSION OVERLAY
══════════════════════════════════════ */
#session-overlay {
    display: none; position: fixed; inset: 0; z-index: 99999;
    background: rgba(3,8,30,0.96);
    backdrop-filter: blur(22px);
    -webkit-backdrop-filter: blur(22px);
    flex-direction: column; align-items: center; justify-content: center;
    gap: 1rem; text-align: center; padding: 2rem;
}
#session-overlay.show { display: flex; }
#session-overlay .ov-icon {
    width: 76px; height: 76px; border-radius: 50%;
    background: rgba(220,38,38,0.18);
    border: 2px solid rgba(220,38,38,0.45);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.9rem; color: #f87171;
    animation: fadeUp 0.5s ease both;
}
#session-overlay h4 {
    font-family: 'Syne', sans-serif; font-size: 1.45rem; font-weight: 800;
    color: #fff; animation: fadeUp 0.5s ease 0.1s both;
}
#session-overlay p {
    font-size: 0.9rem; color: rgba(255,255,255,0.55);
    animation: fadeUp 0.5s ease 0.2s both;
}
#session-overlay .countdown {
    font-size: 0.8rem; color: rgba(255,255,255,0.38);
    animation: fadeUp 0.5s ease 0.25s both;
}
#session-overlay a {
    display: inline-flex; align-items: center; gap: 8px;
    background: linear-gradient(135deg,#1d4ed8,#60a5fa);
    color: #fff; text-decoration: none; border-radius: 12px;
    padding: 0.75rem 1.8rem; font-weight: 700; font-size: 0.9rem;
    box-shadow: 0 6px 20px rgba(29,78,216,0.45);
    animation: fadeUp 0.5s ease 0.3s both;
    transition: filter 0.2s, transform 0.2s;
}
#session-overlay a:hover { filter: brightness(1.12); transform: translateY(-2px); color: #fff; }

/* ══════════════════════════════════════
   ANIMATIONS
══════════════════════════════════════ */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(26px); }
    to   { opacity: 1; transform: translateY(0); }
}
.anim { animation: fadeUp 0.6s ease both; }
.d1 { animation-delay: 0.05s; }
.d2 { animation-delay: 0.12s; }
.d3 { animation-delay: 0.20s; }
.d4 { animation-delay: 0.28s; }
.d5 { animation-delay: 0.36s; }
.d6 { animation-delay: 0.44s; }
.d7 { animation-delay: 0.52s; }

/* ══════════════════════════════════════
   RESPONSIVE OVERRIDES
══════════════════════════════════════ */

/* Large tablets & small laptops — 4 per row */
@media (max-width: 1024px) {
    .actions-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Tablets — 3 per row */
@media (max-width: 768px) {
    .actions-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    .values-grid {
        grid-template-columns: 1fr;
    }
    .stat-card {
        padding: 1rem 1.1rem;
    }
}

/* Large phones — 2 per row */
@media (max-width: 540px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .page-heading {
        gap: 10px;
    }
}

/* Small phones — 1 per row */
@media (max-width: 380px) {
    .actions-grid {
        grid-template-columns: 1fr;
    }
    .stat-card {
        flex-direction: column;
        text-align: center;
    }
    .page-heading {
        flex-direction: column;
        text-align: center;
    }
    .brand-name { display: none; }
}
</style>
</head>

<body>

<!-- SESSION EXPIRED OVERLAY -->
<div id="session-overlay">
    <div class="ov-icon"><i class="fa fa-lock"></i></div>
    <h4>Session Expired</h4>
    <p>You have been logged out. Please sign in again to continue.</p>
    <p class="countdown" id="overlay-countdown">Redirecting in 3 seconds…</p>
    <a href="../index.php"><i class="fa fa-right-to-bracket"></i> Go to Login</a>
</div>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="#">
            <img src="../img/icas_logo.jpeg" alt="Logo" class="brand-logo">
            <span class="brand-name">Attendance <span>System</span></span>
        </a>
        <div class="nav-welcome">
            <span class="nav-dot"></span>
            Welcome, Admin
        </div>
    </div>
</nav>

<!-- MAIN -->
<div class="main-wrap">

    <!-- Page Heading -->
    <div class="page-heading anim d1">
        <div class="icon-badge"><i class="fa fa-gauge-high"></i></div>
        <div>
            <h3>Admin Dashboard</h3>
            <p>Overview of students and attendance records</p>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid anim d2">
        <div class="stat-card blue">
            <div class="stat-icon"><i class="fa fa-users"></i></div>
            <div>
                <div class="stat-label">Total Students</div>
                <div class="stat-number"><?= $students ?></div>
                <div class="stat-sub">Registered in the system</div>
            </div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon"><i class="fa fa-clipboard-check"></i></div>
            <div>
                <div class="stat-label">Today's Attendance</div>
                <div class="stat-number"><?= $attendance ?></div>
                <div class="stat-sub">Logs recorded today</div>
            </div>
        </div>
    </div>

    <hr class="custom-divider anim d3">

    <!-- Action Buttons -->
    <div class="actions-grid anim d4">
        <a href="register.php"         class="btn-action btn-success"><i class="fa fa-user-plus"></i> Register Student</a>
        <a href="students.php"         class="btn-action btn-success"><i class="fa fa-graduation-cap"></i> Enrolled Students</a>
        <a href="register_subject.php" class="btn-action btn-success"><i class="fa fa-book"></i> Register Subject</a>
        <a href="subjects.php"         class="btn-action btn-primary"><i class="fa fa-list"></i> View Subjects</a>
        <a href="attendance.php"       class="btn-action btn-warning"><i class="fa fa-clipboard"></i> Open Attendance</a>
        <a href="view.php"             class="btn-action btn-info"><i class="fa fa-table"></i> View Records</a>
        <a href="../auth/logout.php"   class="btn-action btn-danger"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Value Cards -->
    <div class="values-grid">
        <div class="value-card vc-blue anim d5">
            <div class="v-icon"><i class="fa fa-star"></i></div>
            <h5>Excellence</h5>
            <p>Striving for the highest quality in academics, sports, and personal growth.</p>
        </div>
        <div class="value-card vc-gold anim d6">
            <div class="v-icon"><i class="fa fa-chess-king"></i></div>
            <h5>Leadership</h5>
            <p>Inspiring and guiding others while taking responsibility for actions.</p>
        </div>
        <div class="value-card vc-rose anim d7">
            <div class="v-icon"><i class="fa fa-heart"></i></div>
            <h5>Character</h5>
            <p>Showing integrity, respect, and strong moral values in all situations.</p>
        </div>
    </div>

</div>

<script>
(function () {
    var overlay     = document.getElementById('session-overlay');
    var countdownEl = document.getElementById('overlay-countdown');
    var observer = new MutationObserver(function () {
        if (overlay.classList.contains('show')) {
            var secs = 3;
            countdownEl.textContent = 'Redirecting in ' + secs + ' seconds\u2026';
            var interval = setInterval(function () {
                secs--;
                if (secs <= 0) {
                    clearInterval(interval);
                    countdownEl.textContent = 'Redirecting\u2026';
                } else {
                    countdownEl.textContent = 'Redirecting in ' + secs + ' seconds\u2026';
                }
            }, 1000);
            observer.disconnect();
        }
    });
    observer.observe(overlay, { attributes: true, attributeFilter: ['class'] });
})();
</script>

</body>
</html>