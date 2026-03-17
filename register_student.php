<?php
include "db_connect.php";

$msg = "";

if(isset($_POST['submit'])){

    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $course = trim($_POST['course']);
    $year_level = $_POST['year_level'];

    // Check empty fields
    if(empty($student_id) || empty($name) || empty($course) || empty($year_level)){
        $msg = '<div class="alert alert-warning text-center">⚠ All fields are required!</div>';
    } else {

        // Check duplicate student ID
        $check = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
        $check->bind_param("s", $student_id);
        $check->execute();
        $result = $check->get_result();

        if($result->num_rows > 0){
            $msg = '<div class="alert alert-danger text-center">❌ Student ID already exists!</div>';
        } else {

            // Insert student
            $stmt = $conn->prepare("INSERT INTO students (student_id, name, course, year_level) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $student_id, $name, $course, $year_level);

            if($stmt->execute()){
                $msg = '<div class="alert alert-success text-center">✅ Student registered successfully!</div>';
            } else {
                $msg = '<div class="alert alert-danger text-center">❌ Error saving data</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register Student</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #36b9cc, #4e73df);
    height: 100vh;
}
.card {
    border-radius: 15px;
}
</style>

</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-primary shadow">
  <div class="container">
<a class="navbar-brand d-flex align-items-center fw-bold text-white" href="dashboard.php">
    <img src="assets\img\icas_logo.jpeg" width="50px" height="50px" class="me-3 rounded-circle shadow-sm" alt="ICAS Logo">
    <span class="fs-4">Attendance System</span>
</a>
</a>
    <div>
      <a class="btn btn-light me-2" href="view_attendance.php">Records</a>
      <a class="btn btn-warning" href="dashboard.php">Dashboard</a>
    </div>
  </div>
</nav>

<!-- FORM -->
 
<div class="container d-flex justify-content-center align-items-center" style="height:85vh;">
  <div class="card shadow-lg p-4" style="width:450px;">

    <h3 class="text-center mb-3">🎓 Register Student</h3>

    <!-- MESSAGE -->
    <?php if($msg != "") echo $msg; ?>

    <form method="POST">

      <div class="mb-3">
        <input type="text" name="student_id" class="form-control"
               placeholder="Student ID" required>
      </div>

      <div class="mb-3">
        <input type="text" name="name" class="form-control"
               placeholder="Full Name" required>
      </div>

      <div class="mb-3">
        <input type="text" name="course" class="form-control"
               placeholder="Course (e.g. BSIT)" required>
      </div>

      <div class="mb-3">
        <select name="year_level" class="form-control" required>
          <option value="">Select Year Level</option>
          <option value="1">1st Year</option>
          <option value="2">2nd Year</option>
          <option value="3">3rd Year</option>
          <option value="4">4th Year</option>
        </select>
      </div>

      <button type="submit" name="submit" class="btn btn-primary w-100">
        ✔ Register Student
      </button>

    </form>

  </div>
</div>

</body>
</html>