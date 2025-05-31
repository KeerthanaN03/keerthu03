<?php
session_start();

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "pro";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher_id = $_POST["teacher_id"];
    $password = $_POST["password"];

    $teacher_id = mysqli_real_escape_string($conn, $teacher_id);

    $sql = "SELECT teacher_id, teacher_name, password, department FROM teachers WHERE teacher_id = '$teacher_id'"; 
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if ($password === $row["password"]) {
            $_SESSION['teacher_logged_in'] = true;
            $_SESSION['teacher_id'] = $row["teacher_id"];
            $_SESSION['teacher_name'] = $row["teacher_name"];
            $_SESSION['teacher_department'] = $row["department"]; 
            header("Location: teacher_dashboard.php");
            exit;
        } else {
            $error_message = "Incorrect password.";
        }
    } else {
        $error_message = "Incorrect Teacher ID.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teacher Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 340px; /* Reduced from 400px to 340px */
        }
        .login-box h2 {
            text-align: center;
            margin-bottom: 22px;
            color: #333;
        }
        .login-box label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }
        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 15px;
        }
        .login-box input[type="submit"] {
            width: 100%;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .login-box input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            margin-bottom: 16px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Teacher Login</h2>

        <?php if (!empty($error_message)) { ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php } ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="teacher_id">Teacher ID:</label>
            <input type="text" name="teacher_id" id="teacher_id" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
