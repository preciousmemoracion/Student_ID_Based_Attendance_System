<?php 
include "../db_connect.php";
session_start();

if(!isset($_SESSION['student'])){
    header("Location: ../student_login.php");
    exit();
}

$id = $_SESSION['student'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #f1f5f9;
}
.navbar {
    background: #2563eb;
}
</style>

</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark">
<div class="container">
    <span class="navbar-brand d-flex align-items-center">
        <img src="../img/icas_logo.jpeg" style="width:40px; border-radius:50%; margin-right:10px;">
        Student Panel
    </span>

    <span class="text-white">
        ID: <?= $id ?>
    </span>

    <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
</div>
</nav>

<div class="container mt-4">

<h4>Your Attendance Records</h4>

<div class="card mt-3 p-3 shadow">

<table class="table table-striped text-center">
<tr>
<th>Date</th>
<th>Time</th>
</tr>

<?php
$res = $conn->query("SELECT * FROM attendance WHERE student_id='$id' ORDER BY id DESC");

while($r = $res->fetch_assoc()){
?>
<tr>
<td><?= $r['date'] ?></td>
<td><?= $r['time'] ?></td>
</tr>
<?php } ?>

</table>

</div>

</div>

</body>
</html>