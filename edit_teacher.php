<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$admin_username = $_SESSION['admin_username'];

$host = "localhost";
$user = "root";
$password = "";
$dbname = "pro";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";
$teacher_id = $_GET['id'] ?? '';

$sql = "SELECT * FROM teachers WHERE teacher_id = '$teacher_id'";
$result = $conn->query($sql);
$teacher = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher_name = mysqli_real_escape_string($conn, $_POST["teacher_name"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);
    $department = mysqli_real_escape_string($conn, $_POST["department"]);

    $update_sql = "UPDATE teachers SET teacher_name='$teacher_name', password='$password', department='$department' WHERE teacher_id='$teacher_id'";

    if ($conn->query($update_sql) === TRUE) {
        header("Location: teacher.php");
        exit;
    } else {
        $error_message = "Error updating teacher details: " . $conn->error;
    }
}

$department_sql = "SELECT DISTINCT department FROM teachers";
$department_result = $conn->query($department_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Teacher</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #343a40;
            padding: 1rem;
            text-align: center;
        }

        .navbar a {
            color: #ffffff;
            margin: 0 15px;
            text-decoration: none;
            font-weight: 500;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        .card {
            max-width: 500px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px 40px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        input[type="text"], select {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            text-decoration: none;
            color: #007bff;
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

    <div class="card">
        <h2>Edit Teacher Details</h2>

        <?php if (!empty($error_message)) { ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php } ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=$teacher_id"); ?>">
            <label for="teacher_name">Teacher Name</label>
            <input type="text" name="teacher_name" id="teacher_name" value="<?php echo htmlspecialchars($teacher['teacher_name']); ?>" required>

            <label for="password">Password</label>
            <input type="text" name="password" id="password" value="<?php echo htmlspecialchars($teacher['password']); ?>" required>

            <label for="department">Department</label>
            <select name="department" id="department" required>
                <?php while ($row = $department_result->fetch_assoc()) { ?>
                    <option value="<?php echo $row['department']; ?>" <?php if ($teacher['department'] == $row['department']) echo "selected"; ?>>
                        <?php echo $row['department']; ?>
                    </option>
                <?php } ?>
            </select>

            <input type="submit" value="Update Details">
        </form>
    </div>

    <div class="back-link">
        <a href="teacher.php">← Back to Teacher Details</a>
    </div>

</body>
</html>

<?php
$conn->close();
?>
