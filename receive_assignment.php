<?php
session_start();

// Check if teacher is logged in
if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true) {
    header("Location: teacher_login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

// Connect to database
$conn = new mysqli("localhost", "root", "", "pro");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch all assignments submitted by students for the logged-in teacher
$sql = "SELECT sa.student_id, sa.student_name, sa.course, sa.year, sa.response, sa.submitted_at
        FROM submitted_assignments sa
        WHERE sa.teacher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Received Assignments</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 0;
            margin: 0;
            background-color: #f5f5f5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f4;
        }

        nav {
            background-color: #333;
            overflow: hidden;
        }

        nav a {
            float: left;
            display: block;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
        }

        nav a:hover {
            background-color: #575757;
            color: white;
        }

        .container {
            padding: 30px;
        }

        #assignment-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border: 2px solid #ddd;
            width: 80%;
            max-width: 600px;
        }

        #assignment-modal h3 {
            margin-top: 0;
        }

        #assignment-modal button {
            margin-top: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
        }

        #assignment-modal button:hover {
            background-color: #45a049;
        }
    </style>
    <script>
        // Function to view the assignment response details
        function viewAssignmentDetails(student_id) {
            const modal = document.getElementById('assignment-modal');
            const assignmentContent = document.getElementById('assignment-content');
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'assignment_details.php?student_id=' + student_id, true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    assignmentContent.innerHTML = xhr.responseText;
                    modal.style.display = 'block';
                }
            };
            xhr.send();
        }

        // Function to close the assignment modal
        function closeModal() {
            document.getElementById('assignment-modal').style.display = 'none';
        }
    </script>
</head>
<body>

<nav>
    <a href="teacher_dashboard.php">Dashboard</a>
    <a href="receive_assignment.php">Assignments</a>
    <a href="teacher_logout.php" style="float:right;">Logout</a>
</nav>

<div class="container">
    <h2>Received Assignments</h2>

    <?php if ($result->num_rows > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>View Response</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['course']); ?></td>
                        <td><?php echo htmlspecialchars($row['year']); ?></td>
                        <td><a href="#" onclick="event.preventDefault(); viewAssignmentDetails(<?php echo $row['student_id']; ?>);">View Response</a></td>
                        <td><?php echo htmlspecialchars($row['submitted_at']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No assignments submitted yet.</p>
    <?php } ?>

</div>

<div id="assignment-modal">
    <h3>Assignment Response Details</h3>
    <div id="assignment-content"></div>
    <button onclick="closeModal()">Close</button>
</div>

</body>
</html>

<?php
$conn->close();
?>
