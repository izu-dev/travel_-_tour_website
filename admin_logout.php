<?php
session_start();

// Unset admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

// Redirect to admin login page
header("Location: admin_login.php");
exit();
?>