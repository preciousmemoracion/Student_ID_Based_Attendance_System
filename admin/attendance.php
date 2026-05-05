<?php 
include "../db_connect.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['admin'])){
    header("Location: ../index.php");
    exit();
}

date_default_timezone_set("Asia/Manila");

$message = "";
$msg_type = "";
$today = date("Y-m-d");
$current_time = time();
$current_day = date("l");

/* ======================================================
   GET SECTIONS
====================================================== */
$section_result = $conn->query("
    SELECT DISTINCT section 
    FROM subjects 
    ORDER BY section ASC
");

/* ======================================================
   GET SUBJECTS
====================================================== */
$subject_result = $conn->query("
    SELECT DISTINCT subject 
    FROM subjects 
    ORDER BY subject ASC
");

/* ======================================================
   HANDLE ATTENDANCE
====================================================== */
if(isset($_POST['record'])){

    $student_id = $_POST['student_id'];
    $section    = $_POST['section'];
    $subject    = $_POST['subject'];

    $checkStudent = $conn->prepare("
        SELECT id FROM students 
        WHERE student_id = ? AND section = ?
        LIMIT 1
    ");
    $checkStudent->bind_param("ss", $student_id, $section);
    $checkStudent->execute();
    $studentExists = $checkStudent->get_result();

    if($studentExists->num_rows == 0){
        $message  = "Student ID not found in the selected section.";
        $msg_type = "error";
    } else {
        $stmt = $conn->prepare("
            SELECT subject, start_time, end_time, day
            FROM subjects 
            WHERE section = ? AND subject = ?
            LIMIT 1
        ");
        $stmt->bind_param("ss", $section, $subject);
        $stmt->execute();
        $sub = $stmt->get_result()->fetch_assoc();

        if($sub){
            if(strpos($sub['day'], $current_day) !== false){
                $class_start = strtotime($sub['start_time']);
                $grace_time  = strtotime("+15 minutes", $class_start);
                $class_end   = strtotime($sub['end_time']);
                $time_now    = time();

                if($time_now < $class_start){
                    $status = "Too Early";
                } elseif($time_now <= $grace_time){
                    $status = "Present";
                } elseif($time_now <= $class_end){
                    $status = "Late";
                } else {
                    $status = "Absent";
                }

                $check = $conn->prepare("
                    SELECT id FROM attendance 
                    WHERE student_id=? AND date=? AND subject=?
                ");
                $check->bind_param("sss", $student_id, $today, $subject);
                $check->execute();

                if($check->get_result()->num_rows > 0){
                    $message  = "Attendance already recorded for <strong>$subject</strong> today.";
                    $msg_type = "warning";
                } else {
                    $insert = $conn->prepare("
                        INSERT INTO attendance 
                        (student_id, date, time, status, section, subject)
                        VALUES (?, ?, CURTIME(), ?, ?, ?)
                    ");
                    $insert->bind_param("sssss", $student_id, $today, $status, $section, $subject);
                    $insert->execute();

                    $message  = "Recorded: <strong>$subject</strong> &mdash; Section $section &mdash; <strong>$status</strong>";
                    $msg_type = "success";
                }
            } else {
                $message  = "No class scheduled for <strong>$subject</strong> today ($current_day).";
                $msg_type = "info";
            }
        } else {
            $message  = "Subject not found for the selected section.";
            $msg_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Record Attendance</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
/* ════════════════════════════════
   ROOT
════════════════════════════════ */
:root {
    --blue:      #2563EB;
    --blue-lt:   #3B82F6;
    --blue-glow: rgba(37,99,235,0.45);
    --green:     #059669;
    --green-lt:  #10B981;
    --amber:     #D97706;
    --red:       #DC2626;
    --cyan:      #0891B2;
    --border:    rgba(255,255,255,0.10);
    --border2:   rgba(255,255,255,0.12);
    --muted:     rgba(255,255,255,0.48);
    --surface:   rgba(8,18,55,0.58);
    --glass:     rgba(255,255,255,0.055);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    min-height: 100vh;
    font-family: 'Sora', sans-serif;
    background: url('../img/icas.jpeg') no-repeat center center / cover fixed;
    display: flex;
    flex-direction: column;
    align-items: center;
    overflow-x: hidden;
}

body::before {
    content: '';
    position: fixed; inset: 0;
    background:
        radial-gradient(ellipse 90% 70% at 20% 0%,   rgba(29,78,216,0.30) 0%, transparent 55%),
        radial-gradient(ellipse 70% 60% at 80% 100%,  rgba(109,40,217,0.22) 0%, transparent 55%),
        linear-gradient(165deg, rgba(3,8,30,0.76) 0%, rgba(8,18,58,0.70) 50%, rgba(3,8,30,0.80) 100%);
    z-index: 0;
}

body > * { position: relative; z-index: 1; }

/* ════════════════════════════════
   TOPBAR  (matches subjects.php)
════════════════════════════════ */
.topbar {
    position: sticky; top: 0; z-index: 100;
    width: 100%;
    background: rgba(5,12,40,0.75);
    backdrop-filter: blur(22px) saturate(180%);
    -webkit-backdrop-filter: blur(22px) saturate(180%);
    border-bottom: 1px solid var(--border);
    padding: 0.6rem 0;
}

.topbar-inner {
    max-width: 1280px; margin: 0 auto;
    padding: 0 1.5rem;
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem;
}

.brand { display: flex; align-items: center; gap: 11px; text-decoration: none; }

.brand-logo {
    width: 40px; height: 40px; border-radius: 12px; object-fit: cover;
    border: 1.5px solid rgba(59,130,246,0.45);
    box-shadow: 0 0 0 3px rgba(59,130,246,0.14), 0 4px 14px rgba(0,0,0,0.4);
}

.brand-name { font-size: 1rem; font-weight: 800; color: #fff; letter-spacing: -0.2px; font-family: 'Outfit', sans-serif; }
.brand-name span { color: #60A5FA; }

.online-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--green-lt);
    box-shadow: 0 0 0 2px rgba(16,185,129,0.25);
    animation: breathe 2.4s ease-in-out infinite;
}

@keyframes breathe {
    0%,100% { opacity:1; transform:scale(1); }
    50%      { opacity:0.5; transform:scale(1.45); }
}

.btn-back {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,0.09);
    border: 1px solid var(--border2); color: rgba(255,255,255,0.88);
    border-radius: 12px; padding: 0.38rem 1rem;
    font-family: 'Outfit', sans-serif;
    font-size: 0.82rem; font-weight: 700;
    text-decoration: none;
    transition: background 0.2s, transform 0.2s;
}

.btn-back:hover { background: rgba(255,255,255,0.14); transform: translateX(-3px); color: #fff; }

/* ════════════════════════════════
   PAGE CONTENT WRAPPER
════════════════════════════════ */
.page-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 1.5rem 3rem;
    width: 100%;
}

/* ════════════════════════════════
   CARD
════════════════════════════════ */
.panel {
    width: 100%; max-width: 460px;
    background: var(--surface);
    backdrop-filter: blur(24px) saturate(180%);
    -webkit-backdrop-filter: blur(24px) saturate(180%);
    border: 1px solid var(--border2);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(0,0,0,0.55), 0 0 0 1px rgba(255,255,255,0.04);
    animation: slideUp 0.65s cubic-bezier(0.22,1,0.36,1) both;
}

.panel::before {
    content: '';
    display: block; height: 1px;
    background: linear-gradient(to right,
        transparent 5%,
        rgba(255,255,255,0.28) 35%,
        rgba(59,130,246,0.55) 55%,
        rgba(255,255,255,0.18) 75%,
        transparent 95%
    );
}

/* ════════════════════════════════
   PANEL HEADER
════════════════════════════════ */
.panel-head {
    padding: 1.6rem 1.8rem 1.4rem;
    display: flex; align-items: center; gap: 14px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    background: linear-gradient(135deg, rgba(29,78,216,0.22) 0%, rgba(8,18,55,0.10) 100%);
    position: relative; overflow: hidden;
}

.panel-head::after {
    content: '';
    position: absolute; top: -40px; right: -30px;
    width: 140px; height: 140px;
    background: radial-gradient(circle, rgba(59,130,246,0.30) 0%, transparent 70%);
    pointer-events: none;
}

.head-icon {
    width: 52px; height: 52px; border-radius: 16px; flex-shrink: 0;
    background: linear-gradient(135deg, #1D4ED8, #3B82F6);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; color: #fff;
    box-shadow: 0 6px 22px rgba(29,78,216,0.5), inset 0 1px 0 rgba(255,255,255,0.18);
    position: relative; z-index: 1;
}

.head-text { position: relative; z-index: 1; }

.head-title {
    font-size: 1.25rem; font-weight: 800; color: #fff;
    letter-spacing: -0.3px; line-height: 1.2;
}

.head-sub {
    font-size: 0.78rem; font-weight: 400; color: var(--muted);
    margin-top: 3px; letter-spacing: 0.2px;
}

.datetime-pill {
    margin-left: auto; flex-shrink: 0;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 10px; padding: 0.35rem 0.75rem;
    text-align: right; position: relative; z-index: 1;
}

.datetime-pill .d-day {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.7rem; font-weight: 600; color: #60A5FA;
    display: block; letter-spacing: 0.5px;
}

.datetime-pill .d-date {
    font-size: 0.68rem; font-weight: 500; color: var(--muted);
    display: block; margin-top: 1px;
}

/* ════════════════════════════════
   PANEL BODY
════════════════════════════════ */
.panel-body { padding: 1.6rem 1.8rem 1.8rem; }

/* ════════════════════════════════
   TOAST MESSAGE
════════════════════════════════ */
.toast-msg {
    border-radius: 12px; padding: 0.75rem 1rem;
    font-size: 0.84rem; font-weight: 500;
    margin-bottom: 1.4rem; display: flex;
    align-items: flex-start; gap: 10px;
    border: 1px solid;
    animation: fadeIn 0.4s ease both;
}

.toast-msg .t-icon {
    width: 26px; height: 26px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.78rem; flex-shrink: 0; margin-top: 1px;
}

.toast-msg .t-text { color: #fff; line-height: 1.45; }

.toast-success { background: rgba(5,150,105,0.18); border-color: rgba(16,185,129,0.38); }
.toast-success .t-icon { background: rgba(16,185,129,0.25); color: #34D399; }

.toast-warning { background: rgba(217,119,6,0.18); border-color: rgba(251,191,36,0.38); }
.toast-warning .t-icon { background: rgba(251,191,36,0.2); color: #FCD34D; }

.toast-error { background: rgba(220,38,38,0.18); border-color: rgba(248,113,113,0.38); }
.toast-error .t-icon { background: rgba(239,68,68,0.2); color: #FCA5A5; }

.toast-info { background: rgba(8,145,178,0.18); border-color: rgba(34,211,238,0.38); }
.toast-info .t-icon { background: rgba(34,211,238,0.2); color: #67E8F9; }

/* ════════════════════════════════
   FORM FIELDS
════════════════════════════════ */
.field-group { margin-bottom: 1.1rem; }

.field-label {
    display: flex; align-items: center; gap: 6px;
    font-size: 0.76rem; font-weight: 700;
    color: rgba(255,255,255,0.65);
    text-transform: uppercase; letter-spacing: 0.9px;
    margin-bottom: 0.45rem;
}

.field-label i { font-size: 0.7rem; color: #60A5FA; }

.field-wrap { position: relative; }

.field-wrap .f-icon {
    position: absolute; left: 13px; top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.30); font-size: 0.82rem;
    pointer-events: none; transition: color 0.2s;
}

.field-input {
    width: 100%;
    background: rgba(5,12,40,0.65);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 12px; color: #fff;
    padding: 0.7rem 1rem 0.7rem 2.55rem;
    font-size: 0.88rem; font-family: 'Sora', sans-serif; font-weight: 500;
    transition: border-color 0.22s, background 0.22s, box-shadow 0.22s;
    -webkit-appearance: none; appearance: none;
}

select.field-input {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='rgba(255,255,255,0.4)' d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 12px;
    padding-right: 2.2rem;
}

select.field-input option { background: #0d1a4a; color: #fff; }

.field-input::placeholder { color: rgba(255,255,255,0.25); }

.field-input:focus {
    outline: none;
    border-color: rgba(59,130,246,0.65);
    background: rgba(255,255,255,0.07);
    box-shadow: 0 0 0 4px rgba(37,99,235,0.14);
}

.field-wrap:focus-within .f-icon { color: #60A5FA; }

input[name="student_id"].field-input {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.92rem; letter-spacing: 0.5px;
}

/* ════════════════════════════════
   DIVIDER
════════════════════════════════ */
.divider { height: 1px; background: rgba(255,255,255,0.07); margin: 1.4rem 0; }

/* ════════════════════════════════
   SUBMIT BUTTON
════════════════════════════════ */
.btn-submit {
    width: 100%;
    background: linear-gradient(135deg, #1D4ED8 0%, #3B82F6 100%);
    color: #fff; border: none; border-radius: 13px;
    padding: 0.82rem 1rem;
    font-family: 'Sora', sans-serif;
    font-size: 0.92rem; font-weight: 700;
    letter-spacing: 0.2px; cursor: pointer;
    box-shadow: 0 6px 22px rgba(29,78,216,0.42), inset 0 1px 0 rgba(255,255,255,0.15);
    transition: transform 0.2s ease, filter 0.2s ease, box-shadow 0.2s ease;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    position: relative; overflow: hidden;
}

.btn-submit::before {
    content: '';
    position: absolute; top: 0; left: -100%;
    width: 60%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent);
    transition: left 0.55s ease;
}

.btn-submit:hover { transform: translateY(-2px); filter: brightness(1.1); box-shadow: 0 10px 28px rgba(29,78,216,0.52); }
.btn-submit:hover::before { left: 150%; }
.btn-submit:active { transform: translateY(0); filter: brightness(0.96); }

/* ════════════════════════════════
   FOOTER NOTE
════════════════════════════════ */
.foot-note {
    margin-top: 1.6rem; text-align: center;
    font-size: 0.72rem; color: rgba(255,255,255,0.22);
    letter-spacing: 0.3px;
}

/* ════════════════════════════════
   ANIMATIONS
════════════════════════════════ */
@keyframes slideUp {
    from { opacity: 0; transform: translateY(36px); }
    to   { opacity: 1; transform: translateY(0); }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.field-group:nth-child(1) { animation: fadeIn 0.45s ease 0.20s both; }
.field-group:nth-child(2) { animation: fadeIn 0.45s ease 0.28s both; }
.field-group:nth-child(3) { animation: fadeIn 0.45s ease 0.36s both; }
.btn-submit               { animation: fadeIn 0.45s ease 0.44s both; }
</style>
</head>

<body>

<!-- ══ TOPBAR (matches subjects.php) ══ -->
<header class="topbar">
    <div class="topbar-inner">
        <a class="brand" href="#">
            <img src="../img/icas_logo.jpeg" alt="Logo" class="brand-logo">
            <span class="brand-name">Attendance <span>System</span></span>
        </a>

        <a href="dashboard.php" class="btn-back">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</header>

<!-- ══ PAGE CONTENT ══ -->
<div class="page-content">

    <div class="panel">

        <!-- HEADER -->
        <div class="panel-head">
            <div class="head-icon"><i class="fa fa-clipboard-user"></i></div>
            <div class="head-text">
                <div class="head-title">Record Attendance</div>
                <div class="head-sub">Submit student attendance for today</div>
            </div>
            <div class="datetime-pill">
                <span class="d-day"><?= strtoupper(date("l")) ?></span>
                <span class="d-date"><?= date("M d, Y") ?></span>
            </div>
        </div>

        <!-- BODY -->
        <div class="panel-body">

            <!-- TOAST -->
            <?php if($message):
                $icons = [
                    'success' => 'fa-circle-check',
                    'warning' => 'fa-triangle-exclamation',
                    'error'   => 'fa-circle-xmark',
                    'info'    => 'fa-circle-info',
                ];
                $icon = $icons[$msg_type] ?? 'fa-circle-info';
            ?>
            <div class="toast-msg toast-<?= $msg_type ?>">
                <div class="t-icon"><i class="fa <?= $icon ?>"></i></div>
                <div class="t-text"><?= $message ?></div>
            </div>
            <?php endif; ?>

            <!-- FORM -->
            <form method="POST">

                <!-- Student ID -->
                <div class="field-group">
                    <div class="field-label">
                        <i class="fa fa-id-card"></i> Student ID
                    </div>
                    <div class="field-wrap">
                        <i class="fa fa-hashtag f-icon"></i>
                        <input type="text" name="student_id"
                               class="field-input"
                               placeholder="e.g. 2024-00123"
                               required autocomplete="off">
                    </div>
                </div>

                <!-- Section -->
                <div class="field-group">
                    <div class="field-label">
                        <i class="fa fa-layer-group"></i> Section
                    </div>
                    <div class="field-wrap">
                        <i class="fa fa-users f-icon"></i>
                        <select name="section" class="field-input" required>
                            <option value="">Select a section…</option>
                            <?php while($sec = $section_result->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($sec['section']) ?>">
                                    <?= htmlspecialchars($sec['section']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <!-- Subject -->
                <div class="field-group">
                    <div class="field-label">
                        <i class="fa fa-book"></i> Subject
                    </div>
                    <div class="field-wrap">
                        <i class="fa fa-book-open f-icon"></i>
                        <select name="subject" class="field-input" required>
                            <option value="">Select a subject…</option>
                            <?php while($subj = $subject_result->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($subj['subject']) ?>">
                                    <?= htmlspecialchars($subj['subject']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="divider"></div>

                <button type="submit" name="record" class="btn-submit">
                    <i class="fa fa-circle-check"></i>
                    Submit Attendance
                </button>

            </form>

        </div>
    </div>

    <div class="foot-note">
        <?= htmlspecialchars($_SESSION['admin']) ?> &nbsp;·&nbsp; Attendance System
    </div>

</div>

</body>
</html>