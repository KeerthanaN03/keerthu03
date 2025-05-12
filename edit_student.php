<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "pro");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get student ID from URL
if (isset($_GET['student_id'])) {
    $original_id = $_GET['student_id'];

    // Fetch student details
    $stmt = $conn->prepare("SELECT student_id, student_name, password, course, year FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $original_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        die("Student not found.");
    }
    $stmt->close();
} else {
    die("No student ID provided.");
}

// If form is submitted to update student details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_id = $_POST['student_id'];
    $name = $_POST['student_name'];
    $password = $_POST['password'];
    $course = $_POST['course'];
    $year = $_POST['year'];

    // Update student in database
    $stmt = $conn->prepare("UPDATE students SET student_id = ?, student_name = ?, password = ?, course = ?, year = ? WHERE student_id = ?");
    $stmt->bind_param("ssssss", $new_id, $name, $password, $course, $year, $original_id);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: student.php");
        exit;
    } else {
        echo "Error updating student: " . $conn->error;
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
            font-family: Arial, sans-serif;
        }

        .form-container {
            width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-container label {
            display: block;
            margin-top: 10px;
        }

        .form-container input[type="text"],
        .form-container input[type="submit"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }

        .form-container input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            margin-top: 20px;
            cursor: pointer;
        }

        .form-container input[type="submit"]:hover {
            background-color: #45a049;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #333;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Student Details</h2>
    <form method="post" action="">
        <label for="student_id">Student ID</label>
        <input type="text" name="student_id" id="student_id" value="<?php echo htmlspecialchars($row['student_id']); ?>" required>

        <label for="student_name">Student Name</label>
        <input type="text" name="student_name" id="student_name" value="<?php echo htmlspecialchars($row['student_name']); ?>" required>

        <label for="password">Password</label>
        <input type="text" name="password" id="password" value="<?php echo htmlspecialchars($row['password']); ?>" required>

        <label for="course">Course</label>
        <input type="text" name="course" id="course" value="<?php echo htmlspecialchars($row['course']); ?>" required>

        <label for="year">Year</label>
        <input type="text" name="year" id="year" value="<?php echo htmlspecialchars($row['year']); ?>" required>

        <input type="submit" value="Update Student">
    </form>

    <div class="back-link">
        <a href="student.php">← Back to Student List</a>
    </div>
</div>

</body>
</html>
