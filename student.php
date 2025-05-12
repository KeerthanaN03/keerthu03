<?php
session_start();

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// If form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course = $_POST['course'];
    $year = $_POST['year'];

    if (empty($course) || empty($year)) {
        die("Please select both Course and Year.");
    }

    // Connect to the 'pro' database
    $conn = new mysqli("localhost", "root", "", "pro");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Use LEFT JOIN to get students even if they have no assignments
    $sql = "
        SELECT 
            s.student_id, 
            s.student_name, 
            s.course, 
            s.year,
            a.assignment, 
            a.teacher_id, 
            a.deadline
        FROM 
            students s
        LEFT JOIN 
            assignments a ON s.student_id = a.student_id
        WHERE 
            s.course = ? AND s.year = ?
        ORDER BY 
            s.student_id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $course, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h2>Students of $course - $year</h2>";

    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='10'>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Assignment</th>
                    <th>Deadline</th>
                    <th>Assigned_by Teacher ID</th>
                    <th>Action</th>
                </tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['student_id']}</td>
                    <td>{$row['student_name']}</td>
                    <td>{$row['course']}</td>
                    <td>{$row['year']}</td>
                    <td>" . (!empty($row['assignment']) ? htmlspecialchars($row['assignment']) : "No Assignment") . "</td>
                    <td>" . (!empty($row['deadline']) ? htmlspecialchars($row['deadline']) : "-") . "</td>
                    <td>" . (!empty($row['teacher_id']) ? htmlspecialchars($row['teacher_id']) : "-") . "</td>
                    <td><a href='edit_student.php?student_id={$row['student_id']}'>Edit</a></td>
                </tr>";
        }

        echo "</table>";
    } else {
        echo "<p style='color:red;'>No students found for $course - $year.</p>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
