<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$admin_username = $_SESSION['admin_username'];

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "pro";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get selected department
$department = isset($_POST['department']) ? $_POST['department'] : '';

// SQL query
$sql = "
    SELECT 
        t.teacher_id, 
        t.teacher_name, 
        t.password, 
        t.department, 
        COUNT(a.assignment_id) AS assignment_count
    FROM 
        teachers t
    LEFT JOIN 
        assignments a ON t.teacher_id = a.teacher_id
";

if (!empty($department)) {
    $sql .= " WHERE t.department = '" . $conn->real_escape_string($department) . "'";
}

$sql .= " GROUP BY t.teacher_id, t.teacher_name, t.password, t.department
          ORDER BY t.teacher_id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Details</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f8;
            padding: 20px;
        }

        .navbar {
            background-color: #2c3e50;
            padding: 10px 0;
            text-align: center;
        }

        .navbar a {
            color: #ecf0f1;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
            font-size: 18px;
        }

        h2 {
            text-align: center;
            margin: 30px 0 20px;
            color: #2c3e50;
        }

        table {
            width: 90%;
            margin: 0 auto 30px;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 14px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #34495e;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .action-link a {
            background-color: #3498db;
            color: white;
            padding: 8px 14px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
        }

        .action-link a:hover {
            background-color: #2980b9;
        }

        .back-link {
            text-align: center;
            margin-bottom: 30px;
        }

        .back-link a {
            color: #2c3e50;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="teacher.php">Teacher Details</a>
    </div>

    <h2>Teacher Details<?php echo $department ? " - $department Department" : ""; ?></h2>

    <table>
        <tr>
            <th>Teacher ID</th>
            <th>Name</th>
            <th>Password</th>
            <th>Department</th>
            <th>Assigned Assignments</th>
            <th>Action</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['teacher_id']}</td>
                        <td>{$row['teacher_name']}</td>
                        <td>{$row['password']}</td>
                        <td>{$row['department']}</td>
                        <td>{$row['assignment_count']}</td>
                        <td class='action-link'>
                            <a href='edit_teacher.php?id={$row['teacher_id']}'>Edit</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No teachers found in this department.</td></tr>";
        }
        ?>
    </table>

    <div class="back-link">
        <a href="admin_dashboard.php">← Back to Dashboard</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>
