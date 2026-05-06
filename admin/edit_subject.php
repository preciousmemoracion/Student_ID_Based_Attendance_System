<?php
include "../db_connect.php";

if(!isset($_SESSION['admin'])){
    header("Location: ../index.php");
    exit();
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: subjects.php");
    exit();
}

$subject_id = (int) $_GET['id'];
$alert_type = "";
$alert_msg  = "";

// ── FETCH SUBJECT ──
$stmt = $conn->prepare("SELECT id, subject, section, day, start_time, end_time FROM subjects WHERE id = ?");
$stmt->bind_param("i", $subject_id);
$stmt->execute();
$stmt->bind_result($db_id, $db_subject, $db_section, $db_day, $db_start_time, $db_end_time);

if($stmt->fetch()){
    $subject = [
        'id'         => $db_id,
        'subject'    => $db_subject,
        'section'    => $db_section,
        'day'        => $db_day,
        'start_time' => $db_start_time,
        'end_time'   => $db_end_time,
    ];
} else {
    $subject = null;
}
$stmt->close();

if(!$subject){
    $alert_type = "danger";
    $alert_msg  = "Subject not found!";
}

// ── HANDLE UPDATE ──
if(isset($_POST['update']) && $subject){

    $subject_name = trim($_POST['subject']    ?? '');
    $section      = trim($_POST['section']    ?? '');
    $day          = trim($_POST['day']        ?? '');
    $start_time   = trim($_POST['start_time'] ?? '');
    $end_time     = trim($_POST['end_time']   ?? '');

    if(empty($subject_name) || empty($section)){
        $alert_type = "danger";
        $alert_msg  = "Subject name and section are required!";
    } else {
        $upd = $conn->prepare("UPDATE subjects SET subject=?, section=?, day=?, start_time=?, end_time=? WHERE id=?");
        $upd->bind_param("sssssi", $subject_name, $section, $day, $start_time, $end_time, $subject_id);

        if($upd->execute()){
            $alert_type = "success";
            $alert_msg  = "Subject updated successfully!";
            // Update local copy so form reflects saved values
            $subject['subject']    = $subject_name;
            $subject['section']    = $section;
            $subject['day']        = $day;
            $subject['start_time'] = $start_time;
            $subject['end_time']   = $end_time;
        } else {
            $alert_type = "danger";
            $alert_msg  = "Database error: " . $conn->error;
        }
        $upd->close();
    }
}

$sections_list = [
    "1A","1B","1C",
    "2A","2B","2C",
    "3A","3B","3C",
    "4A","4B","4C"
];

$days_list = [
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
    "Monday/Wednesday/Friday",
    "Tuesday/Thursday",
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
    :root {
        --blue:     #2563EB;
        --blue-lt:  #3B82F6;
        --border:   rgba(255,255,255,0.10);
        --border2:  rgba(255,255,255,0.06);
        --muted:    rgba(255,255,255,0.88);
        --radius:   18px;
        --c-green:    #10B981;
        --c-green-bg: rgba(16,185,129,0.14);
        --c-green-br: rgba(16,185,129,0.35);
        --c-red:      #EF4444;
        --c-red-bg:   rgba(239,68,68,0.14);
        --c-red-br:   rgba(239,68,68,0.35);
        --c-sky:      #38BDF8;
        --c-sky-bg:   rgba(56,189,248,0.14);
        --c-sky-br:   rgba(56,189,248,0.35);
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

    /* ── TOPBAR ── */
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

    /* ── WRAP ── */
    .wrap {
        max-width: 620px; margin: 0 auto;
        padding: 2.5rem 1.5rem 5rem;
    }

    /* ── PAGE HEADING ── */
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

    /* ── ALERT ── */
    .alert-bar {
        display: flex; align-items: center; gap: 10px;
        padding: 0.85rem 1.2rem; border-radius: 12px; border: 1px solid;
        font-size: 0.87rem; font-weight: 500; margin-bottom: 1.5rem;
        animation: fadeUp 0.4s ease both;
    }
    .alert-bar i { font-size: 0.9rem; flex-shrink: 0; }
    .alert-bar.success { background: var(--c-green-bg); border-color: var(--c-green-br); color: #34D399; }
    .alert-bar.danger  { background: var(--c-red-bg);   border-color: var(--c-red-br);   color: #FCA5A5; }

    /* ── FORM CARD ── */
    .form-card {
        background: rgba(8,20,60,0.40);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0,0,0,0.25);
        animation: fadeUp 0.5s ease 0.07s both;
    }

    .fc-head {
        padding: 1.2rem 1.5rem;
        display: flex; align-items: center; gap: 14px;
        background: rgba(29,78,216,0.22);
        border-bottom: 1px solid var(--border2);
        position: relative; overflow: hidden;
    }
    .fc-head::after {
        content: '';
        position: absolute; top: -50%; right: -20px;
        width: 140px; height: 140px;
        background: radial-gradient(circle, rgba(59,130,246,0.18) 0%, transparent 70%);
        pointer-events: none;
    }

    .fc-head-icon {
        width: 46px; height: 46px; border-radius: 14px; flex-shrink: 0;
        background: linear-gradient(135deg, #1D4ED8, #3B82F6);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; color: #fff;
        box-shadow: 0 4px 14px rgba(29,78,216,0.4);
    }

    .fc-head-text h4 {
        font-family: 'Syne', sans-serif;
        font-size: 1.05rem; font-weight: 800; color: #fff;
        margin: 0; letter-spacing: -0.2px;
    }
    .fc-head-text p {
        margin: 3px 0 0; font-size: 0.78rem; font-weight: 500;
        color: rgba(255,255,255,0.50);
    }

    .fc-body { padding: 1.5rem; }

    .id-badge {
        display: flex; align-items: center; gap: 12px;
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--border);
        border-radius: 12px; padding: 0.9rem 1.1rem;
        margin-bottom: 1.4rem;
    }
    .id-badge-icon {
        width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
        background: var(--c-sky-bg); border: 1px solid var(--c-sky-br);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.85rem; color: var(--c-sky);
    }
    .id-badge-label {
        font-size: 0.7rem; font-weight: 700; color: rgba(255,255,255,0.40);
        text-transform: uppercase; letter-spacing: 0.9px;
    }
    .id-badge-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.95rem; font-weight: 600; color: #93C5FD; margin-top: 2px;
    }

    .fc-section-label {
        font-size: 0.7rem; font-weight: 700; color: rgba(255,255,255,0.35);
        text-transform: uppercase; letter-spacing: 1.1px;
        display: flex; align-items: center; gap: 6px;
        margin-bottom: 0.85rem; padding-bottom: 0.6rem;
        border-bottom: 1px solid var(--border2);
    }
    .fc-section-label i { color: #60A5FA; font-size: 0.65rem; }

    .f-group { margin-bottom: 1.2rem; }

    .f-label {
        display: flex; align-items: center; gap: 6px;
        font-size: 0.72rem; font-weight: 700; color: rgba(255,255,255,0.55);
        text-transform: uppercase; letter-spacing: 0.9px;
        margin-bottom: 0.45rem;
    }
    .f-label i { font-size: 0.65rem; color: #60A5FA; }

    .f-wrap {
        display: flex; align-items: center;
        background: rgba(255,255,255,0.08);
        border: 1px solid var(--border); border-radius: 10px;
        overflow: hidden;
        transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
    }
    .f-wrap:focus-within {
        border-color: var(--blue-lt);
        background: rgba(255,255,255,0.13);
        box-shadow: 0 0 0 3px rgba(59,130,246,0.18);
    }

    .f-icon {
        width: 44px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        border-right: 1px solid var(--border2);
        color: rgba(255,255,255,0.35); font-size: 0.82rem;
        align-self: stretch;
    }

    .f-input, .f-select {
        flex: 1; background: transparent; border: none; outline: none;
        color: #fff; font-family: 'DM Sans', sans-serif;
        font-size: 0.9rem; font-weight: 500; padding: 0.7rem 1rem;
    }
    .f-input::placeholder { color: rgba(255,255,255,0.25); }

    /* Style for time inputs */
    .f-input[type="time"] {
        color-scheme: dark;
    }

    .f-select {
        appearance: none; -webkit-appearance: none; cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='rgba(255,255,255,0.4)' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 1rem center;
        padding-right: 2.5rem;
    }
    .f-select option { background: #0a1a4a; color: #fff; }

    .f-row { display: flex; gap: 1rem; }
    .f-row .f-group { flex: 1; }

    .fc-divider { height: 1px; background: var(--border2); margin: 1.5rem 0; }

    .btn-save {
        width: 100%;
        background: linear-gradient(135deg, #1D4ED8, #3B82F6);
        color: #fff; border: none; border-radius: 10px;
        padding: 0.82rem 1.25rem;
        font-family: 'Outfit', sans-serif;
        font-size: 0.9rem; font-weight: 700;
        cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
        box-shadow: 0 4px 18px rgba(29,78,216,0.35);
        transition: transform 0.18s ease, filter 0.18s ease;
        letter-spacing: 0.1px;
        text-decoration: none;
    }
    .btn-save:hover { transform: translateY(-2px); filter: brightness(1.1); }
    .btn-save:active { transform: scale(0.98); }

    .btn-cancel {
        width: 100%; margin-top: 0.65rem;
        background: rgba(255,255,255,0.07); border: 1px solid var(--border);
        color: rgba(255,255,255,0.65); border-radius: 10px;
        padding: 0.75rem 1.25rem;
        font-family: 'Outfit', sans-serif;
        font-size: 0.87rem; font-weight: 700;
        cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
        transition: background 0.18s ease, color 0.18s ease;
        text-decoration: none;
    }
    .btn-cancel:hover { background: rgba(255,255,255,0.12); color: #fff; }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(26px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Responsive ── */
    @media (max-width: 500px) {
        .f-row { flex-direction: column; gap: 0; }
        .page-heading h3 { font-size: 1.5rem; }
    }
    </style>
</head>
<body>

<!-- TOPBAR -->
<header class="topbar">
    <div class="topbar-inner">
        <a class="brand" href="#">
            <img src="../img/icas_logo.jpeg" alt="Logo" class="brand-logo">
            <span class="brand-name">Attendance <span>System</span></span>
        </a>
        <a href="subjects.php" class="btn-nav-back">
            <i class="fa fa-arrow-left"></i> Back to Subjects
        </a>
    </div>
</header>

<div class="wrap">

    <!-- PAGE HEADING -->
    <div class="page-heading">
        <div class="icon-badge"><i class="fa fa-pen-to-square"></i></div>
        <div>
            <h3>Edit Subject</h3>
            <p>Update subject details and section assignment</p>
        </div>
    </div>

    <!-- ALERT -->
    <?php if($alert_msg !== ""): ?>
    <div class="alert-bar <?= $alert_type ?>">
        <i class="fa <?= $alert_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
        <?= htmlspecialchars($alert_msg) ?>
    </div>
    <?php endif; ?>

    <!-- FORM CARD -->
    <?php if($subject): ?>
    <div class="form-card">

        <div class="fc-head">
            <div class="fc-head-icon"><i class="fa fa-book-open"></i></div>
            <div class="fc-head-text">
                <h4><?= htmlspecialchars($subject['subject']) ?></h4>
                <p>
                    Section <?= htmlspecialchars($subject['section']) ?>
                    &nbsp;·&nbsp; <?= htmlspecialchars($subject['day']) ?>
                    &nbsp;·&nbsp; <?= htmlspecialchars($subject['start_time']) ?> – <?= htmlspecialchars($subject['end_time']) ?>
                </p>
            </div>
        </div>

        <div class="fc-body">

            <!-- Subject ID (read-only) -->
            <div class="id-badge">
                <div class="id-badge-icon"><i class="fa fa-hashtag"></i></div>
                <div>
                    <div class="id-badge-label">Subject ID</div>
                    <div class="id-badge-value"><?= htmlspecialchars($subject['id']) ?></div>
                </div>
            </div>

            <form method="POST">

                <div class="fc-section-label">
                    <i class="fa fa-circle-info"></i> Subject Information
                </div>

                <!-- Subject Name -->
                <div class="f-group">
                    <label class="f-label" for="subjectNameField">
                        <i class="fa fa-book"></i> Subject Name
                    </label>
                    <div class="f-wrap">
                        <div class="f-icon"><i class="fa fa-book"></i></div>
                        <input id="subjectNameField" type="text" name="subject" class="f-input"
                               value="<?= htmlspecialchars($subject['subject']) ?>"
                               placeholder="e.g. Mathematics, Science…" required>
                    </div>
                </div>

                <!-- Section & Day -->
                <div class="f-row">

                    <div class="f-group">
                        <label class="f-label" for="sectionField">
                            <i class="fa fa-layer-group"></i> Section
                        </label>
                        <div class="f-wrap">
                            <div class="f-icon"><i class="fa fa-layer-group"></i></div>
                            <select id="sectionField" name="section" class="f-select" required>
                                <option value="" disabled>Select section…</option>
                                <?php foreach($sections_list as $sec): ?>
                                    <option value="<?= $sec ?>" <?= ($subject['section'] == $sec) ? 'selected' : '' ?>>
                                        Section <?= $sec ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="f-group">
                        <label class="f-label" for="dayField">
                            <i class="fa fa-calendar-days"></i> Day
                        </label>
                        <div class="f-wrap">
                            <div class="f-icon"><i class="fa fa-calendar-days"></i></div>
                            <select id="dayField" name="day" class="f-select">
                                <option value="" disabled>Select day…</option>
                                <?php foreach($days_list as $d): ?>
                                    <option value="<?= $d ?>" <?= ($subject['day'] == $d) ? 'selected' : '' ?>>
                                        <?= $d ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="fc-section-label" style="margin-top:0.5rem;">
                    <i class="fa fa-clock"></i> Schedule / Time
                </div>

                <!-- Start Time & End Time -->
                <div class="f-row">

                    <div class="f-group">
                        <label class="f-label" for="startTimeField">
                            <i class="fa fa-hourglass-start"></i> Start Time
                        </label>
                        <div class="f-wrap">
                            <div class="f-icon"><i class="fa fa-hourglass-start"></i></div>
                            <input id="startTimeField" type="time" name="start_time" class="f-input"
                                   value="<?= htmlspecialchars($subject['start_time']) ?>">
                        </div>
                    </div>

                    <div class="f-group">
                        <label class="f-label" for="endTimeField">
                            <i class="fa fa-hourglass-end"></i> End Time
                        </label>
                        <div class="f-wrap">
                            <div class="f-icon"><i class="fa fa-hourglass-end"></i></div>
                            <input id="endTimeField" type="time" name="end_time" class="f-input"
                                   value="<?= htmlspecialchars($subject['end_time']) ?>">
                        </div>
                    </div>

                </div>

                <div class="fc-divider"></div>

                <button name="update" type="submit" class="btn-save">
                    <i class="fa fa-circle-check"></i> Save Changes
                </button>

            </form>

            <a href="subjects.php" class="btn-cancel">
                <i class="fa fa-arrow-left"></i> Cancel
            </a>

        </div>
    </div>
    <?php endif; ?>

</div>

</body>
</html>