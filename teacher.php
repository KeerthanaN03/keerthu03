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

$sql = "SELECT teacher_id, teacher_name, password, department FROM teachers";
if (!empty($department)) {
    $sql .= " WHERE department = '" . $conn->real_escape_string($department) . "'";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Details</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table {
            border-collapse: collapse;
            width: 90%;
            margin: 20px auto;
        }

        th, td {
            padding: 12px;
            border: 1px solid #000;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .navbar {
            margin-bottom: 20px;
        }

        .navbar a {
            margin: 0 15px;
        }

        .action-link a {
            text-decoration: none;
            padding: 6px 12px;
            background-color: #4CAF50;
            color: white;
            border-radius: 4px;
        }

        .action-link a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="teacher.php">Teacher Details</a>
    </div>

    <h2 style="text-align:center;">Teacher Details<?php echo $department ? " - $department Department" : ""; ?></h2>

    <table>
        <tr>
            <th>Teacher ID</th>
            <th>Name</th>
            <th>Password</th>
            <th>Department</th>
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
                        <td class='action-link'>
                            <a href='edit_teacher.php?id={$row['teacher_id']}'>Edit</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No teachers found in this department.</td></tr>";
        }
        ?>
    </table>
    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
</body>
</html>

<?php
$conn->close();
?>
