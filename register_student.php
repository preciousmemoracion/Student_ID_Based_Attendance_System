<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<?php
include "db_connect.php";

if(isset($_POST['submit'])){
    $student_id = $_POST['student_id'];
    $name = $_POST['name'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];

    $sql = "INSERT INTO students (student_id, name, course, year_level)
            VALUES ('$student_id', '$name', '$course', '$year_level')";

    if(mysqli_query($conn,$sql)){
        $msg = "Student Registered Successfully!";
    } else {
        $msg = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register Student</title>
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
  <div class="card p-4 shadow">
    <h3 class="text-center mb-4">Register New Student</h3>

    <?php if(isset($msg)) { ?>
      <div class="alert alert-info"><?php echo $msg; ?></div>
    <?php } ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Student ID</label>
        <input type="text" class="form-control" name="student_id" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" class="form-control" name="name" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Course</label>
        <input type="text" class="form-control" name="course" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Year Level</label>
        <input type="number" class="form-control" name="year_level" required>
      </div>
      <button type="submit" name="submit" class="btn btn-primary w-100">Register Student</button>
    </form>
  </div>
</div>

</body>
</html>