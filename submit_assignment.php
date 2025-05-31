<?php
session_start();

if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: student_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "❌ Invalid request method.";
    exit;
}

if (!isset($_POST['assignment_id'], $_POST['response'])) {
    echo "❌ Missing assignment or response.";
    exit;
}

$student_id = $_SESSION['student_id'];
$response = $_POST['response'];
$assignment_id = intval($_POST['assignment_id']);

$conn = new mysqli("localhost", "root", "", "pro");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch assignment details from assignments table by assignment_id and student_id
$sql = "SELECT teacher_id, student_id, student_name, course, year, total_marks 
        FROM assignments 
        WHERE assignment_id = ? AND student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $assignment_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();
$assignment = $result->fetch_assoc();

if (!$assignment) {
    echo "❌ Assignment not found or access denied.";
    $stmt->close();
    $conn->close();
    exit;
}

// Prepare to insert the submission
$insert_sql = "INSERT INTO submitted_assignments 
    (teacher_id, student_id, student_name, course, year, response, submitted_at, total_marks)
    VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";

$insert_stmt = $conn->prepare($insert_sql);
if (!$insert_stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

// Extract values from fetched assignment row
$teacher_id = $assignment['teacher_id'];
$student_name = $assignment['student_name'];
$course = $assignment['course'];
$year = $assignment['year'];
$total_marks = $assignment['total_marks'];

// Bind params: i=int, s=string; total_marks assumed int
$insert_stmt->bind_param(
    "ssssssi",
    $teacher_id,
    $student_id,
    $student_name,
    $course,
    $year,
    $response,
    $total_marks
);

// Execute insert
if ($insert_stmt->execute()) {
    echo "✅ Assignment submitted successfully.";
} else {
    echo "❌ Failed to submit assignment: " . $insert_stmt->error;
}

// Cleanup
$insert_stmt->close();
$stmt->close();
$conn->close();
?>