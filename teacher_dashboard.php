<?php
session_start();

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true) {
    header("Location: teacher_login.php");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
</head>
<body>
    <h2>Welcome, Teacher!</h2>

    <p><a href="teacher_logout.php">Logout</a></p>
    </body>
</html>