<?php 
include "../db_connect.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['admin'])){
    header("Location: ../index.php");
    exit();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

/* ======================================================
   FILTER INPUTS
====================================================== */
$date             = isset($_GET['date'])    ? $_GET['date']    : date("Y-m-d");
$selected_section = isset($_GET['section']) ? $_GET['section'] : "";
$search_name      = isset($_GET['name'])    ? trim($_GET['name']) : "";
$current_day      = date("l", strtotime($date));

/* ======================================================
   SECTIONS
====================================================== */
$sections_list = [
    "1A","1B","1C",
    "2A","2B","2C",
    "3A","3B","3C",
    "4A","4B","4C"
];

$hasData = false;

/* ======================================================
   GET DATA
====================================================== */
$sql = "
SELECT 
    a.student_id,
    a.status,
    a.time,
    a.section,
    a.subject,
    s.name
FROM attendance a
JOIN students s ON s.student_id = a.student_id
WHERE a.date = ?
";

$params     = [$date];
$paramTypes = "s";

if ($selected_section) {
    $sql .= " AND a.section = ?";
    $params[]    = $selected_section;
    $paramTypes .= "s";
}

if ($search_name !== "") {
    $sql .= " AND s.name LIKE ?";
    $params[]    = "%" . $search_name . "%";
    $paramTypes .= "s";
}

$sql .= " ORDER BY a.section, a.subject, s.name";

$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$grouped = [];
$totals  = ['Present'=>0,'Late'=>0,'Absent'=>0,'Too Early'=>0];

while($row = $result->fetch_assoc()){
    $grouped[$row['section']][$row['subject']][] = $row;
    if(isset($totals[$row['status']])) $totals[$row['status']]++;
}

$grandTotal = array_sum($totals);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance Records</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* ════════════════════════════════
   ROOT & BASE
════════════════════════════════ */
:root {
    --blue:     #2563EB;
    --blue-lt:  #3B82F6;
    --green:    #059669;
    --border:   rgba(255,255,255,0.10);
    --border2:  rgba(255,255,255,0.06);
    --muted:    rgba(255,255,255,0.88);
    --radius:   18px;

    --c-green:    #10B981;
    --c-green-bg: rgba(16,185,129,0.14);
    --c-green-br: rgba(16,185,129,0.35);
    --c-amber:    #F59E0B;
    --c-amber-bg: rgba(245,158,11,0.14);
    --c-amber-br: rgba(245,158,11,0.35);
    --c-red:      #EF4444;
    --c-red-bg:   rgba(239,68,68,0.14);
    --c-red-br:   rgba(239,68,68,0.35);
    --c-sky:      #38BDF8;
    --c-sky-bg:   rgba(56,189,248,0.14);
    --c-sky-br:   rgba(56,189,248,0.35);
    --c-violet:   #8B5CF6;
    --c-violet-bg:rgba(139,92,246,0.14);
    --c-violet-br:rgba(139,92,246,0.35);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }

body {
    min-height: 100vh;
    font-family: 'DM Sans', sans-serif;
    background: url('../img/icas.jpeg') no-repeat center center / cover fixed;
    color: #fff;
    overflow-x: hidden;
}

body::before {
    content: '';
    position: fixed; inset: 0;
    background: linear-gradient(135deg,
        rgba(3,10,35,0.55) 0%,
        rgba(10,30,80,0.48) 60%,
        rgba(3,10,35,0.58) 100%);
    z-index: 0;
}

body > * { position: relative; z-index: 1; }

/* ════════════════════════════════
   TOPBAR  (matches students.php)
════════════════════════════════ */
.topbar {
    position: sticky; top: 0; z-index: 200;
    background: rgba(5,12,40,0.75);
    backdrop-filter: blur(22px) saturate(180%);
    -webkit-backdrop-filter: blur(22px) saturate(180%);
    border-bottom: 1px solid var(--border);
    padding: 0.6rem 0;
}

.topbar-inner {
    max-width: 1280px; margin: 0 auto;
    padding: 0 1.5rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
}

.brand { display: flex; align-items: center; gap: 11px; text-decoration: none; }

.brand-logo {
    width: 40px; height: 40px; border-radius: 12px; object-fit: cover;
    border: 1.5px solid rgba(59,130,246,0.45);
    box-shadow: 0 0 0 3px rgba(59,130,246,0.14), 0 4px 14px rgba(0,0,0,0.4);
}

.brand-name {
    font-family: 'Outfit', sans-serif;
    font-size: 1rem; font-weight: 800;
    color: #fff; letter-spacing: -0.2px;
}
.brand-name span { color: #60A5FA; }

.btn-nav-back {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,0.09);
    border: 1px solid var(--border); color: rgba(255,255,255,0.88);
    border-radius: 12px; padding: 0.38rem 1rem;
    font-family: 'Outfit', sans-serif;
    font-size: 0.82rem; font-weight: 700;
    text-decoration: none;
    transition: background 0.2s, transform 0.2s;
}
.btn-nav-back:hover { background: rgba(255,255,255,0.14); transform: translateX(-3px); color: #fff; }
.btn-nav-back i { font-size: 0.78rem; }

/* ════════════════════════════════
   MAIN WRAP
════════════════════════════════ */
.wrap { max-width: 1280px; margin: 0 auto; padding: 2.5rem 1.5rem 5rem; }

/* ════════════════════════════════
   PAGE HEADING
════════════════════════════════ */
.page-heading {
    display: flex; align-items: center; gap: 14px;
    margin-bottom: 1.8rem;
    animation: fadeUp 0.5s ease both;
}

.page-heading .icon-badge {
    width: 52px; height: 52px; border-radius: 16px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--blue), var(--blue-lt));
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
    box-shadow: 0 6px 22px rgba(37,99,235,0.45);
}

.page-heading h3 {
    font-family: 'Syne', sans-serif;
    font-size: 1.9rem; font-weight: 800; margin: 0;
    color: #fff; text-shadow: 0 2px 12px rgba(0,0,0,0.6);
}

.page-heading p {
    margin: 3px 0 0; font-size: 0.88rem; font-weight: 500;
    color: var(--muted); text-shadow: 0 1px 6px rgba(0,0,0,0.5);
}

/* ════════════════════════════════
   SUMMARY PILLS
════════════════════════════════ */
.summary-row {
    display: flex; gap: 0.75rem; flex-wrap: wrap;
    margin-bottom: 1.8rem;
    animation: fadeUp 0.5s ease 0.07s both;
}

.sum-pill {
    flex: 1; min-width: 120px;
    background: rgba(8,20,60,0.40);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid var(--border);
    border-radius: 14px; padding: 0.9rem 1.2rem;
    display: flex; align-items: center; gap: 12px;
    transition: transform 0.2s ease;
    box-shadow: 0 4px 18px rgba(0,0,0,0.2);
}

.sum-pill:hover { transform: translateY(-2px); }

.sum-pill.s-total   { border-color: rgba(59,130,246,0.30); }
.sum-pill.s-present { border-color: var(--c-green-br); }
.sum-pill.s-late    { border-color: var(--c-amber-br); }
.sum-pill.s-absent  { border-color: var(--c-red-br); }

.pill-icon {
    width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 0.9rem;
}

.s-total   .pill-icon { background: rgba(59,130,246,0.20); color: #93C5FD; }
.s-present .pill-icon { background: var(--c-green-bg);     color: var(--c-green); }
.s-late    .pill-icon { background: var(--c-amber-bg);     color: var(--c-amber); }
.s-absent  .pill-icon { background: var(--c-red-bg);       color: var(--c-red); }

.pill-num {
    font-family: 'JetBrains Mono', monospace;
    font-size: 1.6rem; font-weight: 700; line-height: 1; color: #fff;
}

.pill-label {
    font-size: 0.72rem; font-weight: 600; color: rgba(255,255,255,0.55);
    text-transform: uppercase; letter-spacing: 0.8px; margin-top: 3px;
}

/* ════════════════════════════════
   FILTER CARD
════════════════════════════════ */
.filter-card {
    background: rgba(8,20,60,0.40);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid var(--border);
    border-radius: var(--radius); padding: 1.3rem 1.5rem;
    margin-bottom: 1.8rem;
    box-shadow: 0 4px 18px rgba(0,0,0,0.2);
    animation: fadeUp 0.5s ease 0.12s both;
}

.filter-title {
    font-family: 'Syne', sans-serif;
    font-size: 0.75rem; font-weight: 700; color: rgba(255,255,255,0.6);
    text-transform: uppercase; letter-spacing: 1.2px;
    margin-bottom: 1rem;
    display: flex; align-items: center; gap: 7px;
}
.filter-title i { color: #60A5FA; }

.filter-row {
    display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;
}

.f-group { display: flex; flex-direction: column; gap: 0.38rem; flex: 1; min-width: 150px; }

.f-label {
    font-size: 0.72rem; font-weight: 700; color: rgba(255,255,255,0.55);
    text-transform: uppercase; letter-spacing: 0.9px;
    display: flex; align-items: center; gap: 5px;
}
.f-label i { font-size: 0.65rem; color: #60A5FA; }

.f-search-wrap { position: relative; display: flex; align-items: center; }
.f-search-wrap .search-icon {
    position: absolute; left: 11px;
    color: rgba(255,255,255,0.35); font-size: 0.78rem; pointer-events: none;
}
.f-search-wrap .f-input { padding-left: 2.1rem; }
.f-search-wrap .clear-btn {
    position: absolute; right: 10px;
    background: none; border: none; color: rgba(255,255,255,0.35);
    font-size: 0.78rem; cursor: pointer; padding: 0;
    transition: color 0.18s;
    display: <?= $search_name ? 'flex' : 'none' ?>;
    align-items: center;
}
.f-search-wrap .clear-btn:hover { color: var(--c-red); }

.f-input {
    background: rgba(255,255,255,0.08);
    border: 1px solid var(--border); border-radius: 10px;
    color: #fff; padding: 0.58rem 0.9rem;
    font-size: 0.86rem; font-family: 'DM Sans', sans-serif; font-weight: 500;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    -webkit-appearance: none; appearance: none; width: 100%;
}
.f-input::placeholder { color: rgba(255,255,255,0.28); }
.f-input::-webkit-calendar-picker-indicator { filter: invert(1) opacity(0.5); }

select.f-input {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='rgba(255,255,255,0.4)' d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center; background-size: 11px;
    padding-right: 2rem;
}
select.f-input option { background: #0d1a4a; color: #fff; }

.f-input:focus {
    outline: none; border-color: var(--blue-lt);
    background: rgba(255,255,255,0.13);
    box-shadow: 0 0 0 3px rgba(59,130,246,0.18);
}

.filter-meta {
    margin-top: 0.9rem; padding-top: 0.9rem;
    border-top: 1px solid var(--border2);
    font-size: 0.78rem; color: rgba(255,255,255,0.5);
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.filter-meta i { color: #60A5FA; }
.filter-meta strong { color: #fff; font-weight: 700; }

.active-tag {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--c-violet-bg); border: 1px solid var(--c-violet-br);
    color: #C4B5FD; border-radius: 20px;
    padding: 0.18rem 0.65rem; font-size: 0.7rem; font-weight: 700;
}

/* ════════════════════════════════
   BUTTONS
════════════════════════════════ */
.btn-search {
    background: linear-gradient(135deg, #1D4ED8, #3B82F6);
    color: #fff; border: none; border-radius: 10px;
    padding: 0.6rem 1.25rem;
    font-family: 'Outfit', sans-serif;
    font-size: 0.85rem; font-weight: 700;
    cursor: pointer; white-space: nowrap;
    box-shadow: 0 4px 16px rgba(29,78,216,0.35);
    transition: transform 0.18s ease, filter 0.18s ease;
    display: inline-flex; align-items: center; gap: 6px;
    text-decoration: none;
}
.btn-search:hover { transform: translateY(-2px); filter: brightness(1.1); color: #fff; }

.btn-reset {
    background: rgba(255,255,255,0.09); border: 1px solid var(--border);
    color: rgba(255,255,255,0.75); border-radius: 10px;
    padding: 0.6rem 1.1rem;
    font-family: 'Outfit', sans-serif;
    font-size: 0.85rem; font-weight: 700;
    cursor: pointer; white-space: nowrap;
    transition: background 0.18s ease;
    display: inline-flex; align-items: center; gap: 6px;
    text-decoration: none;
}
.btn-reset:hover { background: rgba(255,255,255,0.14); color: #fff; }

/* ════════════════════════════════
   LIVE SEARCH BAR
════════════════════════════════ */
.live-search-bar {
    background: rgba(8,20,60,0.40);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid var(--c-violet-br);
    border-radius: 14px; padding: 0.85rem 1.2rem;
    margin-bottom: 1.6rem;
    display: flex; align-items: center; gap: 12px;
    animation: fadeUp 0.5s ease 0.15s both;
    box-shadow: 0 0 0 3px var(--c-violet-bg), 0 4px 18px rgba(0,0,0,0.2);
}
.live-search-bar i { color: var(--c-violet); font-size: 1rem; flex-shrink: 0; }

.live-input {
    flex: 1; background: none; border: none;
    color: #fff; font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem; font-weight: 500; outline: none;
}
.live-input::placeholder { color: rgba(255,255,255,0.28); }

.live-count {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.75rem; font-weight: 600;
    color: rgba(255,255,255,0.5); flex-shrink: 0; transition: color 0.2s;
}

.hl { background: rgba(139,92,246,0.35); border-radius: 3px; color: #DDD6FE; font-weight: 700; }

/* ════════════════════════════════
   SECTION BLOCKS
════════════════════════════════ */
.section-block {
    margin-bottom: 2.5rem;
    animation: fadeUp 0.65s ease both;
}
.section-block:nth-child(1) { animation-delay: 0.15s; }
.section-block:nth-child(2) { animation-delay: 0.21s; }
.section-block:nth-child(3) { animation-delay: 0.27s; }
.section-block:nth-child(n+4) { animation-delay: 0.33s; }

.subject-block { margin-bottom: 1.5rem; }
.subject-block:last-child { margin-bottom: 0; }

/* ════════════════════════════════
   RECORD CARD
════════════════════════════════ */
.record-card {
    background: rgba(8,20,60,0.40);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid var(--border);
    border-radius: var(--radius); overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,0.25);
}

.rc-head {
    padding: 1rem 1.4rem;
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap;
    background: rgba(29,78,216,0.22);
    border-bottom: 1px solid var(--border2);
    position: relative; overflow: hidden;
}

.rc-head::after {
    content: '';
    position: absolute; top: -50%; right: -20px;
    width: 120px; height: 120px;
    background: radial-gradient(circle, rgba(59,130,246,0.18) 0%, transparent 70%);
    pointer-events: none;
}

.rc-section-badge { display: inline-flex; align-items: center; gap: 10px; }

.sec-bubble {
    width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
    background: linear-gradient(135deg, #1D4ED8, #3B82F6);
    display: flex; align-items: center; justify-content: center;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.8rem; font-weight: 700; color: #fff;
    box-shadow: 0 4px 12px rgba(29,78,216,0.4);
}

.rc-sec-title {
    font-family: 'Syne', sans-serif;
    font-size: 1.05rem; font-weight: 800; color: #fff; letter-spacing: -0.2px;
}
.rc-sub-label { font-size: 0.78rem; font-weight: 500; color: rgba(255,255,255,0.50); margin-top: 2px; }

.rc-stats { display: flex; gap: 0.5rem; flex-wrap: wrap; }

.mini-stat {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.72rem; font-weight: 700;
    padding: 0.22rem 0.65rem; border-radius: 20px; border: 1px solid;
    font-family: 'JetBrains Mono', monospace; letter-spacing: 0.2px;
}
.ms-p { background: var(--c-green-bg); border-color: var(--c-green-br); color: #34D399; }
.ms-l { background: var(--c-amber-bg); border-color: var(--c-amber-br); color: #FCD34D; }
.ms-a { background: var(--c-red-bg);   border-color: var(--c-red-br);   color: #FCA5A5; }

/* ════════════════════════════════
   STUDENT ROWS
════════════════════════════════ */
.student-list { padding: 0.4rem 0; }

.student-row {
    display: flex; align-items: center; gap: 1rem;
    padding: 0.7rem 1.4rem;
    border-bottom: 1px solid var(--border2);
    transition: background 0.18s ease;
}
.student-row:last-child { border-bottom: none; }
.student-row:hover { background: rgba(59,130,246,0.07); }
.student-row.hidden-row { display: none; }

.stu-avatar {
    width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
    background: linear-gradient(135deg, rgba(59,130,246,0.3), rgba(139,92,246,0.3));
    border: 1px solid rgba(255,255,255,0.10);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.72rem; font-weight: 800; color: #fff; text-transform: uppercase;
}

.stu-id {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.76rem; font-weight: 500;
    color: rgba(255,255,255,0.40); flex-shrink: 0; width: 110px;
}

.stu-name {
    font-size: 0.88rem; font-weight: 600; color: #fff; flex: 1;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    text-shadow: 0 1px 4px rgba(0,0,0,0.4);
}

.stu-time {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.76rem; font-weight: 500; color: rgba(255,255,255,0.38); flex-shrink: 0;
}

.status-badge {
    flex-shrink: 0; min-width: 80px; text-align: center;
    font-size: 0.72rem; font-weight: 700;
    padding: 0.25rem 0.7rem; border-radius: 20px; border: 1px solid; letter-spacing: 0.3px;
}
.sb-present { background: var(--c-green-bg); border-color: var(--c-green-br); color: #34D399; }
.sb-late    { background: var(--c-amber-bg); border-color: var(--c-amber-br); color: #FCD34D; }
.sb-absent  { background: var(--c-red-bg);   border-color: var(--c-red-br);   color: #FCA5A5; }
.sb-early   { background: var(--c-sky-bg);   border-color: var(--c-sky-br);   color: #7DD3FC; }

.no-match-row {
    display: none; padding: 1.2rem 1.4rem;
    color: rgba(255,255,255,0.45); font-size: 0.84rem;
    font-style: italic; align-items: center; gap: 8px;
}
.no-match-row i { color: rgba(139,92,246,0.6); }

/* ════════════════════════════════
   EMPTY STATE
════════════════════════════════ */
.empty-state {
    background: rgba(8,20,60,0.40);
    backdrop-filter: blur(16px);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    text-align: center; padding: 5rem 1rem;
    color: rgba(255,255,255,0.45);
    animation: fadeUp 0.5s ease 0.1s both;
    box-shadow: 0 4px 18px rgba(0,0,0,0.2);
}
.empty-state .e-icon { font-size: 3rem; opacity: 0.2; display: block; margin-bottom: 1rem; }
.empty-state h4 { font-family: 'Syne', sans-serif; font-size: 1.1rem; font-weight: 700; color: rgba(255,255,255,0.45); }
.empty-state p  { font-size: 0.85rem; margin-top: 0.4rem; font-style: italic; }

/* ════════════════════════════════
   ANIMATIONS
════════════════════════════════ */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(26px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ════════════════════════════════
   RESPONSIVE
════════════════════════════════ */
@media (max-width: 640px) {
    .stu-id   { display: none; }
    .stu-time { display: none; }
    .page-heading h3 { font-size: 1.6rem; }
    .sum-pill { min-width: 90px; }
    .filter-row { flex-direction: column; }
}
</style>
</head>
<body>

<!-- ── TOPBAR ── -->
<header class="topbar">
    <div class="topbar-inner">
        <a class="brand" href="#">
            <img src="../img/icas_logo.jpeg" alt="Logo" class="brand-logo">
            <span class="brand-name">Attendance <span>System</span></span>
        </a>
        <a href="dashboard.php" class="btn-nav-back">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</header>

<div class="wrap">

    <!-- ── PAGE HEADING ── -->
    <div class="page-heading">
        <div class="icon-badge"><i class="fa fa-chart-bar"></i></div>
        <div>
            <h3>Attendance Records</h3>
            <p>Browse, filter and review daily attendance logs</p>
        </div>
    </div>

    <!-- ── SUMMARY PILLS ── -->
    <div class="summary-row">
        <div class="sum-pill s-total">
            <div class="pill-icon"><i class="fa fa-users"></i></div>
            <div>
                <div class="pill-num"><?= $grandTotal ?></div>
                <div class="pill-label">Total</div>
            </div>
        </div>
        <div class="sum-pill s-present">
            <div class="pill-icon"><i class="fa fa-circle-check"></i></div>
            <div>
                <div class="pill-num"><?= $totals['Present'] ?></div>
                <div class="pill-label">Present</div>
            </div>
        </div>
        <div class="sum-pill s-late">
            <div class="pill-icon"><i class="fa fa-clock"></i></div>
            <div>
                <div class="pill-num"><?= $totals['Late'] ?></div>
                <div class="pill-label">Late</div>
            </div>
        </div>
        <div class="sum-pill s-absent">
            <div class="pill-icon"><i class="fa fa-circle-xmark"></i></div>
            <div>
                <div class="pill-num"><?= $totals['Absent'] ?></div>
                <div class="pill-label">Absent</div>
            </div>
        </div>
    </div>

    <!-- ── FILTER CARD ── -->
    <div class="filter-card">
        <div class="filter-title"><i class="fa fa-sliders"></i> Filter Records</div>
        <form method="GET" id="filterForm">
            <div class="filter-row">

                <!-- Date -->
                <div class="f-group">
                    <div class="f-label"><i class="fa fa-calendar"></i> Date</div>
                    <input type="date" name="date" class="f-input" value="<?= htmlspecialchars($date) ?>">
                </div>

                <!-- Section -->
                <div class="f-group">
                    <div class="f-label"><i class="fa fa-layer-group"></i> Section</div>
                    <select name="section" class="f-input">
                        <option value="">All Sections</option>
                        <?php foreach($sections_list as $sec): ?>
                            <option value="<?= $sec ?>" <?= ($selected_section == $sec) ? 'selected' : '' ?>>
                                Section <?= $sec ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Student Name Search -->
                <div class="f-group" style="min-width:200px;">
                    <div class="f-label"><i class="fa fa-user"></i> Student Name</div>
                    <div class="f-search-wrap">
                        <i class="fa fa-magnifying-glass search-icon"></i>
                        <input type="text" name="name" id="nameInput" class="f-input"
                               placeholder="Search by name…"
                               value="<?= htmlspecialchars($search_name) ?>"
                               autocomplete="off">
                        <button type="button" class="clear-btn" id="clearName" title="Clear">
                            <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <!-- Buttons -->
                <div style="display:flex;gap:0.5rem;align-items:flex-end;">
                    <button type="submit" class="btn-search">
                        <i class="fa fa-magnifying-glass"></i> Search
                    </button>
                    <a href="view.php" class="btn-reset">
                        <i class="fa fa-rotate-left"></i> Reset
                    </a>
                </div>

            </div>

            <!-- Active filter info -->
            <div class="filter-meta">
                <i class="fa fa-circle-info"></i>
                Showing records for
                <strong><?= date("F j, Y", strtotime($date)) ?></strong>
                &mdash; <strong><?= $current_day ?></strong>
                <?php if($selected_section): ?>
                    &nbsp;<span class="active-tag"><i class="fa fa-layer-group"></i> Section <?= $selected_section ?></span>
                <?php endif; ?>
                <?php if($search_name): ?>
                    &nbsp;<span class="active-tag"><i class="fa fa-user"></i> "<?= htmlspecialchars($search_name) ?>"</span>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ── LIVE SEARCH BAR ── -->
    <?php if($grandTotal > 0): ?>
    <div class="live-search-bar">
        <i class="fa fa-bolt"></i>
        <input type="text" id="liveSearch" class="live-input"
               placeholder="Quick-filter by name across all results…"
               autocomplete="off">
        <span class="live-count" id="liveCount"><?= $grandTotal ?> student<?= $grandTotal != 1 ? 's' : '' ?></span>
    </div>
    <?php endif; ?>

    <!-- ── SECTION BLOCKS ── -->
    <?php
    $blockIdx = 0;
    foreach($sections_list as $section):
        if($selected_section && $selected_section != $section) continue;
        if(!isset($grouped[$section])) continue;
        $blockIdx++;
    ?>
    <div class="section-block" data-section="<?= $section ?>">

        <?php foreach($grouped[$section] as $subject => $students):
            $sp = $sl = $sa = $se = 0;
            foreach($students as $s){
                if($s['status']=='Present')    $sp++;
                elseif($s['status']=='Late')   $sl++;
                elseif($s['status']=='Absent') $sa++;
                else                           $se++;
            }
            $hasData = true;
        ?>
        <div class="subject-block">
        <div class="record-card">

            <div class="rc-head">
                <div class="rc-section-badge">
                    <div class="sec-bubble"><?= $section ?></div>
                    <div>
                        <div class="rc-sec-title"><?= htmlspecialchars($subject) ?></div>
                        <div class="rc-sub-label">
                            Section <?= $section ?> &nbsp;·&nbsp;
                            <span class="student-visible-count"><?= count($students) ?></span>
                            student<?= count($students)!=1?'s':'' ?>
                        </div>
                    </div>
                </div>
                <div class="rc-stats">
                    <?php if($sp): ?><span class="mini-stat ms-p"><i class="fa fa-circle-check"></i> <?= $sp ?> Present</span><?php endif; ?>
                    <?php if($sl): ?><span class="mini-stat ms-l"><i class="fa fa-clock"></i> <?= $sl ?> Late</span><?php endif; ?>
                    <?php if($sa): ?><span class="mini-stat ms-a"><i class="fa fa-circle-xmark"></i> <?= $sa ?> Absent</span><?php endif; ?>
                </div>
            </div>

            <div class="student-list">
                <?php foreach($students as $row):
                    $initials = implode('', array_map(fn($w)=>strtoupper($w[0]), array_slice(explode(' ', trim($row['name'])), 0, 2)));
                    $timeFmt  = date("g:i A", strtotime($row['time']));
                    $sbClass  = match($row['status']){
                        'Present'   => 'sb-present',
                        'Late'      => 'sb-late',
                        'Absent'    => 'sb-absent',
                        default     => 'sb-early'
                    };
                ?>
                <div class="student-row" data-name="<?= strtolower(htmlspecialchars($row['name'])) ?>">
                    <div class="stu-avatar"><?= $initials ?></div>
                    <div class="stu-id"><?= htmlspecialchars($row['student_id']) ?></div>
                    <div class="stu-name"><?= htmlspecialchars($row['name']) ?></div>
                    <div class="stu-time"><?= $timeFmt ?></div>
                    <span class="status-badge <?= $sbClass ?>"><?= $row['status'] ?></span>
                </div>
                <?php endforeach; ?>

                <div class="no-match-row">
                    <i class="fa fa-magnifying-glass"></i>
                    No students match your search in this group.
                </div>
            </div>

        </div>
        </div>
        <?php endforeach; ?>

    </div>
    <?php endforeach; ?>

    <!-- ── EMPTY STATE ── -->
    <?php if(!$hasData): ?>
    <div class="empty-state">
        <i class="fa fa-inbox e-icon"></i>
        <h4>No records found</h4>
        <p>No attendance was recorded for the selected date<?= ($selected_section || $search_name) ? " / filter" : "" ?>.</p>
    </div>
    <?php endif; ?>

</div>

<script>
const nameInput = document.getElementById('nameInput');
const clearBtn  = document.getElementById('clearName');

nameInput.addEventListener('input', () => {
    clearBtn.style.display = nameInput.value ? 'flex' : 'none';
});

clearBtn.addEventListener('click', () => {
    nameInput.value = '';
    clearBtn.style.display = 'none';
    nameInput.focus();
});

const liveInput = document.getElementById('liveSearch');
const liveCount = document.getElementById('liveCount');

if (liveInput) {
    liveInput.addEventListener('input', () => {
        const q = liveInput.value.trim().toLowerCase();
        let visible = 0;

        document.querySelectorAll('.record-card').forEach(card => {
            const rows    = card.querySelectorAll('.student-row');
            const noMatch = card.querySelector('.no-match-row');
            let cardVisible = 0;

            rows.forEach(row => {
                const name   = row.dataset.name;
                const nameEl = row.querySelector('.stu-name');
                const orig   = nameEl.textContent;

                if (!q || name.includes(q)) {
                    row.classList.remove('hidden-row');
                    cardVisible++; visible++;
                    if (q) {
                        const regex = new RegExp(`(${escapeReg(q)})`, 'gi');
                        nameEl.innerHTML = orig.replace(regex, '<mark class="hl">$1</mark>');
                    } else {
                        nameEl.textContent = orig;
                    }
                } else {
                    row.classList.add('hidden-row');
                    nameEl.textContent = orig;
                }
            });

            if (noMatch) noMatch.style.display = (cardVisible === 0 && rows.length > 0) ? 'flex' : 'none';

            const countEl = card.querySelector('.student-visible-count');
            if (countEl) countEl.textContent = cardVisible;
        });

        if (liveCount) {
            liveCount.textContent = visible + ' student' + (visible !== 1 ? 's' : '');
            liveCount.style.color = visible === 0 ? '#FCA5A5' : '';
        }
    });
}

function escapeReg(s) {
    return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
</script>

</body>
</html>