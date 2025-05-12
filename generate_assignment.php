<?php
session_start();

// Check if teacher is logged in
if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true) {
    header("Location: teacher_login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$teacher_department = $_SESSION['teacher_department']; // e.g., "BCA"

$conn = new mysqli("localhost", "root", "", "pro");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle form submission: Assign questions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign'])) {
    $course = $_POST['course'];
    $year = $_POST['year'];
    $question = $_POST['question'];
    $deadline = $_POST['deadline'];
    $total_marks = $_POST['total_marks'];
    $selected_students = $_POST['students'] ?? [];

    // ✅ Only allow assigning within teacher's department
    if ($course !== $teacher_department) {
        die("<p style='color:red;'>Access Denied: You can only assign to students from your own department (" . htmlspecialchars($teacher_department) . ").</p>");
    }

    foreach ($selected_students as $student_id) {
        $stmt = $conn->prepare("SELECT student_name FROM students WHERE student_id = ? AND course = ?");
        $stmt->bind_param("ss", $student_id, $teacher_department);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $student_name = $row['student_name'];

            $insert = $conn->prepare("INSERT INTO assignments (student_id, student_name, course, year, assignment, teacher_id, deadline, total_marks)
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->bind_param("sssssssi", $student_id, $student_name, $course, $year, $question, $teacher_id, $deadline, $total_marks);
            $insert->execute();
        }

        $stmt->close();
    }

    echo "<p style='color:green;'>Assignment assigned successfully!</p>";
}

// Step 1: Show students based on course/year and department
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['assign'])) {
    $course = $_POST['course'];
    $year = $_POST['year'];

    if ($course !== $teacher_department) {
        die("<p style='color:red;'>Access Denied: You can only assign to students from your own department (" . htmlspecialchars($teacher_department) . ").</p>");
    }

    $stmt = $conn->prepare("SELECT student_id, student_name FROM students WHERE course = ? AND year = ?");
    $stmt->bind_param("ss", $course, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Assign Questions</title>
            <script>
                function toggleCheckboxes(source) {
                    const checkboxes = document.querySelectorAll('.student_checkbox');
                    checkboxes.forEach(cb => cb.checked = source.checked);
                }
            </script>
        </head>
        <body>
            <h2>Assign Assignment to <?= htmlspecialchars($course) ?> - Year <?= htmlspecialchars($year) ?> Students</h2>

             <form method="post" action="save_assignment.php">
                <input type="hidden" name="course" value="<?= htmlspecialchars($course) ?>">
                <input type="hidden" name="year" value="<?= htmlspecialchars($year) ?>">

                <h3>Select Students:</h3>
                <input type="checkbox" id="select_all" onclick="toggleCheckboxes(this)"> <strong>Select All</strong><br><br>

                <?php while ($row = $result->fetch_assoc()) { ?>
                    <input type="checkbox" class="student_checkbox" name="students[]" value="<?= $row['student_id'] ?>">
                    <?= htmlspecialchars($row['student_id']) ?> - <?= htmlspecialchars($row['student_name']) ?><br>
                <?php } ?>

                <br><br>
                <label>Assignment Question:</label><br>
                <textarea name="question" rows="5" cols="50" required></textarea><br><br>

                <label>Deadline:</label><br>
                <input type="date" name="deadline" required><br><br>

                <label>Total Marks:</label><br>
                <input type="number" name="total_marks" required><br><br>

                <button type="submit" name="assign">Assign</button>
            </form>
        </body>
        </html>
        <?php
    } else {
        echo "<p style='color:red;'>No students found in " . htmlspecialchars($course) . " - Year " . htmlspecialchars($year) . ".</p>";
    }

    $stmt->close();
}

$conn->close();
?>
