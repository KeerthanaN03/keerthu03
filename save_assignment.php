<?php
session_start();

// Check if teacher is logged in
if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true) {
    header("Location: teacher_login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$teacher_department = $_SESSION['teacher_department'];

$conn = new mysqli("localhost", "root", "", "pro");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Only handle POST submission with assignment data
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['assign'])) {
    $course = $_POST['course'];
    $year = $_POST['year'];
    $question = $_POST['question'];
    $deadline = $_POST['deadline'];
    $total_marks = $_POST['total_marks'];
    $selected_students = $_POST['students'] ?? [];

    // Department validation
    if ($course !== $teacher_department) {
        die("<p style='color:red;'>Access Denied: Only allowed to assign students from your department (" . htmlspecialchars($teacher_department) . ").</p>");
    }

    $success_count = 0;
    $stmt = $conn->prepare("SELECT student_name FROM students WHERE student_id = ? AND course = ?");
    $insert = $conn->prepare("INSERT INTO assignments (student_id, student_name, course, year, assignment, teacher_id, deadline, total_marks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($selected_students as $student_id) {
        $stmt->bind_param("ss", $student_id, $teacher_department);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $student_name = $row['student_name'];

            $insert->bind_param("sssssssi", $student_id, $student_name, $course, $year, $question, $teacher_id, $deadline, $total_marks);
            $insert->execute();
            $success_count++;
        }
    }

    $stmt->close();
    $insert->close();
    $conn->close();

    echo "✅ Assignment successfully assigned! <a href='teacher_dashboard.php'>Back to Dashboard</a>";
} else {
    echo "⚠️ No students selected. <a href='teacher_dashboard.php'>Back</a>";
}

?>
