<?php
session_start();

// Check if teacher is logged in
if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true) {
    header("Location: teacher_login.php");
    exit;
}

// Connect to database
$conn = new mysqli("localhost", "root", "", "pro");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$courses = $conn->query("SELECT DISTINCT course FROM students");
$years = $conn->query("SELECT DISTINCT year FROM students");

$teacher_id = $_SESSION['teacher_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <style>
        :root {
            --primary-color: #4CAF50;
            --dark-color: #333;
            --light-bg: #f5f5f5;
            --card-bg: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light-bg);
        }

        nav {
            background-color: var(--dark-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            padding: 16px 20px;
            display: block;
            transition: background-color 0.3s ease;
        }

        nav a:hover {
            background-color: #575757;
        }

        .nav-links {
            display: flex;
            gap: 10px;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background-color: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: var(--dark-color);
            margin-bottom: 10px;
        }

        h3 {
            margin-top: 30px;
            color: var(--primary-color);
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
        }

        select, button {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        select:focus, button:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        button {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #3e8e41;
        }

        #assignment-form {
            display: none;
            transition: all 0.4s ease-in-out;
        }

        @media (max-width: 600px) {
            .nav-links {
                flex-direction: column;
                width: 100%;
            }

            nav {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
    <script>
        function showAssignmentForm() {
            document.getElementById('assignment-form').style.display = 'block';
        }
    </script>
</head>
<body>

<nav>
    <div class="nav-links">
        <a href="#" onclick="showAssignmentForm()">Generate Assignment</a>
        <a href="assignment_history.php">Assignment History</a>
        <a href="receive_assignment.php">Assignments</a>
    </div>
    <a href="teacher_logout.php">Logout</a>
</nav>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($teacher_id) ?></h2>

    <div id="assignment-form">
        <h3>Generate Assignment</h3>
        <form method="post" action="generate_assignment.php">
            <label for="course">Course</label>
            <select name="course" id="course" required>
                <option value="">-- Select Course --</option>
                <?php while ($row = $courses->fetch_assoc()) { ?>
                    <option value="<?= htmlspecialchars($row['course']) ?>"><?= htmlspecialchars($row['course']) ?></option>
                <?php } ?>
            </select>

            <label for="year">Year</label>
            <select name="year" id="year" required>
                <option value="">-- Select Year --</option>
                <?php while ($row = $years->fetch_assoc()) { ?>
                    <option value="<?= htmlspecialchars($row['year']) ?>"><?= htmlspecialchars($row['year']) ?></option>
                <?php } ?>
            </select>

            <button type="submit">Proceed to Generate</button>
        </form>
    </div>
</div>

</body>
</html>

<?php
$conn->close();
?>
