<?php
include "db_connect.php";

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get total students
$result1 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM students");
$total_students = mysqli_fetch_assoc($result1)['total'] ?? 0;

// Get today's attendance
$today = date("Y-m-d");
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM attendance WHERE date = ?");
$stmt->bind_param("s", $today);
$stmt->execute();
$result2 = $stmt->get_result();
$today_attendance = $result2->fetch_assoc()['total'] ?? 0;

// Get recent attendance
$recent = mysqli_query($conn, "SELECT * FROM attendance ORDER BY date DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/custom.css">
</head>

<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">
        <a class="navbar-brand d-flex align-items-center fw-bold text-white" href="dashboard.php">
    <img src="assets/img/icas_logo.jpeg" width="50" height="50" class="me-3 rounded-circle shadow-sm" alt="ICAS Logo">
    <span class="fs-4">Attendance System</span>
</a>
    </a>

    <div>
      <a class="btn btn-light me-2" href="register_student.php">Register</a>
      <a class="btn btn-light me-2" href="view_attendance.php">Attendance</a>
      <a class="btn btn-warning" href="index.php">Quick Scan</a>
    </div>
  </div>
</nav>

<div class="container mt-4">

  <!-- CLOCK -->
  <div class="text-end mb-3">
    <h5 id="clock" class="text-muted"></h5>
  </div>

  <!-- STATS -->
  <div class="row g-4 mb-4">
    
    <div class="col-md-6">
      <div class="card bg-success text-white shadow-lg border-0">
        <div class="card-body">
          <h5><i class="bi bi-people-fill"></i> Total Students</h5>
          <h2><?php echo $total_students; ?></h2>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card bg-info text-white shadow-lg border-0">
        <div class="card-body">
          <h5><i class="bi bi-check-circle-fill"></i> Today's Attendance</h5>
          <h2><?php echo $today_attendance; ?></h2>
        </div>
      </div>
    </div>

  </div>

  <!-- QUICK ATTENDANCE -->
  <div class="card shadow mb-4">
    <div class="card-body">
      <h4><i class="bi bi-pencil-square"></i> Quick Attendance</h4>

      <form action="record_attendance.php" method="POST" class="row g-3 mt-2">
        <div class="col-md-8">
          <input type="text" class="form-control" name="student_id" placeholder="Enter Student ID" required>
        </div>
        <div class="col-md-4">
          <button type="submit" class="btn btn-success w-100">
            <i class="bi bi-check-lg"></i> Submit
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- RECENT ATTENDANCE -->
  <div class="card shadow">
    <div class="card-body">
      <h4><i class="bi bi-clock-history"></i> Recent Attendance</h4>

      <table class="table table-bordered mt-3">
        <thead class="table-dark">
          <tr>
            <th>Student ID</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = mysqli_fetch_assoc($recent)) { ?>
            <tr>
              <td><?php echo $row['student_id']; ?></td>
              <td><?php echo $row['date']; ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>

    </div>
  </div>

</div>

<!-- JS -->
<script>
function updateClock() {
    let now = new Date();
    document.getElementById("clock").innerHTML = now.toLocaleString();
}
setInterval(updateClock, 1000);
updateClock();
</script>

</body>
</html>    