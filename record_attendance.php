<?php
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $date = date("Y-m-d");
    $time = date("H:i:s");

    $check = $conn->query("SELECT * FROM students WHERE student_id='$student_id'");

    if ($check->num_rows > 0) {
        $sql = "INSERT INTO attendance (student_id, date, time)
                VALUES ('$student_id', '$date', '$time')";
        $conn->query($sql);
        echo "Attendance Recorded!";
    } else {
        echo "Student not found!";
    }
}
?>

<form method="POST">
    <input type="text" name="student_id" placeholder="Enter Student ID" required>
    <button type="submit">Submit</button>
</form>