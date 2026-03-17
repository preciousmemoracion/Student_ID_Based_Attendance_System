<?php
session_start();
include "db_connect.php";

// Restrict access to admin only
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get filters
$date = $_GET['date'] ?? date('Y-m-d');
$search = $_GET['search'] ?? '';

// Base SQL
$sql = "SELECT s.id, s.name, s.course, a.attendance_date
        FROM students s
        JOIN attendance a ON s.id = a.student_id
        WHERE 1";

// Add date filter
if(!empty($date)){
    $sql .= " AND a.attendance_date = ?";
    $params[] = $date;
    $types = "s";
} else {
    $params = [];
    $types = "";
}

// Add search filter
if(!empty($search)){
    $sql .= " AND (s.name LIKE ? OR s.id LIKE ?)";
    $likeSearch = "%$search%";
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $types .= "ss";
}

$sql .= " ORDER BY s.name ASC";

// Prepare statement
$stmt = $conn->prepare($sql);

if(!empty($params)){
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f6fa; }
        .container { margin-top: 40px; }
        .table th { background: #4CAF50; color: white; }
        .table tr:hover { background: #e2f0d9; }
        .filters { margin-bottom: 20px; }
        .logout { float: right; color: red; text-decoration: none; }
        .highlight-today { background-color: #fff3cd !important; }
    </style>
</head>
<body>
<div class="container">
    <h2>Attendance Records <a href="logout.php" class="logout btn btn-outline-danger btn-sm">Logout</a></h2>

    <!-- Filters -->
    <form method="GET" class="row g-2 filters">
        <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date); ?>">
        </div>
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by Name or ID" 
                   value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-3">
            <a href="view_attendance.php" class="btn btn-secondary w-100">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="<?php echo ($row['attendance_date'] == date('Y-m-d')) ? 'highlight-today' : ''; ?>">
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['course']); ?></td>
                            <td><?php echo htmlspecialchars($row['attendance_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            No records found<?php echo !empty($date) ? " for " . htmlspecialchars($date) : ""; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>