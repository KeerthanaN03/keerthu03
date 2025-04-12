<?php
session_start();
session_destroy();
header("Location: db_connection.php");
exit;
?>