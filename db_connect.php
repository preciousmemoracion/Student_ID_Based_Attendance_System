<?php
$conn = new mysqli("localhost", "root", "", "attendance_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// FIX SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>