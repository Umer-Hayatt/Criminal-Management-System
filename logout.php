<?php
include 'db.php';
log_activity('Logout', 'User', $_SESSION['user_id'] ?? 0, ($_SESSION['username'] ?? '') . ' logged out');
session_destroy();
header('Location: login.php');
exit();
