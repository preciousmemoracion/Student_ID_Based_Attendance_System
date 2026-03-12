<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<?php
include "db_connect.php";
$sql = "SELECT * FROM attendance ORDER BY date DESC, time DESC";
$result = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>View Attendance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="index.php">Attendance System</a>
    <div>
      <a class="btn btn-light me-2" href="register_student.php">Register Student</a>
      <a class="btn btn-light" href="view_attendance.php">View Attendance</a>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <h3 class="mb-4">Attendance Records</h3>
  <table class="table table-striped table-bordered">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Student ID</th>
        <th>Date</th>
        <th>Time</th>
      </tr>
    </thead>
    <tbody>
    <?php while($row = mysqli_fetch_assoc($result)){ ?>
      <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo $row['student_id']; ?></td>
        <td><?php echo $row['date']; ?></td>
        <td><?php echo $row['time']; ?></td>
      </tr>
    <?php } ?>
    </tbody>
  </table>
</div>

</body>
</html>

