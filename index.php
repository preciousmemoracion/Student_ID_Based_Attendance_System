
<?php
session_start();
include "db_connect.php";

// 🚫 Prevent caching (important for logout/back button behavior)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$error = "";

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = md5($_POST['password']); // ⚠️ use only if DB uses md5

    // ⚠️ Make sure your table is correct: "admins"
    $query = $conn->query("SELECT * FROM admins WHERE username='$username' AND password='$password'");

    if($query && $query->num_rows > 0){
        $admin = $query->fetch_assoc();

        session_regenerate_id(true); // 🔥 security
        $_SESSION['admin'] = $admin['id'];

        header("Location: admin/dashboard.php");
        exit();
    } else {
        $error = "Invalid Login";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Instructor Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    height: 100vh;
    margin: 0;
    background: url('img/icas.jpeg') no-repeat center center/cover;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Segoe UI', sans-serif;
}
.card {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(12px);
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.3);
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    color: #fff;
    animation: fadeInUp 0.8s ease;
}
.logo {
    width: 80px;
    border-radius: 50%;
    margin-bottom: 10px;
}
.form-control {
    border-radius: 10px;
    transition: 0.3s;
}
.form-control:focus {
    box-shadow: 0 0 10px rgba(78,115,223,0.5);
    transform: scale(1.02);
}
.btn-primary {
    border-radius: 10px;
    transition: 0.3s;
}
.btn-primary:hover {
    transform: scale(1.05);
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}
.alert {
    animation: shake 0.4s;
}
@keyframes shake {
    0% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    50% { transform: translateX(5px); }
    75% { transform: translateX(-5px); }
    100% { transform: translateX(0); }
}
</style>
</head>

<body>

<div class="col-md-4">
    <div class="card p-4 shadow text-center">

        <!-- LOGO -->
        <img src="img/icas_logo.jpeg" class="logo mx-auto">
        <h4 class="mb-3">Instructor Login</h4>

        <!-- FORM -->
        <form method="POST">
            <div class="mb-3 text-start">
                <label>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required>
            </div>

            <div class="mb-3 text-start">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>

            <button name="login" class="btn btn-primary w-100">Login</button>
        </form>

        <!-- ERROR -->
        <?php if($error): ?>
            <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>    