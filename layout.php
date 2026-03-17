<?php
// Start session if needed
// session_start();

// Make sure $pageContent is defined to avoid errors
if (!isset($pageContent)) {
    $pageContent = '';
}

// Get root folder dynamically
$root = dirname($_SERVER['SCRIPT_NAME'], 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Attendance System'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $root; ?>/assets/css/styles.css" rel="stylesheet">
</head>
<body>

<!-- Navbar with Logo -->
<nav class="navbar navbar-dark bg-primary">
    <div class="container-fluid">
        <div class="navbar-brand d-flex align-items-center fw-bold text-white">
            <img src="<?php echo $root; ?>/assets/img/icas_logo.png" width="50" height="50" class="me-3 rounded-circle shadow-sm" alt="ICAS Logo">
            <span class="fs-4">Attendance System</span>
        </div>

        <div class="d-flex">
            <a class="btn btn-outline-light me-2" href="<?php echo $root; ?>/index.php">Home</a>
            <a class="btn btn-outline-light me-2" href="<?php echo $root; ?>/dashboard.php">Dashboard</a>
            <a class="btn btn-outline-light" href="<?php echo $root; ?>/logout.php">Logout</a>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container mt-4">
    <?php echo $pageContent; ?>
</div>

<!-- Footer -->
<footer class="bg-primary text-white text-center py-3 mt-4">
    &copy; <?php echo date("Y"); ?> Attendance System
</footer>

<!-- Bootstrap JS + Custom JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $root; ?>/assets/js/scripts.js"></script>
</body>
</html> 