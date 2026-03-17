<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'student') {
    header("Location: login.php");
    exit();
}

echo "<h2>Welcome, ".$_SESSION['username']."</h2>";
echo "<p>This is your student dashboard.</p>";
echo '<a href="logout.php">Logout</a>';
?>