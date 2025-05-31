<?php
session_start();

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Assignment Report</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: #2c3e50;
        }

        /* Small professional navbar */
        .navbar {
            background-color: #1f3b5b;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            color: white;
        }

        .navbar h1 {
            font-size: 20px;
            margin: 0;
        }

        .navbar a {
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
            margin-left: 20px;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-top: 0;
            color: #1f3b5b;
            font-size: 24px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 16px;
        }

        th, td {
            padding: 14px 12px;
            text-align: left;
        }

        th {
            background-color: #1f3b5b;
            color: white;
            text-transform: uppercase;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #eef5ff;
        }

        a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            color: #c0392b;
            font-weight: bold;
            font-size: 16px;
        }

        .btn-back {
            margin-top: 20px;
            display: inline-block;
            background-color: #1f3b5b;
            color: #fff;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s;
        }

        .btn-back:hover {
            background-color: #16314a;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>Admin Panel</h1>
    <div>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course = $_POST['course'];
    $year = $_POST['year'];

    if (empty($course) || empty($year)) {
        echo "<p class='error'>Please select both Course and Year.</p>";
        exit;
    }

    $conn = new mysqli("localhost", "root", "", "pro");
    if ($conn->connect_error) {
        die("<p class='error'>Connection failed: " . $conn->connect_error . "</p>");
    }

    $sql = "
        SELECT 
            s.student_id, 
            s.student_name, 
            s.course, 
            s.year,
            COUNT(a.assignment_id) AS assignment_count
        FROM 
            students s
        LEFT JOIN 
            assignments a ON s.student_id = a.student_id
        WHERE 
            s.course = ? AND s.year = ?
        GROUP BY 
            s.student_id, s.student_name, s.course, s.year
        ORDER BY 
            s.student_id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $course, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h2>Students in <strong>$course - $year</strong></h2>";

    if ($result->num_rows > 0) {
        echo "<table>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Assigned Assignments</th>
                    <th>Action</th>
                </tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['student_id']}</td>
                    <td>{$row['student_name']}</td>
                    <td>{$row['course']}</td>
                    <td>{$row['year']}</td>
                    <td>{$row['assignment_count']}</td>
                    <td><a href='edit_student.php?student_id={$row['student_id']}'>Edit</a></td>
                </tr>";
        }

        echo "</table>";
    } else {
        echo "<p class='error'>No students found for <strong>$course - $year</strong>.</p>";
    }

    echo "<a href='admin_dashboard.php' class='btn-back'>&larr; Back to Dashboard</a>";

    $stmt->close();
    $conn->close();
} else {
    echo "<p class='error'>Invalid request method.</p>";
}
?>
</div>

</body>
</html>
