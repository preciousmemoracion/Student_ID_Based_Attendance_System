<?php
include "db_connect.php";

$msg = "";

if(isset($_POST['student_id'])){
    $student_id = trim($_POST['student_id']);
    $date = date("Y-m-d");
    $time = date("H:i:s");

    if(!empty($student_id)){

        // Check if student exists
        $stmt = $conn->prepare("SELECT name FROM students WHERE student_id = ?");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){
            $student = $result->fetch_assoc();
            $student_name = $student['name'];

            // Check duplicate
            $stmt2 = $conn->prepare("SELECT * FROM attendance WHERE student_id = ? AND date = ?");
            $stmt2->bind_param("ss", $student_id, $date);
            $stmt2->execute();
            $dup_result = $stmt2->get_result();

            if($dup_result->num_rows > 0){
                $msg = '<div class="alert alert-warning text-center">⚠ Attendance already recorded today!</div>';
            } else {

                // Insert attendance
                $stmt3 = $conn->prepare("INSERT INTO attendance (student_id, date, time) VALUES (?, ?, ?)");
                $stmt3->bind_param("sss", $student_id, $date, $time);

                if($stmt3->execute()){
                    $msg = '<div class="alert alert-success text-center">
                            ✅ Attendance recorded for <strong>'.$student_name.'</strong>
                           </div>';
                } else {
                    $msg = '<div class="alert alert-danger text-center">❌ Error saving attendance</div>';
                }
            }

        } else {
            $msg = '<div class="alert alert-danger text-center">❌ Student not registered!</div>';
        }

    } else {
        $msg = '<div class="alert alert-warning text-center">⚠ Please enter Student ID</div>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Record Attendance</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
    height: 100vh;
}
.card {
    border-radius: 15px;
}
</style>

</head>
<body>

<nav class="navbar navbar-dark bg-primary shadow">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Attendance System</a>
    <div>
      <a class="btn btn-light me-2" href="register_student.php">Register</a>
      <a class="btn btn-light me-2" href="view_attendance.php">Records</a>
      <a class="btn btn-warning" href="dashboard.php">Dashboard</a>
    </div>
  </div>
</nav>

<div class="container d-flex justify-content-center align-items-center" style="height:80vh;">
  <div class="card p-4 shadow-lg" style="width:400px;">

    <h3 class="text-center mb-3">📋 Record Attendance</h3>

    <!-- MESSAGE -->
    <?php if($msg != "") echo $msg; ?>

    <!-- FORM -->
    <form method="POST">
      <div class="mb-3">
        <input type="text" name="student_id" class="form-control text-center"
               placeholder="Enter Student ID"
               autofocus required>
      </div>

      <button type="submit" class="btn btn-success w-100">
        ✔ Submit Attendance
      </button>
    </form>

  </div>
</div>

</body>
</html>