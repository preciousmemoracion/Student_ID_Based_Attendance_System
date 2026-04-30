<?php
include "../db_connect.php";

// Start session if not already started
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// Check admin login
if(!isset($_SESSION['admin'])){
    header("Location: ../index.php");
    exit();
}

// DELETE student if requested
if(isset($_GET['delete_id'])){
    $id = $_GET['delete_id'];

    // Prepared statement for safety
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();

    header("Location: students.php");
    exit();
}

// Fetch all students
$result = $conn->query("SELECT * FROM students ORDER BY name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registered Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

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
        position: fixed;
        inset: 0;
        background: linear-gradient(135deg,
            rgba(3,10,35,0.45) 0%,
            rgba(10,30,80,0.38) 60%,
            rgba(3,10,35,0.48) 100%);
        z-index: 0;
    }

    body > * { position: relative; z-index: 1; }

    /* ── NAVBAR ── */
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
        color: #fff !important;
        gap: 12px;
    }

    .navbar-brand img {
        width: 42px; height: 42px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.25);
        box-shadow: 0 0 0 4px rgba(37,99,235,0.25);
    }

    /* ── MAIN WRAP ── */
    .main-wrap {
        max-width: 1100px;
        margin: 0 auto;
        padding: 2rem 1.25rem 3rem;
    }

    /* ── PAGE HEADING ── */
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
        font-size: 1.9rem;
        font-weight: 800;
        margin: 0;
        color: #fff;
        text-shadow: 0 2px 12px rgba(0,0,0,0.6);
    }

    .page-heading p {
        margin: 2px 0 0;
        font-size: 0.88rem;
        font-weight: 500;
        color: var(--muted);
        text-shadow: 0 1px 6px rgba(0,0,0,0.5);
    }

    /* ── GLASS PANEL ── */
    .glass-panel {
        background: rgba(8,20,60,0.40);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        padding: 1.5rem;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    }

    /* ── SEARCH BAR ── */
    .search-wrap {
        position: relative;
        max-width: 320px;
    }

    .search-wrap i {
        position: absolute;
        left: 12px; top: 50%;
        transform: translateY(-50%);
        color: rgba(255,255,255,0.5);
        font-size: 0.85rem;
    }

    .search-wrap input {
        background: rgba(255,255,255,0.10);
        border: 1px solid var(--border);
        border-radius: 10px;
        color: #fff;
        padding: 0.45rem 0.9rem 0.45rem 2.2rem;
        font-size: 0.85rem;
        font-family: 'DM Sans', sans-serif;
        width: 100%;
        transition: border-color 0.2s, background 0.2s;
    }

    .search-wrap input::placeholder { color: rgba(255,255,255,0.45); }
    .search-wrap input:focus {
        outline: none;
        border-color: var(--blue-lt);
        background: rgba(255,255,255,0.15);
        box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
    }

    /* ── COUNT BADGE ── */
    .count-badge {
        background: rgba(37,99,235,0.25);
        border: 1px solid rgba(59,130,246,0.4);
        border-radius: 8px;
        padding: 0.3rem 0.8rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #93C5FD;
        letter-spacing: 0.3px;
    }

    /* ── TABLE ── */
    .table {
        color: #fff;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background: rgba(29,78,216,0.65);
        color: #fff;
        font-family: 'Syne', sans-serif;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        border: none;
        padding: 0.85rem 1rem;
    }

    .table thead th:first-child { border-radius: 10px 0 0 0; }
    .table thead th:last-child  { border-radius: 0 10px 0 0; }

    .table tbody tr {
        transition: background 0.18s;
    }

    .table tbody tr:hover td {
        background: rgba(59,130,246,0.12);
    }

    .table tbody td {
        border-color: rgba(255,255,255,0.07);
        padding: 0.85rem 1rem;
        vertical-align: middle;
        font-size: 0.9rem;
        font-weight: 500;
        color: rgba(255,255,255,0.92);
        background: rgba(255,255,255,0.04);
        text-shadow: 0 1px 4px rgba(0,0,0,0.4);
    }

    /* alternating rows */
    .table tbody tr:nth-child(even) td {
        background: rgba(255,255,255,0.07);
    }

    /* student ID pill */
    .id-pill {
        display: inline-block;
        background: rgba(37,99,235,0.2);
        border: 1px solid rgba(59,130,246,0.3);
        border-radius: 6px;
        padding: 0.15rem 0.6rem;
        font-size: 0.8rem;
        font-weight: 700;
        color: #93C5FD;
        letter-spacing: 0.5px;
        font-family: 'Syne', sans-serif;
    }

    /* section badge */
    .section-badge {
        display: inline-block;
        background: rgba(16,185,129,0.18);
        border: 1px solid rgba(16,185,129,0.35);
        border-radius: 20px;
        padding: 0.2rem 0.75rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: #6EE7B7;
        letter-spacing: 0.3px;
    }

    /* empty state */
    .empty-state td {
        padding: 3rem 1rem !important;
        color: rgba(255,255,255,0.5) !important;
        font-style: italic;
        font-size: 0.9rem;
    }

    /* ── BUTTONS ── */
    .btn {
        font-family: 'DM Sans', sans-serif;
        font-size: 0.82rem;
        font-weight: 600;
        border-radius: 8px;
        padding: 0.38rem 0.85rem;
        border: none;
        transition: transform 0.18s ease, filter 0.18s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn:hover   { transform: translateY(-2px); filter: brightness(1.12); }
    .btn:active  { transform: translateY(0); }

    .btn-warning { background: linear-gradient(135deg,#b45309,#fbbf24); color:#fff; box-shadow: 0 3px 10px rgba(180,83,9,0.35); }
    .btn-danger  { background: linear-gradient(135deg,#b91c1c,#f87171); color:#fff; box-shadow: 0 3px 10px rgba(185,28,28,0.35); }
    .btn-secondary{ background: rgba(255,255,255,0.15); border: 1px solid var(--border); color:#fff; }
    .btn-success { background: linear-gradient(135deg,#059669,#34d399); color:#fff; box-shadow: 0 3px 10px rgba(5,150,105,0.35); }

    /* ── BOTTOM BAR ── */
    .bottom-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-top: 1.5rem;
    }

    /* ── ANIMATIONS ── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(28px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .anim { animation: fadeUp 0.55s ease both; }
    .d1 { animation-delay: 0.05s; }
    .d2 { animation-delay: 0.15s; }
    .d3 { animation-delay: 0.25s; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <span class="navbar-brand d-flex align-items-center">
            <img src="../img/icas_logo.jpeg" alt="Logo">
            Attendance System
        </span>
    </div>
</nav>

<div class="main-wrap">

    <!-- Page Heading -->
    <div class="page-heading anim d1">
        <div class="icon-badge"><i class="fa fa-graduation-cap"></i></div>
        <div>
            <h3>Officially Enrolled Students</h3>
            <p>Manage and view all registered student records</p>
        </div>
    </div>

    <!-- Glass Panel -->
    <div class="glass-panel anim d2">

        <!-- Panel Top Bar -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div class="search-wrap">
                <i class="fa fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search students…" onkeyup="filterTable()">
            </div>
            <span class="count-badge">
                <i class="fa fa-users me-1"></i>
                <?= $result->num_rows ?> Students
            </span>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table" id="studentTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Section</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($result->num_rows > 0){
                        $i = 1;
                        while($row = $result->fetch_assoc()){
                            echo "<tr>
                                    <td style='color:rgba(255,255,255,0.45);font-size:0.8rem;'>{$i}</td>
                                    <td><span class='id-pill'>{$row['student_id']}</span></td>
                                    <td><strong>{$row['name']}</strong></td>
                                    <td><span class='section-badge'>{$row['section']}</span></td>
                                    <td style='text-align:center;'>
                                        <a href='edit_student.php?id={$row['student_id']}' class='btn btn-warning'>
                                            <i class='fa fa-edit'></i> Edit
                                        </a>
                                        <a href='students.php?delete_id={$row['student_id']}' class='btn btn-danger'
                                           onclick=\"return confirm('Delete this student?');\">
                                            <i class='fa fa-user-minus'></i> Delete
                                        </a>
                                    </td>
                                  </tr>";
                            $i++;
                        }
                    } else {
                        echo "<tr class='empty-state'><td colspan='5'><i class='fa fa-inbox me-2'></i>No students registered yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="bottom-bar anim d3">
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>
        <a href="register.php" class="btn btn-success">
            <i class="fa fa-user-plus"></i> Register New Student
        </a>
    </div>

</div>

<script>
function filterTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const rows  = document.querySelectorAll('#studentTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(input) ? '' : 'none';
    });
}
</script>

</body>
</html>