<?php
include "../db_connect.php";

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['admin'])){
    header("Location: ../index.php");
    exit();
}

if(isset($_GET['id'])){
    $id = $_GET['id'];

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id, name, section = ?");
    $stmt->bind_param("sss", $student_id, $name, $section); // "s" for string, use "i" if numeric
    $stmt->execute();
}

header("Location: students.php");
exit();
?>