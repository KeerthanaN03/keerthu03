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
        .form-container {
            width: 50%;
            margin: 0 auto;
        }

        .form-container label {
            display: block;
            margin-bottom: 8px;
        }

        .form-container input, .form-container select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-container input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .form-container input[type="submit"]:hover {
            background-color: #45a049;
        }

        .navbar a {
            margin: 0 15px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="teacher.php">Teacher Details</a>
    </div>

    <h2 style="text-align:center;">Edit Teacher Details</h2>

    <div class="form-container">
        <?php if (!empty($error_message)) { ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
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

    <p style="text-align:center;"><a href="teacher.php">Back to Teacher Details</a></p>
</body>
</html>

<?php
$conn->close();
?>
