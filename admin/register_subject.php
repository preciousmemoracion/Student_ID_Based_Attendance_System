<?php 
include "../db_connect.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['instructor']) && !isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit();
}

$message = "";
$msg_type = "";

// SAVE SUBJECT
if(isset($_POST['save'])){

    $subject    = trim($_POST['subject']);
    $section    = trim($_POST['section']);
    $day        = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time   = $_POST['end_time'];

    if(empty($subject) || empty($section) || empty($day) || empty($start_time) || empty($end_time)){
        $msg_type = "error"; $message = "All fields are required!";
    }
    elseif(strtotime($start_time) >= strtotime($end_time)){
        $msg_type = "error"; $message = "End time must be after start time!";
    }
    else {
        $stmt = $conn->prepare("SELECT id FROM subjects WHERE subject=? AND section=? AND day=?");
        $stmt->bind_param("sss", $subject, $section, $day);
        $stmt->execute();

        if($stmt->get_result()->num_rows > 0){
            $msg_type = "error"; $message = "Subject already exists for this section and day!";
        } else {
            $stmt = $conn->prepare("INSERT INTO subjects (subject, section, day, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $subject, $section, $day, $start_time, $end_time);
            $stmt->execute();
            $msg_type = "success"; $message = "Subject successfully added!";
        }
    }
}

// DELETE SUBJECT
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM subjects WHERE id=$id");
    header("Location: register_subject.php");
    exit();
}

// FETCH SUBJECTS
$subjects = $conn->query("SELECT * FROM subjects ORDER BY section, start_time ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Register Subject</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
:root {
    --blue:    #2563EB;
    --blue-lt: #3B82F6;
    --green:   #059669;
    --border:  rgba(255,255,255,0.14);
    --muted:   rgba(255,255,255,0.88);
    --radius:  18px;
}

* { box-sizing: border-box; }

body {
    min-height: 100vh;
    margin: 0;
    background: url('../img/icas.jpeg') no-repeat center center / cover fixed;
    font-family: 'DM Sans', sans-serif;
    color: #fff;
}

body::before {
    content: '';
    position: fixed; inset: 0;
    background: linear-gradient(135deg,
        rgba(3,10,35,0.45) 0%,
        rgba(10,30,80,0.38) 60%,
        rgba(3,10,35,0.48) 100%);
    z-index: 0;
}

body > * { position: relative; z-index: 1; }

/* ════════════════════════════════
   TOPBAR  — matches subjects.php
════════════════════════════════ */
.topbar {
    position: sticky; top: 0; z-index: 100;
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

.brand-name { font-size: 1rem; font-weight: 800; color: #fff; letter-spacing: -0.2px; }
.brand-name span { color: #60A5FA; }

.btn-back {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,0.09);
    border: 1px solid var(--border); color: rgba(255,255,255,0.88);
    border-radius: 12px; padding: 0.38rem 1rem;
    font-family: 'Outfit', sans-serif;
    font-size: 0.82rem; font-weight: 700;
    text-decoration: none;
    transition: background 0.2s, transform 0.2s;
}
.btn-back:hover { background: rgba(255,255,255,0.14); transform: translateX(-3px); color: #fff; }

/* ── MAIN WRAP ── */
.main-wrap {
    max-width: 720px;
    margin: 0 auto;
    padding: 2rem 1.25rem 3rem;
}

/* ── PAGE HEADING ── */
.page-heading {
    display: flex; align-items: center; gap: 14px;
    margin-bottom: 1.8rem;
    animation: fadeUp 0.5s ease both;
}

.page-heading .icon-badge {
    width: 48px; height: 48px; border-radius: 14px;
    background: linear-gradient(135deg, var(--blue), var(--blue-lt));
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    box-shadow: 0 4px 18px rgba(37,99,235,0.45);
    flex-shrink: 0;
}

.page-heading h3 {
    font-family: 'Syne', sans-serif;
    font-size: 1.9rem; font-weight: 800; margin: 0;
    color: #fff; text-shadow: 0 2px 12px rgba(0,0,0,0.6);
}

.page-heading p {
    margin: 2px 0 0; font-size: 0.88rem; font-weight: 500;
    color: rgba(255,255,255,0.6); text-shadow: 0 1px 6px rgba(0,0,0,0.5);
}

/* ── GLASS PANEL ── */
.glass-panel {
    background: rgba(8,20,60,0.40);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    box-shadow: 0 12px 40px rgba(0,0,0,0.35);
    overflow: hidden;
    animation: fadeUp 0.55s ease both 0.1s;
}

/* panel header stripe */
.panel-header {
    background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
    padding: 1.25rem 1.75rem;
    display: flex; align-items: center; gap: 12px;
    position: relative; overflow: hidden;
}

.panel-header::after {
    content: '';
    position: absolute; top: -40%; right: -8%;
    width: 130px; height: 130px; border-radius: 50%;
    background: rgba(255,255,255,0.07);
    pointer-events: none;
}

.panel-header .ph-icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: rgba(255,255,255,0.18);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}

.panel-header h5 {
    font-family: 'Syne', sans-serif;
    font-weight: 700; font-size: 1rem; margin: 0;
    color: #fff;
}

.panel-header p {
    font-size: 0.78rem; color: rgba(255,255,255,0.72);
    margin: 0; font-weight: 500;
}

.panel-body { padding: 1.75rem; }

/* ── ALERT ── */
.custom-alert {
    border-radius: 10px; padding: 0.75rem 1rem;
    font-size: 0.875rem; font-weight: 600;
    margin-bottom: 1.25rem;
    display: flex; align-items: center; gap: 8px;
}
.custom-alert.error {
    background: rgba(185,28,28,0.25);
    border: 1px solid rgba(248,113,113,0.4);
    color: #FCA5A5;
}
.custom-alert.success {
    background: rgba(5,150,105,0.22);
    border: 1px solid rgba(52,211,153,0.4);
    color: #6EE7B7;
}

/* ── FORM GRID ── */
.form-row-2 {
    display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;
}

/* ── FIELD GROUPS ── */
.field-group { margin-bottom: 1.1rem; }

.field-label {
    font-size: 0.75rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 1px;
    color: rgba(255,255,255,0.7); margin-bottom: 0.4rem;
    display: block;
}

.input-wrap { position: relative; }

.input-wrap .input-icon {
    position: absolute; left: 11px; top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.4); font-size: 0.85rem;
    pointer-events: none;
}

.field-input {
    width: 100%;
    background: rgba(255,255,255,0.08);
    border: 1px solid var(--border);
    border-radius: 10px; color: #fff;
    padding: 0.6rem 0.9rem 0.6rem 2.5rem;
    font-size: 0.9rem; font-family: 'DM Sans', sans-serif; font-weight: 500;
    transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
}

.field-input.no-icon { padding-left: 0.9rem; }

.field-input::placeholder { color: rgba(255,255,255,0.35); }
.field-input:focus {
    outline: none; border-color: var(--blue-lt);
    background: rgba(255,255,255,0.13);
    box-shadow: 0 0 0 3px rgba(59,130,246,0.22);
}
.field-input[type="time"] { color-scheme: dark; }
.field-input option { background: #1e3a6e; color: #fff; }

/* ── DIVIDER ── */
.field-divider {
    border: none; height: 1px;
    background: var(--border); margin: 1.4rem 0;
}

/* ── BUTTONS ── */
.btn-row { display: flex; gap: 0.75rem; }

.btn-save {
    flex: 1;
    background: linear-gradient(135deg, #059669, #34d399);
    color: #fff; border: none; border-radius: 11px;
    padding: 0.7rem 1rem;
    font-family: 'Syne', sans-serif; font-size: 0.92rem; font-weight: 700;
    cursor: pointer; box-shadow: 0 4px 16px rgba(5,150,105,0.4);
    transition: transform 0.18s ease, filter 0.18s ease;
    display: flex; align-items: center; justify-content: center; gap: 7px;
}

.btn-save:hover  { transform: translateY(-2px); filter: brightness(1.1); }
.btn-save:active { transform: translateY(0); }

/* ── ANIMATIONS ── */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(28px); }
    to   { opacity: 1; transform: translateY(0); }
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
        <a href="dashboard.php" class="btn-back">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</header>

<div class="main-wrap">

    <!-- Page Heading -->
    <div class="page-heading">
        <div class="icon-badge"><i class="fa fa-book"></i></div>
        <div>
            <h3>Register Subject</h3>
            <p>Add a new subject with schedule and section</p>
        </div>
    </div>

    <!-- Form Panel -->
    <div class="glass-panel">

        <div class="panel-header">
            <div class="ph-icon"><i class="fa fa-plus"></i></div>
            <div>
                <h5>New Subject Entry</h5>
                <p>All fields are required</p>
            </div>
        </div>

        <div class="panel-body">

            <?php if($message != ""): ?>
            <div class="custom-alert <?= $msg_type ?>">
                <i class="fa fa-<?= $msg_type === 'success' ? 'circle-check' : 'circle-exclamation' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <form method="POST">

                <!-- Subject Name -->
                <div class="field-group">
                    <label class="field-label">Subject Name</label>
                    <div class="input-wrap">
                        <i class="fa fa-book-open input-icon"></i>
                        <input type="text" name="subject" class="field-input"
                               placeholder="e.g. Mathematics, Filipino, Science"
                               value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>"
                               required>
                    </div>
                </div>

                <!-- Section & Day -->
                <div class="form-row-2">
                    <div class="field-group">
                        <label class="field-label">Section</label>
                        <div class="input-wrap">
                            <i class="fa fa-layer-group input-icon"></i>
                            <select name="section" class="field-input" required>
                                <option value="">Select Section</option>
                                <?php
                                $sections = ['1A','1B','1C','2A','2B','2C','3A','3B','3C','4A','4B','4C'];
                                $sel_sec = isset($_POST['section']) ? $_POST['section'] : '';
                                foreach($sections as $s){
                                    echo "<option value='$s'" . ($sel_sec===$s ? ' selected' : '') . ">$s</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Day</label>
                        <div class="input-wrap">
                            <i class="fa fa-calendar-days input-icon"></i>
                            <select name="day" class="field-input" required>
                                <option value="">Select Day</option>
                                <?php
                                $days = ['Monday/Wednesday/Friday','Tuesday/Thursday'];
                                $sel_day = isset($_POST['day']) ? $_POST['day'] : '';
                                foreach($days as $d){
                                    echo "<option value='$d'" . ($sel_day===$d ? ' selected' : '') . ">$d</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Time -->
                <div class="form-row-2">
                    <div class="field-group">
                        <label class="field-label">Start Time</label>
                        <input type="time" name="start_time" class="field-input no-icon"
                               value="<?= isset($_POST['start_time']) ? $_POST['start_time'] : '' ?>"
                               required>
                    </div>
                    <div class="field-group">
                        <label class="field-label">End Time</label>
                        <input type="time" name="end_time" class="field-input no-icon"
                               value="<?= isset($_POST['end_time']) ? $_POST['end_time'] : '' ?>"
                               required>
                    </div>
                </div>

                <hr class="field-divider">

                <div class="btn-row">
                    <button type="submit" name="save" class="btn-save">
                        <i class="fa fa-save"></i> Save Subject
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>