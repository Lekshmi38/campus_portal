<?php
// edit_profile.php
session_start();
require_once 'db.php';
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}
$student_id = intval($_SESSION['student_id']);
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $branch = trim($_POST['branch']);
    $state = trim($_POST['state']);
    $cgpa = floatval($_POST['cgpa']);
    $current_backlogs = intval($_POST['current_backlogs']);
    $history_of_backlogs = intval($_POST['history_of_backlogs']);

    $stmt = $conn->prepare("UPDATE students SET name=?, email=?, phone=?, branch=?, state=?, cgpa=?, current_backlogs=?, history_of_backlogs=? WHERE id=?");
    $stmt->bind_param("sssssdiii", $name, $email, $phone, $branch, $state, $cgpa, $current_backlogs, $history_of_backlogs, $student_id);
    if ($stmt->execute()) {
        $msg = "Profile updated.";
    } else {
        $msg = "Error: " . $conn->error;
    }
    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM students WHERE id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!doctype html>
<html>
<head><title>Edit Profile</title></head>
<body>
<h2>Edit Profile</h2>
<?php if($msg) echo "<p style='color:green;'>$msg</p>"; ?>
<form method="post">
  Name: <input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required><br><br>
  Email: <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required><br><br>
  Phone: <input type="text" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>"><br><br>
  Branch: <input type="text" name="branch" value="<?php echo htmlspecialchars($student['branch']); ?>" required><br><br>
  State: <input type="text" name="state" value="<?php echo htmlspecialchars($student['state']); ?>" required><br><br>
  CGPA: <input type="number" step="0.01" name="cgpa" value="<?php echo htmlspecialchars($student['cgpa']); ?>"><br><br>
  Current Backlogs: <input type="number" name="current_backlogs" value="<?php echo htmlspecialchars($student['current_backlogs']); ?>"><br><br>
  History of Backlogs: <input type="number" name="history_of_backlogs" value="<?php echo htmlspecialchars($student['history_of_backlogs']); ?>"><br><br>

  <button type="submit">Update</button>
</form>
<p><a href="student_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
