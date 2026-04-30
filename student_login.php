<?php 
include "db_connect.php"; 
//session_start();
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
    height: 100vh;
}
.card {
    border-radius: 15px;
}
.logo {
    width: 80px;
}
</style>
</head>

<body class="d-flex justify-content-center align-items-center" style="height:100vh; background: linear-gradient(135deg, #0ea5e9, #2563eb);">

<div class="col-md-4">
<div class="card p-4 shadow">

<div class="text-center">
    <img src="img/icas_logo.jpeg" class="logo mb-2">
    <h4>Student Login</h4>
</div>

<form method="POST">

<div class="mb-3">
<label>Student ID</label>
<input type="text" name="student_id" class="form-control" required>
</div>




<button name="login" class="btn btn-primary w-100">Login</button>

</form>

<?php
if(isset($_POST['login'])){
    $id = $_POST['student_id'];
   // $pass = md5($_POST['password']);

    $res = $conn->query("SELECT * FROM students WHERE student_id='$id' ");

    if($res->num_rows > 0){
        $_SESSION['student'] = $id;
        header("Location: student/dashboard.php");
        exit();
    } else {
        echo "<div class='alert alert-danger mt-3 text-center'>Invalid Login</div>";
    }
}
?>

</div>
</div>

</body>
</html>