<?php
session_start();
if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

$conn = new mysqli("localhost", "root", "", "pro");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch only the required assignment data for the student
$sql = "SELECT a.assignment_id, a.assignment, a.deadline, a.total_marks, t.teacher_id, t.teacher_name
        FROM assignments a
        JOIN teachers t ON a.teacher_id = t.teacher_id
        WHERE a.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Assignments</title>
    <style>
        table {
            width: 90%;
            border-collapse: collapse;
            margin: 20px auto;
        }

        th, td {
            border: 1px solid #444;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #eee;
        }

        .btn {
            padding: 5px 10px;
            background-color: blue;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn.disabled {
            background-color: gray;
            pointer-events: none;
        }

        h2 {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>Assignments Assigned by Teachers</h2>
    <table>
        <tr>
            <th>Teacher ID</th>
            <th>Teacher Name</th>
            <th>Assignment</th>
            <th>Deadline</th>
            <th>Total Marks</th>
            <th>Status</th>
        </tr>

        <?php
        $hasAssignment = false;
        $today = date('Y-m-d');
        while ($row = $result->fetch_assoc()) {
            $hasAssignment = true;
            $deadline = $row['deadline'];
            $isActive = ($deadline >= $today);
            ?>
            <tr>
                <td><?= htmlspecialchars($row['teacher_id']) ?></td>
                <td><?= htmlspecialchars($row['teacher_name']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['assignment'])) ?></td>
                <td><?= htmlspecialchars($row['deadline']) ?></td>
                <td><?= htmlspecialchars($row['total_marks']) ?></td>
                <td>
                    <?php if ($isActive): ?>
                        <a class="btn" href="write_assignment.php?assignment_id=<?= $row['assignment_id'] ?>">Write Assignment</a>
                    <?php else: ?>
                        <span class="btn disabled">Deadline Passed</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php
        }

        if (!$hasAssignment) {
            echo "<tr><td colspan='6'>No assignments assigned to you.</td></tr>";
        }

        $stmt->close();
        $conn->close();
        ?>
    </table>
</body>
</html>
