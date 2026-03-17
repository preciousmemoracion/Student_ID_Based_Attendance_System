<?php
// Get the root URL of the project dynamically
$root = dirname($_SERVER['SCRIPT_NAME'], 1); 
?>

<div class="navbar-brand d-flex align-items-center fw-bold text-white">
    <img src="<?php echo $root; ?>/assets/img/icas_logo.png" width="50" height="50" class="me-3 rounded-circle shadow-sm" alt="ICAS Logo">
    <span class="fs-4">Attendance System</span>
</div>