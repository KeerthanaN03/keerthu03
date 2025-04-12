<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$admin_username = $_SESSION['admin_username'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="admin_dashboard.php">Home</a>
        <a href="teacher.php">Teacher</a>
        <a href="student.php">Student</a>
        <a href="class.php">Class</a>
        <a href="manage_admins.php">Admin</a>
    </div>
    <button>
        <a href="logout.php">Logout</a>
</button>

    </body>
</html>