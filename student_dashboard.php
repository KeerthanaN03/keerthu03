<?php
session_start();

if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Student Dashboard</title>
    <style>
        /* Reset and basics */
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }

        /* Navbar styling */
        nav {
            background-color: #2c3e50;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        nav ul li {
            margin-right: 20px;
        }

        nav ul li:last-child {
            margin-right: 0;
        }

        nav ul li a {
            color: #ecf0f1;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        nav ul li a:hover,
        nav ul li a:focus {
            background-color: #34495e;
            outline: none;
        }

        /* Logout button */
        .logout a {
            color: #ecf0f1;
            background-color: #e74c3c;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .logout a:hover,
        .logout a:focus {
            background-color: #c0392b;
            outline: none;
        }

        /* Responsive for small screens */
        @media (max-width: 480px) {
            nav {
                flex-direction: column;
                align-items: flex-start;
            }
            nav ul {
                flex-direction: column;
                width: 100%;
            }
            nav ul li {
                margin-bottom: 10px;
                margin-right: 0;
                width: 100%;
            }
            nav ul li a {
                display: block;
                width: 100%;
            }
            .logout {
                margin-top: 10px;
                width: 100%;
                text-align: left;
            }
            .logout a {
                width: 100%;
                display: block;
            }
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="received_marks.php">Received Marks</a></li>
            <li><a href="view_questions.php">View Generated Questions</a></li>
        </ul>
        <div class="logout">
            <a href="student_logout.php" tabindex="0">Logout</a>
        </div>
    </nav>
</body>
</html>
