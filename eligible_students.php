<?php
// eligible_students.php
session_start();
require_once 'db.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
$company_id = intval($_GET['id'] ?? 0);
if ($company_id <= 0) die("Invalid company id.");

$company = $conn->query("SELECT * FROM companies WHERE id = $company_id")->fetch_assoc();
if (!$company) die("Company not found.");

// Build WHERE conditions dynamically
$where = [];
$where[] = "cgpa >= " . floatval($company['min_cgpa']);

if ($company['allow_backlogs'] == 0) {
    $where[] = "current_backlogs = 0";
}
if ($company['allow_history_backlogs'] == 0) {
    $where[] = "history_of_backlogs = 0";
}

// Branch filter (allowed_branches is CSV)
$allowedBranches = array_map('trim', explode(",", $company['allowed_branches']));
if (!in_array('All', $allowedBranches)) {
    $bEsc = array_map(function($b) use ($conn){ return "'" . $conn->real_escape_string($b) . "'"; }, $allowedBranches);
    $where[] = "branch IN (" . implode(",", $bEsc) . ")";
}

// State filter
$allowedStates = array_map('trim', explode(",", $company['allowed_states']));
if (!in_array('All', $allowedStates)) {
    $sEsc = array_map(function($s) use ($conn){ return "'" . $conn->real_escape_string($s) . "'"; }, $allowedStates);
    $where[] = "state IN (" . implode(",", $sEsc) . ")";
}

$sql = "SELECT * FROM students";
if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY name ASC";

$students = $conn->query($sql);
?>
<!doctype html>
<html>
<head><title>Eligible Students for <?php echo htmlspecialchars($company['name']); ?></title></head>
<body>
<h2>Eligible Students for "<?php echo htmlspecialchars($company['name']); ?>"</h2>
<p>Filters: Min CGPA = <?php echo htmlspecialchars($company['min_cgpa']); ?> |
Branches = <?php echo htmlspecialchars($company['allowed_branches']); ?> |
States = <?php echo htmlspecialchars($company['allowed_states']); ?> |
Allow current backlogs = <?php echo $company['allow_backlogs'] ? 'Yes' : 'No'; ?> |
Allow history = <?php echo $company['allow_history_backlogs'] ? 'Yes' : 'No'; ?></p>

<table border="1" cellpadding="6">
<tr><th>Name</th><th>Roll</th><th>Branch</th><th>CGPA</th><th>Curr Backlogs</th><th>History</th><th>State</th></tr>
<?php while($s = $students->fetch_assoc()): ?>
<tr>
  <td><?php echo htmlspecialchars($s['name']); ?></td>
  <td><?php echo htmlspecialchars($s['roll']); ?></td>
  <td><?php echo htmlspecialchars($s['branch']); ?></td>
  <td><?php echo htmlspecialchars($s['cgpa']); ?></td>
  <td><?php echo htmlspecialchars($s['current_backlogs']); ?></td>
  <td><?php echo htmlspecialchars($s['history_of_backlogs']); ?></td>
  <td><?php echo htmlspecialchars($s['state']); ?></td>
</tr>
<?php endwhile; ?>
</table>

<p><a href="admin_dashboard.php">Back to Admin Dashboard</a></p>
</body>
</html>
