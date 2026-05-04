<?php
include "../db_connect.php";

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['admin']) && !isset($_SESSION['instructor'])){
    header("Location: ../index.php");
    exit();
}

/* DELETE SUBJECT */
if(isset($_GET['delete_id'])){
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: subjects.php");
    exit();
}

/* SORTED */
$result = $conn->query("
    SELECT * FROM subjects
    ORDER BY 
        subject ASC,
        section ASC,
        FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'),
        start_time ASC
");

/* GROUP BY SUBJECT */
$grouped = [];
$total   = 0;
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $grouped[$row['subject']][] = $row;
        $total++;
    }
}

/* Day color map */
$dayColors = [
    'Monday'    => ['bg'=>'rgba(59,130,246,0.18)',  'border'=>'rgba(59,130,246,0.45)',  'text'=>'#93C5FD'],
    'Tuesday'   => ['bg'=>'rgba(168,85,247,0.18)',  'border'=>'rgba(168,85,247,0.45)',  'text'=>'#D8B4FE'],
    'Wednesday' => ['bg'=>'rgba(236,72,153,0.18)',  'border'=>'rgba(236,72,153,0.45)',  'text'=>'#FBCFE8'],
    'Thursday'  => ['bg'=>'rgba(245,158,11,0.18)',  'border'=>'rgba(245,158,11,0.45)',  'text'=>'#FCD34D'],
    'Friday'    => ['bg'=>'rgba(16,185,129,0.18)',  'border'=>'rgba(16,185,129,0.45)',  'text'=>'#6EE7B7'],
    'Saturday'  => ['bg'=>'rgba(239,68,68,0.18)',   'border'=>'rgba(239,68,68,0.45)',   'text'=>'#FCA5A5'],
];

/* Subject accent colors (cycles) */
$accentPalette = [
    ['a'=>'#3B82F6','b'=>'#1D4ED8'],
    ['a'=>'#8B5CF6','b'=>'#6D28D9'],
    ['a'=>'#EC4899','b'=>'#BE185D'],
    ['a'=>'#10B981','b'=>'#065F46'],
    ['a'=>'#F59E0B','b'=>'#92400E'],
    ['a'=>'#06B6D4','b'=>'#0E7490'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subject Schedules</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

<style>
/* ════════════════════════════════
   ROOT
════════════════════════════════ */
:root {
    --surface:  rgba(8,18,55,0.52);
    --glass:    rgba(255,255,255,0.055);
    --border:   rgba(255,255,255,0.10);
    --border2:  rgba(255,255,255,0.06);
    --muted:    rgba(255,255,255,0.48);
    --bright:   #fff;
    --blue:     #3B82F6;
    --green:    #10B981;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }

body {
    min-height: 100vh;
    font-family: 'Outfit', sans-serif;
    color: var(--bright);
    background: url('../img/icas.jpeg') no-repeat center center / cover fixed;
    overflow-x: hidden;
}

body::before {
    content: '';
    position: fixed; inset: 0;
    background:
        radial-gradient(ellipse 80% 60% at 15% 10%, rgba(29,78,216,0.28) 0%, transparent 60%),
        radial-gradient(ellipse 60% 50% at 85% 80%, rgba(109,40,217,0.22) 0%, transparent 55%),
        linear-gradient(160deg, rgba(3,8,30,0.74) 0%, rgba(8,18,55,0.66) 50%, rgba(3,8,30,0.76) 100%);
    z-index: 0;
}

body > * { position: relative; z-index: 1; }

/* ════════════════════════════════
   TOPBAR
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
}

.brand { display: flex; align-items: center; gap: 11px; text-decoration: none; }

.brand-logo {
    width: 40px; height: 40px; border-radius: 12px; object-fit: cover;
    border: 1.5px solid rgba(59,130,246,0.45);
    box-shadow: 0 0 0 3px rgba(59,130,246,0.14), 0 4px 14px rgba(0,0,0,0.4);
}

.brand-name { font-size: 1rem; font-weight: 800; color: #fff; letter-spacing: -0.2px; }
.brand-name span { color: #60A5FA; }

.user-chip {
    display: flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,0.07);
    border: 1px solid var(--border);
    border-radius: 30px;
    padding: 0.28rem 0.85rem 0.28rem 0.48rem;
    font-size: 0.82rem; font-weight: 600;
    color: rgba(255,255,255,0.88);
}

.user-chip .avatar {
    width: 26px; height: 26px; border-radius: 50%;
    background: linear-gradient(135deg, var(--blue), #8B5CF6);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.65rem; font-weight: 800;
}

.online-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--green);
    box-shadow: 0 0 0 2px rgba(16,185,129,0.25);
    animation: breathe 2.4s ease-in-out infinite;
}

@keyframes breathe {
    0%,100% { opacity:1; transform:scale(1); }
    50%      { opacity:0.5; transform:scale(1.45); }
}

/* ════════════════════════════════
   WRAP
════════════════════════════════ */
.wrap {
    max-width: 1280px; margin: 0 auto;
    padding: 1.5rem 1.5rem 4rem;
}

/* ════════════════════════════════
   HERO
════════════════════════════════ */
.hero {
    display: flex; align-items: flex-end;
    justify-content: space-between; flex-wrap: wrap;
    gap: 1rem; margin-bottom: 1.2rem;
    animation: riseUp 0.6s cubic-bezier(0.22,1,0.36,1) both;
}

.hero-left { display: flex; align-items: center; gap: 18px; }

.hero-icon {
    width: 62px; height: 62px; border-radius: 18px; flex-shrink: 0;
    background: linear-gradient(135deg, #1D4ED8, #3B82F6);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.45rem;
    box-shadow: 0 8px 30px rgba(29,78,216,0.5), inset 0 1px 0 rgba(255,255,255,0.15);
    position: relative; overflow: hidden;
}

.hero-icon::after {
    content: '';
    position: absolute; top: -30%; left: -10%;
    width: 60%; height: 60%;
    background: rgba(255,255,255,0.18);
    border-radius: 50%; filter: blur(8px);
}

.hero-title {
    font-size: 2.2rem; font-weight: 900; letter-spacing: -0.8px; line-height: 1.1;
    background: linear-gradient(135deg, #fff 40%, #93C5FD 100%);
    -webkit-background-clip: text; background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero-sub {
    margin-top: 5px; font-size: 0.88rem; font-weight: 400;
    color: var(--muted); letter-spacing: 0.1px;
}

.stats-row { display: flex; gap: 0.75rem; flex-wrap: wrap; }

.stat-pill {
    background: var(--glass);
    border: 1px solid var(--border);
    border-radius: 14px; padding: 0.6rem 1.2rem;
    text-align: center; min-width: 80px;
}

.stat-pill .num {
    font-size: 1.7rem; font-weight: 900; display: block; line-height: 1;
    background: linear-gradient(135deg, #fff, #93C5FD);
    -webkit-background-clip: text; background-clip: text;
    -webkit-text-fill-color: transparent;
    font-family: 'JetBrains Mono', monospace;
}

.stat-pill .lbl {
    font-size: 0.68rem; font-weight: 600;
    color: var(--muted); text-transform: uppercase;
    letter-spacing: 1px; margin-top: 3px; display: block;
}

/* ════════════════════════════════
   TOOLBAR
════════════════════════════════ */
.toolbar {
    display: flex; align-items: center; gap: 1rem;
    margin-bottom: 1.2rem; flex-wrap: wrap;
    animation: riseUp 0.6s cubic-bezier(0.22,1,0.36,1) 0.08s both;
}

.search-box {
    position: relative; flex: 1;
    min-width: 220px; max-width: 400px;
}

.search-box input {
    width: 100%;
    background: rgba(10,20,58,0.62);
    backdrop-filter: blur(10px);
    border: 1px solid var(--border);
    border-radius: 13px; color: #fff;
    padding: 0.65rem 1rem 0.65rem 2.6rem;
    font-size: 0.88rem; font-family: 'Outfit', sans-serif; font-weight: 500;
    transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
}

.search-box input::placeholder { color: rgba(255,255,255,0.27); }

.search-box input:focus {
    outline: none;
    border-color: rgba(59,130,246,0.6);
    background: rgba(255,255,255,0.08);
    box-shadow: 0 0 0 4px rgba(59,130,246,0.12);
}

.search-box .s-icon {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    color: var(--muted); font-size: 0.82rem; pointer-events: none;
    transition: color 0.2s;
}

.search-box:focus-within .s-icon { color: #60A5FA; }

/* ════════════════════════════════
   SUBJECT BLOCKS
════════════════════════════════ */
.subject-block {
    margin-bottom: 1.5rem;
    animation: riseUp 0.65s cubic-bezier(0.22,1,0.36,1) both;
}

.subject-block:nth-child(1) { animation-delay: 0.10s; }
.subject-block:nth-child(2) { animation-delay: 0.16s; }
.subject-block:nth-child(3) { animation-delay: 0.22s; }
.subject-block:nth-child(4) { animation-delay: 0.28s; }
.subject-block:nth-child(5) { animation-delay: 0.34s; }
.subject-block:nth-child(n+6) { animation-delay: 0.40s; }

.subject-label {
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 1rem; padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--border2);
}

.subject-dot {
    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
    box-shadow: 0 0 10px currentColor;
}

.subject-name {
    font-size: 1.1rem; font-weight: 800;
    letter-spacing: -0.2px; color: #fff;
    text-shadow: 0 2px 12px rgba(0,0,0,0.5);
}

.subject-tag {
    font-size: 0.68rem; font-weight: 700;
    letter-spacing: 0.8px; text-transform: uppercase;
    padding: 0.18rem 0.65rem; border-radius: 30px;
    font-family: 'JetBrains Mono', monospace;
}

.subject-divider {
    flex: 1; height: 1px;
    background: linear-gradient(to right, var(--border), transparent);
}

/* ════════════════════════════════
   VERTICAL TABLE
════════════════════════════════ */
.cards-grid {
    display: flex;
    flex-direction: column;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid var(--border);
}

/* Table header */
.table-head-row {
    display: grid;
    grid-template-columns: 1.4fr 2fr 2fr 1fr;
    background: rgba(10,22,65,0.80);
    backdrop-filter: blur(12px);
    padding: 0.55rem 1.25rem;
    border-bottom: 1px solid var(--border);
}

.table-head-row span {
    font-size: 0.67rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 1.3px;
    color: var(--muted);
    font-family: 'JetBrains Mono', monospace;
}

/* ── SCHEDULE ROW ── */
.sched-card {
    background: rgba(8,18,55,0.42);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--border2);
    position: relative;
    display: grid;
    grid-template-columns: 1.4fr 2fr 2fr 1fr;
    align-items: center;
    padding: 0.8rem 1.25rem;
    transition: background 0.2s ease;
    gap: 0.5rem;
}

.sched-card:last-child { border-bottom: none; }

/* left accent bar */
.sched-card::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: linear-gradient(to bottom, var(--accent-a), var(--accent-b));
}

.sched-card:hover { background: rgba(59,130,246,0.09); }

/* even row tint */
.sched-card:nth-child(even) { background: rgba(255,255,255,0.025); }
.sched-card:nth-child(even):hover { background: rgba(59,130,246,0.09); }

/* Section cell */
.section-badge {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 0.86rem; font-weight: 800;
    letter-spacing: 0.2px; color: #fff;
}

.s-icon {
    width: 28px; height: 28px; border-radius: 8px;
    background: var(--glass); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.62rem; color: var(--muted); flex-shrink: 0;
}

/* Day chip */
.day-chip {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 0.28rem 0.75rem; border-radius: 8px; border: 1px solid;
    font-size: 0.78rem; font-weight: 700; letter-spacing: 0.3px;
    width: fit-content;
}

.day-chip i { font-size: 0.65rem; }

/* Time cell */
.time-row { display: flex; align-items: center; gap: 8px; }

.time-icon {
    width: 26px; height: 26px; border-radius: 7px;
    background: rgba(139,92,246,0.18);
    border: 1px solid rgba(139,92,246,0.3);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.62rem; color: #C4B5FD; flex-shrink: 0;
}

.time-text {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.83rem; font-weight: 600;
    color: #E9D5FF; letter-spacing: 0.3px;
}

/* Actions cell */
.card-actions {
    display: flex; gap: 5px;
    justify-content: flex-end;
}

/* ════════════════════════════════
   BUTTONS
════════════════════════════════ */
.btn-act {
    display: inline-flex; align-items: center; gap: 4px;
    font-family: 'Outfit', sans-serif;
    font-size: 0.73rem; font-weight: 700;
    border: none; cursor: pointer; border-radius: 8px;
    padding: 0.27rem 0.62rem;
    transition: transform 0.18s ease, filter 0.18s ease;
    letter-spacing: 0.2px; text-decoration: none;
}

.btn-act:hover  { transform: translateY(-2px); filter: brightness(1.15); }
.btn-act:active { transform: translateY(0); filter: brightness(0.95); }

.btn-edit {
    background: linear-gradient(135deg, #92400E, #F59E0B);
    color: #fff; box-shadow: 0 3px 10px rgba(245,158,11,0.28);
}

.btn-del {
    background: linear-gradient(135deg, #991B1B, #EF4444);
    color: #fff; box-shadow: 0 3px 10px rgba(239,68,68,0.28);
}

.btn-back {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,0.09);
    border: 1px solid var(--border); color: rgba(255,255,255,0.88);
    border-radius: 12px; padding: 0.58rem 1.2rem;
    font-family: 'Outfit', sans-serif;
    font-size: 0.85rem; font-weight: 700;
    text-decoration: none;
    transition: background 0.2s, transform 0.2s;
}

.btn-back:hover { background: rgba(255,255,255,0.14); transform: translateX(-3px); color: #fff; }

.btn-add {
    display: inline-flex; align-items: center; gap: 8px;
    background: linear-gradient(135deg, #065F46, #10B981);
    color: #fff; border-radius: 12px; padding: 0.58rem 1.25rem;
    font-family: 'Outfit', sans-serif;
    font-size: 0.85rem; font-weight: 700;
    text-decoration: none;
    box-shadow: 0 5px 18px rgba(16,185,129,0.35);
    transition: transform 0.2s, filter 0.2s;
}

.btn-add:hover { transform: translateY(-2px); filter: brightness(1.1); color: #fff; }

/* ════════════════════════════════
   EMPTY STATE
════════════════════════════════ */
.empty-state {
    text-align: center; padding: 5rem 1rem; color: var(--muted);
}

.empty-state i { font-size: 3rem; opacity: 0.22; display: block; margin-bottom: 1rem; }
.empty-state p { font-size: 0.95rem; font-style: italic; }

/* ════════════════════════════════
   ANIMATIONS
════════════════════════════════ */
@keyframes riseUp {
    from { opacity: 0; transform: translateY(28px); }
    to   { opacity: 1; transform: translateY(0); }
}

@media (max-width: 640px) {
    .hero-title { font-size: 1.75rem; }
    .cards-grid { grid-template-columns: 1fr; }
    .hero { flex-direction: column; align-items: flex-start; }
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
        <div class="user-chip">
            <div class="avatar"><?= strtoupper(substr($_SESSION['admin'] ?? $_SESSION['instructor'], 0, 1)) ?></div>
            <?= htmlspecialchars($_SESSION['admin'] ?? $_SESSION['instructor']) ?>
            <span class="online-dot"></span>
        </div>
    </div>
</header>

<div class="wrap">

    <!-- HERO -->
    <div class="hero">
        <div class="hero-left">
            <div class="hero-icon"><i class="fa fa-calendar-check"></i></div>
            <div>
                <div class="hero-title">Subject Schedules</div>
                <div class="hero-sub">All subjects, sections and time slots at a glance</div>
            </div>
        </div>
        <div class="stats-row">
            <div class="stat-pill">
                <span class="num"><?= count($grouped) ?></span>
                <span class="lbl">Subjects</span>
            </div>
            <div class="stat-pill">
                <span class="num"><?= $total ?></span>
                <span class="lbl">Sections</span>
            </div>
        </div>
    </div>

    <!-- ✅ TOOLBAR: Back to Dashboard moved here, alongside search and Add Subject -->
    <div class="toolbar">

        <!-- ✅ MOVED: Back to Dashboard now at top left of toolbar -->
        <a href="dashboard.php" class="btn-back">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="search-box">
            <i class="fa fa-magnifying-glass s-icon"></i>
            <input type="text" id="searchInput"
                   placeholder="Search subject, section, or day…"
                   oninput="filterCards()" autocomplete="off">
        </div>

        <!-- ✅ MOVED: Add Subject also promoted to toolbar (admin only) -->
        <?php if(isset($_SESSION['admin'])): ?>
        <a href="register_subject.php" class="btn-add">
            <i class="fa fa-plus"></i> Add Subject
        </a>
        <?php endif; ?>

    </div>

    <!-- SUBJECT BLOCKS -->
    <?php if(!empty($grouped)):
        $ai = 0;
        foreach($grouped as $subjectName => $rows):
            $ac = $accentPalette[$ai % count($accentPalette)];
            $ai++;
    ?>
    <div class="subject-block" data-subject="<?= strtolower(htmlspecialchars($subjectName)) ?>">

        <div class="subject-label">
            <span class="subject-dot" style="background:<?= $ac['a'] ?>; color:<?= $ac['a'] ?>;"></span>
            <span class="subject-name"><?= htmlspecialchars($subjectName) ?></span>
            <span class="subject-tag"
                  style="background:<?= $ac['a'] ?>22; border:1px solid <?= $ac['a'] ?>55; color:<?= $ac['a'] ?>;">
                <?= count($rows) ?> <?= count($rows) === 1 ? 'section' : 'sections' ?>
            </span>
            <div class="subject-divider"></div>
        </div>

        <div class="cards-grid">

            <!-- Table Header -->
            <div class="table-head-row">
                <span><i class="fa fa-layer-group" style="margin-right:6px;opacity:0.6;"></i>Section</span>
                <span><i class="fa fa-calendar-days" style="margin-right:6px;opacity:0.6;"></i>Day</span>
                <span><i class="fa fa-clock" style="margin-right:6px;opacity:0.6;"></i>Time</span>
                <span style="text-align:right;">Actions</span>
            </div>

            <?php foreach($rows as $row):
                $section  = htmlspecialchars($row['section']);
                $day      = htmlspecialchars($row['day']);
                $startFmt = date("g:i A", strtotime($row['start_time']));
                $endFmt   = date("g:i A", strtotime($row['end_time']));
                $dc       = $dayColors[$row['day']] ?? $dayColors['Monday'];
            ?>
            <div class="sched-card"
                 style="--accent-a:<?= $ac['a'] ?>; --accent-b:<?= $ac['b'] ?>;"
                 data-search="<?= strtolower($subjectName . ' ' . $row['section'] . ' ' . $row['day']) ?>">

                <!-- Section -->
                <div class="section-badge">
                    <div class="s-icon"><i class="fa fa-layer-group"></i></div>
                    Section <?= $section ?>
                </div>

                <!-- Day -->
                <div>
                    <div class="day-chip"
                         style="background:<?= $dc['bg'] ?>; border-color:<?= $dc['border'] ?>; color:<?= $dc['text'] ?>;">
                        <i class="fa fa-calendar-days"></i>
                        <?= $day ?>
                    </div>
                </div>

                <!-- Time -->
                <div class="time-row">
                    <div class="time-icon"><i class="fa fa-clock"></i></div>
                    <span class="time-text"><?= $startFmt ?> &ndash; <?= $endFmt ?></span>
                </div>

                <!-- Actions -->
                <?php if(isset($_SESSION['admin'])): ?>
                <div class="card-actions">
                    <a href="edit_subject.php?id=<?= $row['id'] ?>" class="btn-act btn-edit">
                        <i class="fa fa-pen"></i> Edit
                    </a>
                    <a href="subjects.php?delete_id=<?= $row['id'] ?>"
                       class="btn-act btn-del"
                       onclick="return confirm('Delete this entry?');">
                        <i class="fa fa-trash"></i>
                    </a>
                </div>
                <?php else: ?>
                <div></div>
                <?php endif; ?>

            </div>
            <?php endforeach; ?>

        </div>

    </div>
    <?php endforeach; ?>

    <?php else: ?>
    <div class="empty-state">
        <i class="fa fa-inbox"></i>
        <p>No subjects registered yet.</p>
    </div>
    <?php endif; ?>

</div>

<script>
function filterCards(){
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    document.querySelectorAll('.subject-block').forEach(block => {
        const subj = block.dataset.subject || '';
        let anyVisible = false;
        block.querySelectorAll('.sched-card').forEach(card => {
            const txt = card.dataset.search || '';
            const show = !q || txt.includes(q) || subj.includes(q);
            card.style.display = show ? '' : 'none';
            if(show) anyVisible = true;
        });
        block.style.display = anyVisible ? '' : 'none';
    });
}
</script>

</body>
</html>