<?php
session_start();

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true) {
    header("Location: teacher_login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

$conn = new mysqli("localhost", "root", "", "pro");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle marks submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id'], $_POST['marks_assigned'])) {
    $submission_id = intval($_POST['submission_id']);
    $marks_assigned = intval($_POST['marks_assigned']);

    $update_sql = "UPDATE submitted_assignments SET marks_assigned = ? WHERE submission_id = ? AND teacher_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("iii", $marks_assigned, $submission_id, $teacher_id);
    if ($stmt->execute()) {
        $message = "✅ Marks submitted successfully.";
    } else {
        $message = "❌ Failed to submit marks: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all submitted assignments for this teacher
$sql = "SELECT submission_id, student_id, student_name, course, year, response, submitted_at, total_marks, marks_assigned
        FROM submitted_assignments 
        WHERE teacher_id = ? 
        ORDER BY submitted_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard - Received Assignments</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .assignment {
            border: 1px solid #ccc; padding: 15px; margin-bottom: 20px;
            background: #f9f9f9;
        }
        .graded { background: #d4edda; }
        textarea { width: 100%; height: 150px; }
        input[type=number] { width: 80px; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .response-area {
            border: 1px solid #999;
            padding: 10px;
            background: #fff;
            overflow-x: auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<h2>Received Assignments</h2>

<?php if (!empty($message)): ?>
    <div class="message <?= strpos($message, '✅') === 0 ? 'success' : 'error' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php if ($result->num_rows === 0): ?>
    <p>No submitted assignments found.</p>
<?php else: ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="assignment <?= is_null($row['marks_assigned']) ? '' : 'graded' ?>">
            <p><strong>Student:</strong> <?= htmlspecialchars($row['student_name']) ?> (ID: <?= $row['student_id'] ?>)</p>
            <p><strong>Course:</strong> <?= htmlspecialchars($row['course']) ?> | <strong>Year:</strong> <?= htmlspecialchars($row['year']) ?></p>
            <p><strong>Submitted At:</strong> <?= htmlspecialchars($row['submitted_at']) ?></p>
            <p><strong>Total Marks:</strong> <?= htmlspecialchars($row['total_marks']) ?></p>
            <p><strong>Student Response:</strong></p>
            <div class="response-area">
                <?= html_entity_decode($row['response']) ?>
            </div>

            <?php if (is_null($row['marks_assigned'])): ?>
                <form method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="submission_id" value="<?= $row['submission_id'] ?>">
                    <label>Enter Marks Assigned: </label>
                    <input type="number" name="marks_assigned" min="0" max="<?= $row['total_marks'] ?>" required>
                    <button type="submit">Submit Marks</button>
                </form>
            <?php else: ?>
                <p><strong>Marks Obtained:</strong> <?= htmlspecialchars($row['marks_assigned']) ?></p>
                <p><em>Grading completed. Student cannot resubmit this assignment.</em></p>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>