<?php
include "db_connect.php";

// Default query
$query = "SELECT * FROM attendance WHERE 1";

// Search filter
$search = $_GET['search'] ?? '';
$date = $_GET['date'] ?? '';

if (!empty($search)) {
    $query .= " AND student_id LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
}

if (!empty($date)) {
    $query .= " AND date = '" . mysqli_real_escape_string($conn, $date) . "'";
}

$query .= " ORDER BY date DESC, time DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
<title>View Attendance</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/custom.css">

<style>
  body { background: #f5f6fa; }
  .card { border-radius: 15px; }
  table tr:hover { background-color: #f1f1f1; }
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
  <div class="container">
    <a class="btn btn-light fw-bold shadow-sm rounded-pill d-flex align-items-center" href="dashboard.php">
    <i class="bi bi-speedometer2 me-2"></i> Attendance System
</a>
    <div>
      <a class="btn btn-light me-2" href="register_student.php">Register</a>
      <a class="btn btn-warning" href="dashboard.php">Dashboard</a>
    </div>
  </div>
</nav>

<div class="container mt-4">

  <!-- FILTERS -->
  <div class="card p-3 shadow mb-4">
    <form method="GET" class="row g-3">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control"
               placeholder="Search Student ID"
               value="<?php echo htmlspecialchars($search); ?>">
      </div>

      <div class="col-md-4">
        <input type="date" name="date" class="form-control"
               value="<?php echo htmlspecialchars($date); ?>">
      </div>

      <div class="col-md-4">
        <button class="btn btn-primary w-100">Filter</button>
      </div>
    </form>
  </div>

  <!-- TABLE -->
  <div class="card shadow">
    <div class="card-body">

      <h4 class="mb-3">Attendance Records</h4>

      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Student ID</th>
              <th>Date</th>
              <th>Time</th>
            </tr>
          </thead>
          <tbody>

          <?php if(mysqli_num_rows($result) > 0){ ?>
              <?php while($row = mysqli_fetch_assoc($result)){ ?>
              <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                <td><?php echo $row['date']; ?></td>
                <td><?php echo $row['time']; ?></td>
              </tr>
              <?php } ?>
          <?php } else { ?>
              <tr>
                <td colspan="4" class="text-center text-muted">No records found</td>
              </tr>
          <?php } ?>

          </tbody>
        </table>
      </div>

    </div>
  </div>

</div>

<script src="assets/js/scripts.js"></script>
</body>
</html>