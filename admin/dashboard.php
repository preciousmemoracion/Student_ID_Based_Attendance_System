<?php
session_start();
include "../db_connect.php";

// ✅ Block access first
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit();
}

// ✅ Disable caching (remove the duplicate Cache-Control line)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// 📊 Queries
$students = $conn->query("SELECT * FROM students")->num_rows;
$attendance = $conn->query("SELECT * FROM attendance")->num_rows;
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<title>Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<script>
window.addEventListener("pageshow", function(event) {
    if (event.persisted || performance.getEntriesByType("navigation")[0].type === "back_forward") {
        window.location.href = "../index.php";
    }
});
</script>


<style>
:root {
    --blue:    #2563EB;
    --blue-lt: #3B82F6;
    --green:   #059669;
    --green-lt:#10B981;
    --gold:    #F59E0B;
    --surface: rgba(8, 20, 50, 0.55);
    --glass:   rgba(255,255,255,0.08);
    --border:  rgba(255,255,255,0.14);
    --text:    #FFFFFF;
    --muted:   rgba(240,246,255,0.88);
    --radius:  18px;
}

* { box-sizing: border-box; }

body {
    min-height: 100vh;
    margin: 0;
    background: url('../img/icas.jpeg') no-repeat center center / cover fixed;
    font-family: 'DM Sans', sans-serif;
    color: var(--text);
}

/* ── dark overlay over the whole page ── */
body::before {
    content: '';
    position: fixed;
    inset: 0;
    background: linear-gradient(135deg,
        rgba(3,10,35,0.45) 0%,
        rgba(10,30,80,0.38) 60%,
        rgba(3,10,35,0.48) 100%);
    z-index: 0;
}

body > * { position: relative; z-index: 1; }

/* ══════════════════════════════════════
   NAVBAR
══════════════════════════════════════ */
.navbar {
    background: rgba(8,20,60,0.60);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border-bottom: 1px solid var(--border);
    padding: 0.65rem 0;
}

.navbar-brand {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.15rem;
    letter-spacing: 0.4px;
    color: #fff !important;
    gap: 12px;
}

.navbar-brand img {
    width: 42px;
    height: 42px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.25);
    box-shadow: 0 0 0 4px rgba(37,99,235,0.25);
}

.nav-welcome {
    font-size: 0.9rem;
    font-weight: 600;
    color: rgba(255,255,255,0.92);
    display: flex;
    align-items: center;
    gap: 7px;
}

.nav-welcome .dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--green-lt);
    box-shadow: 0 0 6px var(--green-lt);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%,100% { opacity: 1; transform: scale(1); }
    50%      { opacity: 0.5; transform: scale(1.35); }
}

/* ══════════════════════════════════════
   MAIN CONTAINER
══════════════════════════════════════ */
.main-wrap {
    max-width: 1140px;
    margin: 0 auto;
    padding: 2rem 1.25rem 3rem;
}

/* ══════════════════════════════════════
   PAGE HEADING
══════════════════════════════════════ */
.page-heading {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 1.8rem;
}

.page-heading .icon-badge {
    width: 48px; height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--blue), var(--blue-lt));
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    box-shadow: 0 4px 18px rgba(37,99,235,0.45);
    flex-shrink: 0;
}

.page-heading h3 {
    font-family: 'Syne', sans-serif;
    font-size: 2rem !important;
    font-weight: 800 !important;
    letter-spacing: -0.3px;
    margin: 0;
    color: #fff;
    text-shadow: 0 2px 12px rgba(0,0,0,0.6);
}

.page-heading p {
    margin: 2px 0 0;
    font-size: 0.88rem;
    font-weight: 500;
    color: rgba(255,255,255,0.85);
    text-shadow: 0 1px 6px rgba(0,0,0,0.5);
}

/* ══════════════════════════════════════
   STAT CARDS
══════════════════════════════════════ */
.stat-card {
    border-radius: var(--radius);
    padding: 1.6rem 1.75rem;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::after {
    content: '';
    position: absolute;
    top: -30%;
    right: -15%;
    width: 160px; height: 160px;
    border-radius: 50%;
    background: rgba(255,255,255,0.07);
    pointer-events: none;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 40px rgba(0,0,0,0.45) !important;
}

.stat-card.blue {
    background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
    box-shadow: 0 8px 28px rgba(29,78,216,0.45);
}

.stat-card.green {
    background: linear-gradient(135deg, #047857 0%, #10b981 100%);
    box-shadow: 0 8px 28px rgba(4,120,87,0.45);
}

.stat-icon {
    width: 58px; height: 58px;
    border-radius: 14px;
    background: rgba(255,255,255,0.18);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.stat-label {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    opacity: 1;
    margin-bottom: 2px;
    text-shadow: 0 1px 4px rgba(0,0,0,0.3);
}

.stat-number {
    font-family: 'Syne', sans-serif;
    font-size: 3rem;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -1px;
}

.stat-sub {
    font-size: 0.78rem;
    opacity: 0.88;
    margin-top: 3px;
    font-weight: 500;
}

/* ══════════════════════════════════════
   DIVIDER
══════════════════════════════════════ */
.custom-divider {
    border: none;
    height: 1px;
    background: var(--border);
    margin: 1.75rem 0 1.5rem;
}

/* ══════════════════════════════════════
   ACTION BUTTONS
══════════════════════════════════════ */
.actions-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
    align-items: center;
}

.btn {
    font-family: 'DM Sans', sans-serif;
    font-size: 0.84rem;
    font-weight: 600;
    border-radius: 10px;
    padding: 0.5rem 1.1rem;
    letter-spacing: 0.2px;
    border: none;
    transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn:hover {
    transform: translateY(-2px);
    filter: brightness(1.1);
}

.btn:active { transform: translateY(0); }

.btn-success  { background: linear-gradient(135deg,#059669,#34d399); box-shadow: 0 4px 14px rgba(5,150,105,0.35); color:#fff; }
.btn-primary  { background: linear-gradient(135deg,#1d4ed8,#60a5fa); box-shadow: 0 4px 14px rgba(29,78,216,0.35); color:#fff; }
.btn-warning  { background: linear-gradient(135deg,#b45309,#fbbf24); box-shadow: 0 4px 14px rgba(180,83,9,0.35);  color:#fff; }
.btn-info     { background: linear-gradient(135deg,#0369a1,#38bdf8); box-shadow: 0 4px 14px rgba(3,105,161,0.35); color:#fff; }
.btn-danger   { background: linear-gradient(135deg,#b91c1c,#f87171); box-shadow: 0 4px 14px rgba(185,28,28,0.35); color:#fff; }

/* logout pushes right */
.btn-logout { margin-left: auto; }

/* ══════════════════════════════════════
   VALUE CARDS (Excellence / Leadership / Character)
══════════════════════════════════════ */
.value-card {
    background: rgba(8,20,60,0.35);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    padding: 2rem 1.5rem;
    text-align: center;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    position: relative;
    overflow: hidden;
}

.value-card::before {
    content: '';
    position: absolute;
    top: 0; left: 50%;
    transform: translateX(-50%);
    width: 60%;
    height: 2px;
    border-radius: 0 0 4px 4px;
    background: linear-gradient(90deg, transparent, var(--accent, #3B82F6), transparent);
}

.value-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 45px rgba(0,0,0,0.4);
    border-color: rgba(255,255,255,0.28);
}

.value-card .v-icon {
    width: 56px; height: 56px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.35rem;
    margin: 0 auto 1rem;
}

.value-card h5 {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.2rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 0.65rem;
    color: #fff;
    text-shadow: 0 2px 10px rgba(0,0,0,0.6);
}

.value-card p {
    font-size: 0.92rem;
    color: rgba(255,255,255,0.88);
    line-height: 1.75;
    margin: 0;
    text-shadow: 0 1px 6px rgba(0,0,0,0.5);
    font-weight: 500;
    font-style: italic;
    letter-spacing: 0.2px;
}

/* per-card accent colors */
.vc-blue  { --accent: #60a5fa; }
.vc-blue  .v-icon  { background: rgba(59,130,246,0.18); color: #60a5fa; }

.vc-gold  { --accent: #fbbf24; }
.vc-gold  .v-icon  { background: rgba(251,191,36,0.18); color: #fbbf24; }

.vc-rose  { --accent: #fb7185; }
.vc-rose  .v-icon  { background: rgba(251,113,133,0.18); color: #fb7185; }

/* ══════════════════════════════════════
   ENTRANCE ANIMATIONS
══════════════════════════════════════ */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(32px); }
    to   { opacity: 1; transform: translateY(0); }
}

.anim { animation: fadeUp 0.6s ease both; }
.d1 { animation-delay: 0.05s; }
.d2 { animation-delay: 0.15s; }
.d3 { animation-delay: 0.25s; }
.d4 { animation-delay: 0.35s; }
.d5 { animation-delay: 0.45s; }
.d6 { animation-delay: 0.55s; }
</style>

</head>

<body>

<!-- ── NAVBAR ── -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <span class="navbar-brand d-flex align-items-center">
            <img src="../img/icas_logo.jpeg" alt="Logo">
            Attendance System
        </span>
        <span class="nav-welcome ms-auto">
            <span class="dot"></span>
            Welcome, Admin
        </span>
    </div>
</nav>

<!-- ── MAIN ── -->
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
    <div class="row g-3 mb-1">
        <div class="col-md-6 anim d2">
            <div class="stat-card blue shadow">
                <div class="stat-icon"><i class="fa fa-users"></i></div>
                <div>
                    <div class="stat-label">Total Students</div>
                    <div class="stat-number"><?= $students ?></div>
                    <div class="stat-sub">Registered in the system</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 anim d3">
            <div class="stat-card green shadow">
                <div class="stat-icon"><i class="fa fa-clipboard-check"></i></div>
                <div>
                    <div class="stat-label">Total Attendance</div>
                    <div class="stat-number"><?= $attendance ?></div>
                    <div class="stat-sub">Logs recorded so far</div>
                </div>
            </div>
        </div>
    </div>

    <hr class="custom-divider anim d3">

    <!-- Action Buttons -->
    <div class="actions-wrap anim d4">
        <a href="register.php" class="btn btn-success">
            <i class="fa fa-user-plus"></i> Register Student
        </a>
        <a href="students.php" class="btn btn-success">
            <i class="fa fa-graduation-cap"></i> Officially Enrolled Students
        </a>
        <a href="register_subject.php" class="btn btn-success">
            <i class="fa fa-book"></i> Register Subject
        </a>
        <a href="subjects.php" class="btn btn-primary">
            <i class="fa fa-list"></i> View Subjects
        </a>
        <a href="attendance.php" class="btn btn-warning">
            <i class="fa fa-clipboard"></i> Open Attendance
        </a>
        <a href="view.php" class="btn btn-info">
            <i class="fa fa-table"></i> View Records
        </a>
        <a href="../auth/logout.php" class="btn btn-danger btn-logout">
            <i class="fa fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Value Cards -->
    <div class="row mt-4 g-3">

        <div class="col-md-4 anim d4">
            <div class="value-card vc-blue">
                <div class="v-icon"><i class="fa fa-star"></i></div>
                <h5>Excellence</h5>
                <p>Striving for the highest quality in academics, sports, and personal growth.</p>
            </div>
        </div>

        <div class="col-md-4 anim d5">
            <div class="value-card vc-gold">
                <div class="v-icon"><i class="fa fa-chess-king"></i></div>
                <h5>Leadership</h5>
                <p>Inspiring and guiding others while taking responsibility for actions.</p>
            </div>
        </div>

        <div class="col-md-4 anim d6">
            <div class="value-card vc-rose">
                <div class="v-icon"><i class="fa fa-heart"></i></div>
                <h5>Character</h5>
                <p>Showing integrity, respect, and strong moral values in all situations.</p>
            </div>
        </div>

    </div>

</div>

</body>
</html>