<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<?php
include "db_connect.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="#">Attendance System</a>
    <div>
      <a class="btn btn-light me-2" href="register_student.php">Register Student</a>
      <a class="btn btn-light" href="view_attendance.php">View Attendance</a>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <div class="card p-4 shadow">
    <h3 class="text-center mb-4">Record Attendance</h3>
    <form action="record_attendance.php" method="POST">
      <div class="mb-3">
        <label for="student_id" class="form-label">Student ID</label>
        <input type="text" class="form-control" name="student_id" placeholder="Enter Student ID" required>
      </div>
      <button type="submit" class="btn btn-success w-100">Submit Attendance</button>
    </form>
  </div>
</div>

</body>
</html>