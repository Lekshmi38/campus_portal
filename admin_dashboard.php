<?php
// admin_dashboard.php
session_start();
require_once 'db.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// list companies
$companies = $conn->query("SELECT * FROM companies ORDER BY created_at DESC");
?>
<!doctype html>
<html>
<head><title>Admin Dashboard</title></head>
<body>
<h2>Admin Dashboard</h2>
<p><a href="add_company.php">Add Company</a> | <a href="logout.php">Logout</a></p>

<h3>Companies</h3>
<table border="1" cellpadding="6">
<tr><th>Name</th><th>Min CGPA</th><th>Branches</th><th>States</th><th>Backlogs Allowed</th><th>Package</th><th>Eligible Students</th></tr>
<?php while($c = $companies->fetch_assoc()): ?>
<tr>
  <td><?php echo htmlspecialchars($c['name']); ?></td>
  <td><?php echo htmlspecialchars($c['min_cgpa']); ?></td>
  <td><?php echo htmlspecialchars($c['allowed_branches']); ?></td>
  <td><?php echo htmlspecialchars($c['allowed_states']); ?></td>
  <td>
    Curr: <?php echo $c['allow_backlogs'] ? 'Yes' : 'No'; ?><br>
    Hist: <?php echo $c['allow_history_backlogs'] ? 'Yes' : 'No'; ?>
  </td>
  <td><?php echo htmlspecialchars($c['package_offered']); ?></td>
  <td><a href="eligible_students.php?id=<?php echo $c['id']; ?>">View Eligible</a></td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
