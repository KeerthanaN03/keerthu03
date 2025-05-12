<?php
session_start();
if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: student_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['student_id'];
    $student_name = $_SESSION['student_name'];
    $teacher_id = $_POST['teacher_id']; // Ensure this comes from the form
    $course = $_POST['course'];
    $year = $_POST['year'];
    $response = $_POST['response'];

    $teacher_id = $_SESSION['teacher_id'];
    $student_name = $_SESSION['student_name'];

    // Database connection
    $conn = mysqli_connect("localhost", "root", "", "pro");
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Check for duplicate submission
    $query = "SELECT * FROM submitted_assignments WHERE student_id = '$student_id' AND teacher_id = '$teacher_id' AND course = '$course' AND year = '$year'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        echo "You have already submitted this assignment.";
        exit;
    }

    // Insert submission into database
    $submitted_at = date("Y-m-d H:i:s");
    $total_marks = 0;
    $query = "INSERT INTO submitted_assignments ( teacher_id, student_id, student_name, course, year, response, submitted_at, total_marks) 
          VALUES ( '$teacher_id', '$student_id', '$student_name', '$course', '$year', '$response', '$submitted_at', '$total_marks')";

    
    if (mysqli_query($conn, $query)) {
        echo "✅ Assignment successfully submitted! <a href='teacher_dashboard.php'>Back to Dashboard</a>";
    } else {
        echo "⚠️ Error in submission. <a href='write_assignment.php?assignment_id=$assignment_id&teacher_id=$teacher_id'>Back</a>";
    }

    mysqli_close($conn);
}
?>
