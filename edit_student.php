<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "pro");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";
$original_id = $_GET['student_id'] ?? '';

$stmt = $conn->prepare("SELECT student_id, student_name, password, course, year FROM students WHERE student_id = ?");
$stmt->bind_param("s", $original_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    die("Student not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_id = $_POST['student_id'];
    $name = $_POST['student_name'];
    $password = $_POST['password'];
    $course = $_POST['course'];
    $year = $_POST['year'];

    $stmt = $conn->prepare("UPDATE students SET student_id = ?, student_name = ?, password = ?, course = ?, year = ? WHERE student_id = ?");
    $stmt->bind_param("ssssss", $new_id, $name, $password, $course, $year, $original_id);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: student.php");
        exit;
    } else {
        $error_message = "Error updating student: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
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

        input[type="text"] {
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
        <a href="student.php">Student Details</a>
        <a href="admin_dashboard.php">Dashboard</a>
    </div>

    <div class="card">
        <h2>Edit Student Details</h2>

        <?php if (!empty($error_message)) { ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php } ?>

        <form method="post" action="">
            <label for="student_id">Student ID</label>
            <input type="text" name="student_id" id="student_id" value="<?php echo htmlspecialchars($row['student_id']); ?>" required>

            <label for="student_name">Name</label>
            <input type="text" name="student_name" id="student_name" value="<?php echo htmlspecialchars($row['student_name']); ?>" required>

            <label for="password">Password</label>
            <input type="text" name="password" id="password" value="<?php echo htmlspecialchars($row['password']); ?>" required>

            <label for="course">Course</label>
            <input type="text" name="course" id="course" value="<?php echo htmlspecialchars($row['course']); ?>" required>

            <label for="year">Year</label>
            <input type="text" name="year" id="year" value="<?php echo htmlspecialchars($row['year']); ?>" required>

            <input type="submit" value="Update Student">
        </form>
    </div>

    <div class="back-link">
        <a href="student.php">← Back to Student List</a>
    </div>

</body>
</html>
