<?php
// register.php
require_once 'db.php';

$err = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $roll = trim($_POST['roll']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $branch = trim($_POST['branch']);
    $state = trim($_POST['state']);
    $password = $_POST['password'];

    if (!$name || !$roll || !$email || !$password) {
        $err = "Please fill required fields.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO students (name, roll, email, phone, branch, state, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $roll, $email, $phone, $branch, $state, $hash);
        if ($stmt->execute()) {
            header("Location: login.php?registered=1");
            exit();
        } else {
            $err = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html>
<head><title>Register</title></head>
<body>
<h2>Student Registration</h2>
<?php if($err) echo "<p style='color:red;'>$err</p>"; ?>
<form method="post">
  Name*: <input type="text" name="name" required><br><br>
  Roll*: <input type="text" name="roll" required><br><br>
  Email*: <input type="email" name="email" required><br><br>
  Phone: <input type="text" name="phone"><br><br>
  Branch*: <input type="text" name="branch" required placeholder="e.g., CSE"><br><br>
  State*: <input type="text" name="state" required placeholder="e.g., Kerala"><br><br>
  Password*: <input type="password" name="password" required><br><br>
  <button type="submit">Register</button>
</form>
<p>Already registered? <a href="login.php">Login</a></p>
</body>
</html>
