<?php 
include "../db_connect.php";

// Start session
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// Check admin login
if(!isset($_SESSION['admin'])){
    header("Location: ../index.php");
    exit();
}

// Get student ID from URL
if(!isset($_GET['id'])){
    header("Location: students.php");
    exit();
}

$student_id = $_GET['id'];
$message = "";

// Fetch existing student data
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    $message = "<div class='alert alert-danger text-center'>Student not found!</div>";
    $student = null;
} else {
    $student = $result->fetch_assoc();
}

// Process form submission
if(isset($_POST['update'])){
    $name = trim($_POST['name']);
    $section = trim($_POST['section']);

    if(empty($name) || empty($section)){
        $message = "<div class='alert alert-danger text-center'>All fields are required!</div>";
    } else {
        $stmt = $conn->prepare("UPDATE students SET name=?, section=? WHERE student_id=?");
        $stmt->bind_param("sss", $name, $section, $student_id);
        $stmt->execute();
        $message = "<div class='alert alert-success text-center'>Student updated successfully!</div>";

        // Refresh student info
        $student['name'] = $name;
        $student['section'] = $section;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: url('../img/icas.jpeg') no-repeat center center/cover;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            color: #fff;
            margin-top: 20px;
        }
        .card:hover {
            transform: scale(1.03);
            box-shadow: 0 12px 40px rgba(0,0,0,0.5);
        }
        .input-group-text {
            background: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>

<div class="container mt-5 col-md-5">
    <div class="card p-4">
        <h4 class="text-center mb-3"><i class="fa fa-edit"></i> Edit Student</h4>

        <?php if($message != "") echo $message; ?>

        <?php if($student): ?>
        <form method="POST">
            <!-- Student ID (read-only) -->
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fa fa-id-card"></i></span>
                <input type="text" class="form-control" value="<?= $student['student_id'] ?>" readonly>
            </div>

            <!-- Name -->
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fa fa-user"></i></span>
                <input type="text" name="name" class="form-control" value="<?= $student['name'] ?>" required>
            </div>

            <!-- Section -->
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fa fa-users"></i></span>
                <input type="text" name="section" class="form-control" value="<?= $student['section'] ?>" required>
            </div>

            <button name="update" class="btn btn-success w-100"><i class="fa fa-save"></i> Update</button>
        </form>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="students.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to Students</a>
        </div>
    </div>
</div>

</body>
</html>