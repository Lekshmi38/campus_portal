<?php
// login.php
session_start();
require_once 'db.php';
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll = trim($_POST['roll']);
    $password = $_POST['password'];

    // quick admin default account (you can expand to admin table if needed)
    if ($roll === 'admin' && $password === 'admin123') {
        $_SESSION['admin'] = true;
        header("Location: admin_dashboard.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, password, name FROM students WHERE roll = ?");
    $stmt->bind_param("s", $roll);
    $stmt->execute();
    $stmt->bind_result($id, $hash, $name);
    if ($stmt->fetch()) {
        if (password_verify($password, $hash)) {
            $_SESSION['student_id'] = $id;
            $_SESSION['student_name'] = $name;
            header("Location: student_dashboard.php");
            exit();
        } else {
            $msg = "Invalid credentials.";
        }
    } else {
        $msg = "Invalid credentials.";
    }
    $stmt->close();
}
?>
<!doctype html>
<html>
<head><title>Login</title></head>
<body>
<h2>Login</h2>
<?php if(isset($_GET['registered'])) echo "<p style='color:green;'>Registration successful — please login.</p>"; ?>
<form method="post">
  Roll: <input type="text" name="roll" required><br><br>
  Password: <input type="password" name="password" required><br><br>
  <button type="submit">Login</button>
</form>
<p style="color:red;"><?php echo $msg; ?></p>
<p>Student? <a href="register.php">Register here</a></p>
<p>Admin? Use Roll = <b>admin</b> and Password = <b>admin123</b></p>
</body>
</html>
