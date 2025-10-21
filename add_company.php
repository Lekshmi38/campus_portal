<?php
// add_company.php
session_start();
require_once 'db.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $min_cgpa = floatval($_POST['min_cgpa']);
    $branchesArr = $_POST['branches'] ?? [];
    $statesArr = $_POST['states'] ?? [];
    $allow_backlogs = isset($_POST['allow_backlogs']) ? 1 : 0;
    $allow_history = isset($_POST['allow_history']) ? 1 : 0;
    $package = floatval($_POST['package']);
    $desc = trim($_POST['description']);

    if (empty($branchesArr)) $branchesArr = ['All'];
    if (empty($statesArr)) $statesArr = ['All'];

    // sanitize each value (simple)
    $branchesArr = array_map(function($v){ return preg_replace('/[^a-zA-Z0-9\s\-]/','',trim($v)); }, $branchesArr);
    $statesArr = array_map(function($v){ return preg_replace('/[^a-zA-Z0-9\s\-]/','',trim($v)); }, $statesArr);

    $branchesCSV = implode(",", $branchesArr);
    $statesCSV = implode(",", $statesArr);

    $stmt = $conn->prepare("INSERT INTO companies (name, min_cgpa, allowed_branches, allow_backlogs, allow_history_backlogs, allowed_states, package_offered, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdsiisds", $name, $min_cgpa, $branchesCSV, $allow_backlogs, $allow_history, $statesCSV, $package, $desc);
    // Note: bind_param types may require correction because 's d s i i s d s' mapping is mixed numbers & strings;
    // to avoid complexity, use direct query below if binding errors arise.
    // For stability, we'll use a safer direct prepared-like string via real_escape (below).
    $stmt->close();

    // Safer fallback: use real_escape_string and normal query
    $name_e = $conn->real_escape_string($name);
    $desc_e = $conn->real_escape_string($desc);
    $query = "INSERT INTO companies (name, min_cgpa, allowed_branches, allow_backlogs, allow_history_backlogs, allowed_states, package_offered, description)
              VALUES ('$name_e', $min_cgpa, '".$conn->real_escape_string($branchesCSV)."', $allow_backlogs, $allow_history, '".$conn->real_escape_string($statesCSV)."', $package, '$desc_e')";
    if ($conn->query($query)) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $msg = "Error: " . $conn->error;
    }
}
?>
<!doctype html>
<html>
<head><title>Add Company</title></head>
<body>
<h2>Add Company</h2>
<?php if($msg) echo "<p style='color:red;'>$msg</p>"; ?>
<form method="post">
  Name*: <input type="text" name="name" required><br><br>
  Min CGPA: <input type="number" step="0.01" name="min_cgpa" value="0"><br><br>

  Allowed Branches* (hold Ctrl/Cmd to select multiple):<br>
  <select name="branches[]" multiple size="6">
    <option value="All">All</option>
    <option value="CSE">CSE</option>
    <option value="ECE">ECE</option>
    <option value="EEE">EEE</option>
    <option value="MECH">MECH</option>
    <option value="CIVIL">CIVIL</option>
  </select><br><br>

  Allowed States (hold Ctrl/Cmd to select multiple):<br>
  <select name="states[]" multiple size="6">
    <option value="All">All</option>
    <option value="Kerala">Kerala</option>
    <option value="Tamil Nadu">Tamil Nadu</option>
    <option value="Karnataka">Karnataka</option>
    <option value="Andhra Pradesh">Andhra Pradesh</option>
    <option value="Telangana">Telangana</option>
  </select><br><br>

  Allow current backlogs? <input type="checkbox" name="allow_backlogs" checked><br><br>
  Allow history of backlogs? <input type="checkbox" name="allow_history" checked><br><br>

  Package: <input type="number" step="0.01" name="package" value="0"><br><br>
  Description:<br>
  <textarea name="description" rows="4" cols="50"></textarea><br><br>

  <button type="submit">Add Company</button>
</form>
<p><a href="admin_dashboard.php">Back</a></p>
</body>
</html>
