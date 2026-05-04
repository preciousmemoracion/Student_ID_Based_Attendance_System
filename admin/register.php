<?php 
include "../db_connect.php";

// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Protect page (admin only)
if(!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit();
}

$message = "";

// PROCESS FORM
if(isset($_POST['save'])){

    $id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $section = trim($_POST['section']);

    if(empty($id) || empty($name) || empty($section)){
        $message = "error:All fields are required!";
    } else {

        // Check if student already exists
        $stmt = $conn->prepare("SELECT * FROM students WHERE student_id=?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){
            $message = "error:Student ID already exists!";
        } else {

            // Insert new student
            $stmt = $conn->prepare("INSERT INTO students (student_id, name, section) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $id, $name, $section);
            $stmt->execute();

            header("Location: students.php");
            exit();
        }
    }
}

// Parse message type
$msg_type = "";
$msg_text = "";
if($message != ""){
    [$msg_type, $msg_text] = explode(":", $message, 2);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register Student</title>

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
    display: flex;
    flex-direction: column;
}

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

/* ── TOPBAR (matches students.php) ── */
.topbar {
    position: sticky; top: 0; z-index: 100;
    background: rgba(5,12,40,0.75);
    backdrop-filter: blur(22px) saturate(180%);
    -webkit-backdrop-filter: blur(22px) saturate(180%);
    border-bottom: 1px solid var(--border);
    padding: 0.6rem 0;
}

.topbar-inner {
    max-width: 1100px; margin: 0 auto;
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

/* ── CENTER LAYOUT ── */
.page-center {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 1rem;
}

/* ── FORM CARD ── */
.form-card {
    width: 100%;
    max-width: 460px;
    background: rgba(8,20,60,0.45);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border-radius: 22px;
    border: 1px solid var(--border);
    box-shadow: 0 16px 48px rgba(0,0,0,0.4);
    overflow: hidden;
    animation: fadeUp 0.55s ease both;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── CARD HEADER ── */
.form-card-header {
    background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
    padding: 1.6rem 1.75rem 1.4rem;
    position: relative;
    overflow: hidden;
}

.form-card-header::after {
    content: '';
    position: absolute;
    top: -40%; right: -10%;
    width: 140px; height: 140px;
    border-radius: 50%;
    background: rgba(255,255,255,0.07);
    pointer-events: none;
}

.form-card-header .icon-wrap {
    width: 52px; height: 52px;
    border-radius: 14px;
    background: rgba(255,255,255,0.18);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
    margin-bottom: 0.75rem;
}

.form-card-header h4 {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.35rem;
    margin: 0 0 3px;
    color: #fff;
}

.form-card-header p {
    font-size: 0.82rem;
    color: rgba(255,255,255,0.75);
    margin: 0;
    font-weight: 500;
}

/* ── CARD BODY ── */
.form-card-body {
    padding: 1.75rem;
}

/* ── ALERT ── */
.custom-alert {
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.custom-alert.error {
    background: rgba(185,28,28,0.25);
    border: 1px solid rgba(248,113,113,0.4);
    color: #FCA5A5;
}

/* ── FIELD GROUPS ── */
.field-group {
    margin-bottom: 1.1rem;
}

.field-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(255,255,255,0.7);
    margin-bottom: 0.4rem;
    display: block;
}

.field-input {
    width: 100%;
    background: rgba(255,255,255,0.08);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: #fff;
    padding: 0.6rem 0.9rem 0.6rem 2.5rem;
    font-size: 0.9rem;
    font-family: 'DM Sans', sans-serif;
    font-weight: 500;
    transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
}

.field-input::placeholder { color: rgba(255,255,255,0.35); }

.field-input:focus {
    outline: none;
    border-color: var(--blue-lt);
    background: rgba(255,255,255,0.13);
    box-shadow: 0 0 0 3px rgba(59,130,246,0.22);
}

/* select dropdown */
.field-input option {
    background: #1e3a6e;
    color: #fff;
}

.input-wrap {
    position: relative;
}

.input-wrap .input-icon {
    position: absolute;
    left: 11px; top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.4);
    font-size: 0.85rem;
    pointer-events: none;
}

/* ── DIVIDER ── */
.field-divider {
    border: none;
    height: 1px;
    background: var(--border);
    margin: 1.4rem 0;
}

/* ── SUBMIT BTN ── */
.btn-register {
    width: 100%;
    background: linear-gradient(135deg, #059669, #34d399);
    color: #fff;
    border: none;
    border-radius: 11px;
    padding: 0.7rem 1rem;
    font-family: 'Syne', sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(5,150,105,0.4);
    transition: transform 0.18s ease, filter 0.18s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-register:hover  { transform: translateY(-2px); filter: brightness(1.1); }
.btn-register:active { transform: translateY(0); }

/* ── BACK LINK ── */
.back-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 1rem;
    font-size: 0.83rem;
    font-weight: 600;
    color: rgba(255,255,255,0.6);
    text-decoration: none;
    transition: color 0.18s;
}

.back-link:hover { color: #fff; }
</style>

</head>
<body>

<!-- ── TOPBAR (matches students.php) ── -->
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

<!-- CENTERED FORM -->
<div class="page-center">
    <div class="form-card">

        <!-- Header -->
        <div class="form-card-header">
            <div class="icon-wrap"><i class="fa fa-user-plus"></i></div>
            <h4>Register Student</h4>
            <p>Fill in the details to enroll a new student</p>
        </div>

        <!-- Body -->
        <div class="form-card-body">

            <?php if($msg_text != ""): ?>
            <div class="custom-alert error">
                <i class="fa fa-circle-exclamation"></i>
                <?= htmlspecialchars($msg_text) ?>
            </div>
            <?php endif; ?>

            <form method="POST">

                <!-- Student ID -->
                <div class="field-group">
                    <label class="field-label">Student ID</label>
                    <div class="input-wrap">
                        <i class="fa fa-id-card input-icon"></i>
                        <input type="text" name="student_id" class="field-input"
                               placeholder="e.g. 2024-00123"
                               value="<?= isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : '' ?>"
                               required>
                    </div>
                </div>

                <!-- Name -->
                <div class="field-group">
                    <label class="field-label">Full Name</label>
                    <div class="input-wrap">
                        <i class="fa fa-user input-icon"></i>
                        <input type="text" name="name" class="field-input"
                               placeholder="e.g. Juan Dela Cruz"
                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
                               required>
                    </div>
                </div>

                <!-- Section -->
                <div class="field-group">
                    <label class="field-label">Section</label>
                    <div class="input-wrap">
                        <i class="fa fa-layer-group input-icon"></i>
                        <select name="section" class="field-input" required>
                            <option value="">Select Section</option>
                            <?php
                            $sections = ['1A','1B','1C','2A','2B','2C','3A','3B','3C','4A','4B','4C'];
                            $selected_section = isset($_POST['section']) ? $_POST['section'] : '';
                            foreach($sections as $s){
                                $sel = ($selected_section === $s) ? 'selected' : '';
                                echo "<option value='$s' $sel>$s</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <hr class="field-divider">

                <button type="submit" name="save" class="btn-register">
                    <i class="fa fa-save"></i> Register Student
                </button>

            </form>



        </div>
    </div>
</div>

</body>
</html>