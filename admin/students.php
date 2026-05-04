<?php
include "../db_connect.php";

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['admin'])){
    header("Location: ../index.php");
    exit();
}

if(isset($_GET['delete_id'])){
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    header("Location: students.php");
    exit();
}

$result = $conn->query("SELECT * FROM students ORDER BY section ASC, name ASC");

$sections = [];
$total = 0;
while($row = $result->fetch_assoc()){
    $sections[$row['section']][] = $row;
    $total++;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registered Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
    :root {
        --blue:    #2563EB;
        --blue-lt: #3B82F6;
        --green:   #059669;
        --border:  rgba(255,255,255,0.10);
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

    /* ── TOPBAR (matches subjects.php) ── */
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

    /* ── MAIN WRAP ── */
    .main-wrap {
        max-width: 1100px;
        margin: 0 auto;
        padding: 2rem 1.25rem 3rem;
    }

    /* ── PAGE HEADING ── */
    .page-heading {
        display: flex; align-items: center; gap: 14px;
        margin-bottom: 1.8rem;
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
        font-size: 1.9rem; font-weight: 800;
        margin: 0; color: #fff;
        text-shadow: 0 2px 12px rgba(0,0,0,0.6);
    }

    .page-heading p {
        margin: 2px 0 0; font-size: 0.88rem; font-weight: 500;
        color: var(--muted); text-shadow: 0 1px 6px rgba(0,0,0,0.5);
    }

    /* ── STATS ROW ── */
    .stats-row { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.8rem; }

    .stat-card {
        background: rgba(8,20,60,0.40);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-radius: 14px;
        border: 1px solid var(--border);
        padding: 1rem 1.4rem;
        display: flex; align-items: center; gap: 12px;
        flex: 1; min-width: 160px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.2);
    }

    .stat-card .stat-icon {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }

    .stat-card .stat-icon.blue  { background: rgba(37,99,235,0.25);  color: #93C5FD; }
    .stat-card .stat-icon.green { background: rgba(5,150,105,0.25);   color: #6EE7B7; }
    .stat-card .stat-icon.amber { background: rgba(217,119,6,0.25);   color: #FCD34D; }

    .stat-card .stat-val { font-family: 'Syne', sans-serif; font-size: 1.5rem; font-weight: 800; color: #fff; line-height: 1; }
    .stat-card .stat-lbl { font-size: 0.75rem; font-weight: 500; color: var(--muted); margin-top: 2px; }

    /* ── TOOLBAR ── */
    .toolbar {
        background: rgba(8,20,60,0.40);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-radius: 14px; border: 1px solid var(--border);
        padding: 1rem 1.25rem;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 0.75rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 18px rgba(0,0,0,0.2);
    }

    /* ── SEARCH BAR ── */
    .search-wrap { position: relative; max-width: 280px; flex: 1; }
    .search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.5); font-size: 0.85rem; }
    .search-wrap input {
        background: rgba(255,255,255,0.10);
        border: 1px solid var(--border); border-radius: 10px;
        color: #fff; padding: 0.45rem 0.9rem 0.45rem 2.2rem;
        font-size: 0.85rem; font-family: 'DM Sans', sans-serif;
        width: 100%; transition: border-color 0.2s, background 0.2s;
    }
    .search-wrap input::placeholder { color: rgba(255,255,255,0.45); }
    .search-wrap input:focus { outline: none; border-color: var(--blue-lt); background: rgba(255,255,255,0.15); box-shadow: 0 0 0 3px rgba(59,130,246,0.2); }

    /* ── SECTION FILTER ── */
    .section-filter select {
        background: rgba(255,255,255,0.10);
        border: 1px solid var(--border); border-radius: 10px;
        color: #fff; padding: 0.45rem 2.2rem 0.45rem 0.9rem;
        font-size: 0.85rem; font-family: 'DM Sans', sans-serif;
        appearance: none; -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='rgba(255,255,255,0.5)' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 0.7rem center;
        cursor: pointer; transition: border-color 0.2s;
    }
    .section-filter select:focus { outline: none; border-color: var(--blue-lt); box-shadow: 0 0 0 3px rgba(59,130,246,0.2); }
    .section-filter select option { background: #0a1a4a; color: #fff; }

    /* ── TOGGLE CONTROLS ── */
    .toggle-controls { display: flex; gap: 0.5rem; align-items: center; }

    .btn-toggle-all {
        background: rgba(255,255,255,0.10); border: 1px solid var(--border);
        border-radius: 8px; color: rgba(255,255,255,0.75);
        font-size: 0.78rem; font-weight: 600; padding: 0.38rem 0.75rem;
        cursor: pointer; transition: background 0.18s, color 0.18s;
        font-family: 'DM Sans', sans-serif;
        display: inline-flex; align-items: center; gap: 5px;
    }
    .btn-toggle-all:hover { background: rgba(255,255,255,0.18); color: #fff; }

    /* ── SECTION BLOCK ── */
    .section-block { margin-bottom: 1.2rem; animation: fadeUp 0.45s ease both; }

    .section-header {
        display: flex; align-items: center; justify-content: space-between;
        background: rgba(29,78,216,0.50);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 14px 14px 0 0;
        border: 1px solid rgba(59,130,246,0.3); border-bottom: none;
        padding: 0.9rem 1.25rem; cursor: pointer; user-select: none;
        transition: background 0.2s;
    }
    .section-header:hover { background: rgba(29,78,216,0.65); }
    .section-header.collapsed { border-radius: 14px; border-bottom: 1px solid rgba(59,130,246,0.3); }

    .section-header-left { display: flex; align-items: center; gap: 12px; }

    .section-icon {
        width: 36px; height: 36px; border-radius: 10px;
        background: rgba(255,255,255,0.15);
        display: flex; align-items: center; justify-content: center; font-size: 0.95rem;
    }

    .section-title { font-family: 'Syne', sans-serif; font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
    .section-subtitle { font-size: 0.73rem; color: rgba(255,255,255,0.55); margin: 0; }
    .section-header-right { display: flex; align-items: center; gap: 10px; }

    .section-count {
        background: rgba(255,255,255,0.18); border-radius: 20px;
        padding: 0.2rem 0.75rem; font-size: 0.78rem; font-weight: 700; color: #fff; letter-spacing: 0.3px;
    }

    .chevron-icon { font-size: 0.8rem; color: rgba(255,255,255,0.7); transition: transform 0.3s ease; }
    .chevron-icon.rotated { transform: rotate(-90deg); }

    /* ── SECTION BODY ── */
    .section-body {
        background: rgba(8,20,60,0.35); backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-radius: 0 0 14px 14px; border: 1px solid rgba(59,130,246,0.3); border-top: none;
        overflow: hidden; transition: max-height 0.35s ease, opacity 0.3s ease;
        max-height: 2000px; opacity: 1;
    }
    .section-body.collapsed { max-height: 0; opacity: 0; }

    /* ── TABLE ── */
    .table { color: #fff; margin: 0; border-collapse: separate; border-spacing: 0; width: 100%; table-layout: fixed; }
    .table colgroup col:nth-child(1) { width: 5%; }
    .table colgroup col:nth-child(2) { width: 22%; }
    .table colgroup col:nth-child(3) { width: 35%; }
    .table colgroup col:nth-child(4) { width: 38%; }

    .table thead th {
        background: rgba(15,40,100,0.55); color: rgba(255,255,255,0.65);
        font-family: 'DM Sans', sans-serif; font-size: 0.74rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: 1px;
        border: none; padding: 0.65rem 1rem; vertical-align: middle;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .table thead th:nth-child(1) { text-align: center; }
    .table thead th:nth-child(2) { text-align: left; }
    .table thead th:nth-child(3) { text-align: left; }
    .table thead th:nth-child(4) { text-align: center; }

    .table tbody tr { transition: background 0.18s; }
    .table tbody tr:hover td { background: rgba(59,130,246,0.12); }

    .table tbody td {
        border-color: rgba(255,255,255,0.06); padding: 0.8rem 1rem;
        vertical-align: middle; font-size: 0.9rem; font-weight: 500;
        color: rgba(255,255,255,0.92); background: transparent;
        text-shadow: 0 1px 4px rgba(0,0,0,0.4);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .table tbody td:nth-child(1) { text-align: center; }
    .table tbody td:nth-child(2) { text-align: left; }
    .table tbody td:nth-child(3) { text-align: left; }
    .table tbody td:nth-child(4) { text-align: center; }
    .table tbody tr:nth-child(even) td { background: rgba(255,255,255,0.04); }

    .row-num { color: rgba(255,255,255,0.40) !important; font-size: 0.8rem !important; font-weight: 400 !important; }
    .action-cell { white-space: nowrap !important; }
    .action-cell .btn { margin: 0 2px; }

    .id-pill {
        display: inline-block; background: rgba(37,99,235,0.2);
        border: 1px solid rgba(59,130,246,0.3); border-radius: 6px;
        padding: 0.15rem 0.6rem; font-size: 0.8rem; font-weight: 700;
        color: #93C5FD; letter-spacing: 0.5px; font-family: 'Syne', sans-serif;
    }

    .empty-state td { padding: 3rem 1rem !important; color: rgba(255,255,255,0.5) !important; font-style: italic; font-size: 0.9rem; text-align: center; }

    /* ── BUTTONS ── */
    .btn {
        font-family: 'DM Sans', sans-serif; font-size: 0.82rem; font-weight: 600;
        border-radius: 8px; padding: 0.38rem 0.85rem; border: none;
        transition: transform 0.18s ease, filter 0.18s ease;
        display: inline-flex; align-items: center; gap: 5px; text-decoration: none;
    }
    .btn:hover  { transform: translateY(-2px); filter: brightness(1.12); }
    .btn:active { transform: translateY(0); }
    .btn-warning  { background: linear-gradient(135deg,#b45309,#fbbf24); color:#fff; box-shadow: 0 3px 10px rgba(180,83,9,0.35); }
    .btn-danger   { background: linear-gradient(135deg,#b91c1c,#f87171); color:#fff; box-shadow: 0 3px 10px rgba(185,28,28,0.35); }
    .btn-success  { background: linear-gradient(135deg,#059669,#34d399); color:#fff; box-shadow: 0 3px 10px rgba(5,150,105,0.35); }

    /* ── NO RESULTS ── */
    .no-results {
        background: rgba(8,20,60,0.40); backdrop-filter: blur(16px);
        border-radius: 14px; border: 1px solid var(--border);
        padding: 3rem 1rem; text-align: center;
        color: rgba(255,255,255,0.5); font-style: italic; display: none;
    }

    /* ── ANIMATIONS ── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(28px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .anim { animation: fadeUp 0.55s ease both; }
    .d1 { animation-delay: 0.05s; }
    .d2 { animation-delay: 0.12s; }
    .d3 { animation-delay: 0.20s; }
    .d4 { animation-delay: 0.28s; }
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

<div class="main-wrap">

    <!-- Page Heading -->
    <div class="page-heading anim d1">
        <div class="icon-badge"><i class="fa fa-graduation-cap"></i></div>
        <div>
            <h3>Officially Enrolled Students</h3>
            <p>Manage and view all registered student records</p>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row anim d2">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fa fa-users"></i></div>
            <div>
                <div class="stat-val"><?= $total ?></div>
                <div class="stat-lbl">Total Students</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fa fa-layer-group"></i></div>
            <div>
                <div class="stat-val"><?= count($sections) ?></div>
                <div class="stat-lbl">Total Sections</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fa fa-chart-bar"></i></div>
            <div>
                <div class="stat-val"><?= count($sections) > 0 ? round($total / count($sections)) : 0 ?></div>
                <div class="stat-lbl">Avg. per Section</div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar anim d3">
        <div class="search-wrap">
            <i class="fa fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search students…" onkeyup="filterStudents()">
        </div>

        <div class="section-filter">
            <select id="sectionFilter" onchange="filterBySection()">
                <option value="all">All Sections</option>
                <?php foreach(array_keys($sections) as $sec): ?>
                    <option value="<?= htmlspecialchars($sec) ?>"><?= htmlspecialchars($sec) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="toggle-controls">
            <button class="btn-toggle-all" onclick="expandAll()">
                <i class="fa fa-chevron-down"></i> Expand All
            </button>
            <button class="btn-toggle-all" onclick="collapseAll()">
                <i class="fa fa-chevron-up"></i> Collapse All
            </button>
        </div>

        <a href="register.php" class="btn btn-success">
            <i class="fa fa-user-plus"></i> Register New Student
        </a>
    </div>

    <!-- Section Blocks -->
    <div id="sectionsContainer" class="anim d4">
        <?php if(count($sections) > 0): ?>
            <?php foreach($sections as $sectionName => $students): ?>
                <div class="section-block" data-section="<?= htmlspecialchars($sectionName) ?>">

                    <div class="section-header" onclick="toggleSection(this)">
                        <div class="section-header-left">
                            <div class="section-icon"><i class="fa fa-chalkboard-user"></i></div>
                            <div>
                                <p class="section-title"><?= htmlspecialchars($sectionName) ?></p>
                                <p class="section-subtitle">Click to expand or collapse</p>
                            </div>
                        </div>
                        <div class="section-header-right">
                            <span class="section-count">
                                <i class="fa fa-user me-1"></i>
                                <?= count($students) ?> Student<?= count($students) > 1 ? 's' : '' ?>
                            </span>
                            <i class="fa fa-chevron-down chevron-icon"></i>
                        </div>
                    </div>

                    <div class="section-body">
                        <div class="table-responsive">
                            <table class="table section-table">
                                <colgroup>
                                    <col style="width:5%">
                                    <col style="width:22%">
                                    <col style="width:35%">
                                    <col style="width:38%">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach($students as $row): ?>
                                    <tr class="student-row"
                                        data-name="<?= strtolower(htmlspecialchars($row['name'])) ?>"
                                        data-id="<?= strtolower(htmlspecialchars($row['student_id'])) ?>">
                                        <td class="row-num"><?= $i ?></td>
                                        <td><span class="id-pill"><?= htmlspecialchars($row['student_id']) ?></span></td>
                                        <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                        <td class="action-cell">
                                            <a href="edit_student.php?id=<?= $row['student_id'] ?>" class="btn btn-warning">
                                                <i class="fa fa-edit"></i> Edit
                                            </a>
                                            <a href="students.php?delete_id=<?= $row['student_id'] ?>" class="btn btn-danger"
                                               onclick="return confirm('Delete this student?');">
                                                <i class="fa fa-user-minus"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    <?php $i++; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results" style="display:block;">
                <i class="fa fa-inbox fa-2x mb-2" style="display:block; margin: 0 auto 0.5rem;"></i>
                No students registered yet.
            </div>
        <?php endif; ?>
    </div>

    <div class="no-results" id="noResults">
        <i class="fa fa-search fa-2x mb-2" style="display:block; margin: 0 auto 0.5rem;"></i>
        No students found matching your search.
    </div>

</div>

<script>
function toggleSection(header) {
    const body    = header.nextElementSibling;
    const chevron = header.querySelector('.chevron-icon');
    const isOpen  = !body.classList.contains('collapsed');
    if(isOpen){
        body.classList.add('collapsed');
        header.classList.add('collapsed');
        chevron.classList.add('rotated');
    } else {
        body.classList.remove('collapsed');
        header.classList.remove('collapsed');
        chevron.classList.remove('rotated');
    }
}

function expandAll() {
    document.querySelectorAll('.section-body').forEach(b => b.classList.remove('collapsed'));
    document.querySelectorAll('.section-header').forEach(h => h.classList.remove('collapsed'));
    document.querySelectorAll('.chevron-icon').forEach(c => c.classList.remove('rotated'));
}

function collapseAll() {
    document.querySelectorAll('.section-body').forEach(b => b.classList.add('collapsed'));
    document.querySelectorAll('.section-header').forEach(h => h.classList.add('collapsed'));
    document.querySelectorAll('.chevron-icon').forEach(c => c.classList.add('rotated'));
}

function filterBySection() {
    const val = document.getElementById('sectionFilter').value;
    document.querySelectorAll('.section-block').forEach(block => {
        block.style.display = (val === 'all' || block.dataset.section === val) ? '' : 'none';
    });
    checkNoResults();
}

function filterStudents() {
    const query      = document.getElementById('searchInput').value.toLowerCase().trim();
    const sectionVal = document.getElementById('sectionFilter').value;

    document.querySelectorAll('.section-block').forEach(block => {
        if(sectionVal !== 'all' && block.dataset.section !== sectionVal){
            block.style.display = 'none';
            return;
        }
        let hasMatch = false;
        block.querySelectorAll('.student-row').forEach(row => {
            const match = !query || row.dataset.name.includes(query) || row.dataset.id.includes(query);
            row.style.display = match ? '' : 'none';
            if(match) hasMatch = true;
        });
        block.style.display = hasMatch ? '' : 'none';
        if(query && hasMatch){
            block.querySelector('.section-body').classList.remove('collapsed');
            block.querySelector('.section-header').classList.remove('collapsed');
            block.querySelector('.chevron-icon').classList.remove('rotated');
        }
    });
    checkNoResults();
}

function checkNoResults() {
    const anyVisible = [...document.querySelectorAll('.section-block')].some(b => b.style.display !== 'none');
    document.getElementById('noResults').style.display = anyVisible ? 'none' : 'block';
}
</script>

</body>
</html>