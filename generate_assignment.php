<?php
session_start();

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign'])) {
    $course = $_POST['course'];
    $year = $_POST['year'];
    $question = $_POST['question'];
    $deadline = $_POST['deadline'];
    $total_marks = $_POST['total_marks'];
    $selected_students = $_POST['students'] ?? [];

    if ($course !== $teacher_department) {
        die("<div class='error'>Access Denied: Only allowed to assign within your department ($teacher_department).</div>");
    }

    foreach ($selected_students as $student_id) {
        $stmt = $conn->prepare("SELECT student_name FROM students WHERE student_id = ? AND course = ?");
        $stmt->bind_param("ss", $student_id, $teacher_department);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $student_name = $row['student_name'];
            $insert = $conn->prepare("INSERT INTO assignments (student_id, student_name, course, year, assignment, teacher_id, deadline, total_marks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->bind_param("sssssssi", $student_id, $student_name, $course, $year, $question, $teacher_id, $deadline, $total_marks);
            $insert->execute();
        }

        $stmt->close();
    }

    echo "<div class='success'>✅ Assignment successfully assigned to selected students.</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['assign'])) {
    $course = $_POST['course'];
    $year = $_POST['year'];

    if ($course !== $teacher_department) {
        die("<div class='error'>Access Denied: Only allowed to assign within your department ($teacher_department).</div>");
    }

    $stmt = $conn->prepare("SELECT student_id, student_name FROM students WHERE course = ? AND year = ?");
    $stmt->bind_param("ss", $course, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Assignment</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #eef2f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: 600;
            color: #444;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }
        textarea {
            resize: vertical;
        }
        .students-list {
            max-height: 240px;
            overflow-y: auto;
            background: #f9f9f9;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .students-list label {
            display: block;
            margin-bottom: 6px;
            font-weight: normal;
            color: #555;
        }
        .select-all {
            font-weight: bold;
            margin-bottom: 12px;
        }
        button {
            width: 100%;
            background: #007bff;
            color: #fff;
            padding: 14px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .success, .error {
            max-width: 900px;
            margin: 20px auto;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-size: 15px;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
    <script>
        function toggleCheckboxes(source) {
            const checkboxes = document.querySelectorAll('.student_checkbox');
            checkboxes.forEach(cb => cb.checked = source.checked);
        }

        window.onload = function () {
            const dateInput = document.getElementById('deadline');
            const today = new Date();
            const toISODate = date => date.toISOString().split('T')[0];
            dateInput.min = toISODate(today); // Allow today and all future dates
        };
    </script>
</head>
<body>
    <div class="container">
        <h2>Assign Assignment to <?= htmlspecialchars($course) ?> - Year <?= htmlspecialchars($year) ?></h2>

        <form method="post" action="">
            <input type="hidden" name="course" value="<?= htmlspecialchars($course) ?>">
            <input type="hidden" name="year" value="<?= htmlspecialchars($year) ?>">

            <label class="select-all">
                <input type="checkbox" onclick="toggleCheckboxes(this)"> Select All Students
            </label>

            <div class="students-list">
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <label>
                        <input type="checkbox" class="student_checkbox" name="students[]" value="<?= $row['student_id'] ?>">
                        <?= htmlspecialchars($row['student_id']) ?> - <?= htmlspecialchars($row['student_name']) ?>
                    </label>
                <?php } ?>
            </div>

            <label for="question">Assignment Question:</label>
            <textarea name="question" id="question" rows="4" required></textarea>

            <label for="deadline">Deadline:</label>
            <input type="date" name="deadline" id="deadline" required>

            <label for="total_marks">Total Marks:</label>
            <input type="number" name="total_marks" id="total_marks" required>

            <button type="submit" name="assign">Assign Assignment</button>
        </form>
    </div>
</body>
</html>
<?php
    } else {
        echo "<div class='error'>❌ No students found in $course - Year $year.</div>";
    }

    $stmt->close();
}
$conn->close();
?>
