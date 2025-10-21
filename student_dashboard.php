<?php
// student_dashboard.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = intval($_SESSION['student_id']);

// Fetch student record
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Ensure numeric defaults
$student['cgpa'] = (float)($student['cgpa'] ?? 0);
$student['current_backlogs'] = (int)($student['current_backlogs'] ?? 0);
$student['history_of_backlogs'] = (int)($student['history_of_backlogs'] ?? 0);

// Fetch all companies
$companiesRes = $conn->query("SELECT * FROM companies ORDER BY created_at DESC");
$eligible_companies = [];

while ($c = $companiesRes->fetch_assoc()) {
    // Normalize company criteria
    $allowedBranches = array_map('strtolower', array_map('trim', explode(",", $c['allowed_branches'])));
    $allowedStates   = array_map('strtolower', array_map('trim', explode(",", $c['allowed_states'])));
    $studentBranch   = strtolower(trim($student['branch']));
    $studentState    = strtolower(trim($student['state']));

    $eligible = true;

    // CGPA check
    if ((float)$student['cgpa'] < (float)$c['min_cgpa']) {
        $eligible = false;
    }

    // Branch check
    if (!in_array('all', $allowedBranches) && !in_array($studentBranch, $allowedBranches)) {
        $eligible = false;
    }

    // State check
    if (!in_array('all', $allowedStates) && !in_array($studentState, $allowedStates)) {
        $eligible = false;
    }

    // Current backlog check
    if ($c['allow_backlogs'] == 0 && (int)$student['current_backlogs'] > 0) {
        $eligible = false;
    }

    // History of backlog check
    if ($c['allow_history_backlogs'] == 0 && (int)$student['history_of_backlogs'] > 0) {
        $eligible = false;
    }

    // Check if already applied
    $appliedStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE student_id=? AND company_id=?");
    $appliedStmt->bind_param("ii", $student_id, $c['id']);
    $appliedStmt->execute();
    $appliedStmt->bind_result($appliedCount);
    $appliedStmt->fetch();
    $appliedStmt->close();

    if ($eligible) {
        $c['already_applied'] = ($appliedCount > 0);
        $eligible_companies[] = $c;
    }
}

// Fetch applications
$appRes = $conn->prepare("
    SELECT a.*, c.name, c.package_offered 
    FROM applications a 
    JOIN companies c ON a.company_id = c.id 
    WHERE a.student_id = ? 
    ORDER BY a.applied_at DESC
");
$appRes->bind_param("i", $student_id);
$appRes->execute();
$applications = $appRes->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Dashboard</title>
</head>
<body>

<h2>Welcome, <?php echo htmlspecialchars($student['name']); ?></h2>
<p>
    <b>Roll No:</b> <?php echo htmlspecialchars($student['roll']); ?> |
    <b>Branch:</b> <?php echo htmlspecialchars($student['branch']); ?> |
    <b>CGPA:</b> <?php echo htmlspecialchars($student['cgpa']); ?>
</p>
<p>
    <a href="edit_profile.php">Edit Profile / Academic Details</a> |
    <a href="logout.php" class="logout">Logout</a>
</p>

<hr>

<h3>Eligible Companies</h3>
<?php if (empty($eligible_companies)): ?>
    <p>No companies currently match your profile.</p>
<?php else: ?>
<table>
<tr>
    <th>Company Name</th>
    <th>Package</th>
    <th>Min CGPA</th>
    <th>Allowed Branches</th>
    <th>Allowed States</th>
    <th>Action</th>
</tr>
<?php foreach ($eligible_companies as $c): ?>
<tr>
    <td><?php echo htmlspecialchars($c['name']); ?></td>
    <td><?php echo htmlspecialchars($c['package_offered']); ?></td>
    <td><?php echo htmlspecialchars($c['min_cgpa']); ?></td>
    <td><?php echo htmlspecialchars($c['allowed_branches']); ?></td>
    <td><?php echo htmlspecialchars($c['allowed_states']); ?></td>
    <td>
        <?php if ($c['already_applied']): ?>
            <span class="applied">Applied</span>
        <?php else: ?>
            <a class="btn" href="apply_company.php?id=<?php echo $c['id']; ?>">Apply</a>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<hr>

<h3>Your Applications</h3>
<?php if ($applications->num_rows == 0): ?>
    <p>You haven’t applied to any company yet.</p>
<?php else: ?>
<table>
<tr>
    <th>Company</th>
    <th>Package</th>
    <th>Status</th>
    <th>Applied On</th>
</tr>
<?php while ($r = $applications->fetch_assoc()): ?>
<tr>
    <td><?php echo htmlspecialchars($r['name']); ?></td>
    <td><?php echo htmlspecialchars($r['package_offered']); ?></td>
    <td><?php echo htmlspecialchars($r['status']); ?></td>
    <td><?php echo htmlspecialchars($r['applied_at']); ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>

</body>
</html>
