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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #1a237e; /* Dark Blue */
            overflow: hidden;
            padding: 5px 15px; /* Smaller padding */
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 5px 10px; /* Smaller padding */
            display: inline-block;
            font-size: 13px; /* Smaller font */
        }

        .navbar a:hover {
            background-color: #3949ab; /* Lighter blue hover */
        }

        .container {
            padding: 20px;
        }

        .dropdown-section {
            display: none;
            margin-top: 20px;
        }
        .logout-btn {
            float: right;
            margin: 8px 0 0 0;
        }

        .logout-btn a {
            text-decoration: none;
            background-color: #c62828; /* Dark Red */
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 13px;
            display: inline-block;
        }
    
        .logout-btn a:hover {
            background-color: #b71c1c; /* Darker red */
        }
    </style>

    <script>
        function showStudentDropdowns() {
            document.getElementById("student-dropdown").style.display = "block";
            document.getElementById("teacher-dropdown").style.display = "none";
        }

        function showTeacherDropdowns() {
            document.getElementById("teacher-dropdown").style.display = "block";
            document.getElementById("student-dropdown").style.display = "none";
        }

        function showYearDropdown() {
            var course = document.getElementById("course").value;
            var yearDiv = document.getElementById("yearDiv");

            if (course !== "") {
                yearDiv.style.display = "block";
            } else {
                yearDiv.style.display = "none";
            }
        }
    </script>
</head>
<body>

    <div class="navbar">
        <a href="#" onclick="showStudentDropdowns()">Student Details</a>
        <a href="#" onclick="showTeacherDropdowns()">Teacher Details</a>
        <div class="logout-btn">
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">

        <!-- Student Dropdown -->
        <div id="student-dropdown" class="dropdown-section">
            <h2>Select Course and Year</h2>
            <form action="student.php" method="POST">
                <label for="course">Course:</label>
                <select name="course" id="course" onchange="showYearDropdown()">
                    <option value="">-- Select Course --</option>
                    <option value="BCA">BCA</option>
                    <option value="BSC">BSC</option>
                    <option value="B.Com">B.Com</option>
                    <option value="BBA">BBA</option>
                </select>

                <div id="yearDiv" style="display:none; margin-top:10px;">
                    <label for="year">Year:</label>
                    <select name="year" id="year">
                        <option value="1st">1st Year</option>
                        <option value="2nd">2nd Year</option>
                        <option value="3rd">3rd Year</option>
                    </select>
                </div>

                <br><br>
                <input type="submit" value="Submit">
            </form>
        </div>

        <!-- Teacher Dropdown -->
        <div id="teacher-dropdown" class="dropdown-section">
            <h2>Select Department</h2>
            <form action="teacher.php" method="POST">
                <label for="department">Department:</label>
                <select name="department" id="department">
                    <option value="">-- Select Department --</option>
                    <option value="BCA">BCA</option>
                    <option value="BBA">BBA</option>
                    <option value="BSC">BSC</option>
                    <option value="B.Com">B.Com</option>
                </select>
                <br><br>
                <input type="submit" value="Submit">
            </form>
        </div>

    </div>
</body>
</html>
