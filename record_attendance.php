<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<?php
include "db_connect.php";

$student_id = $_POST['student_id'];
$date = date("Y-m-d");
$time = date("H:i:s");

// Check if student ID exists
$check = "SELECT * FROM students WHERE student_id = '$student_id'";
$result = mysqli_query($conn, $check);

if(mysqli_num_rows($result) > 0){

    // Student exists, record attendance
    $sql = "INSERT INTO attendance (student_id, date, time)
            VALUES ('$student_id', '$date', '$time')";

    if(mysqli_query($conn,$sql)){
        echo "Attendance Recorded Successfully";
    }else{
        echo "Error: " . mysqli_error($conn);
    }

}else{

    // Student ID not found
    echo "Student ID not registered!";
}
?>
```

