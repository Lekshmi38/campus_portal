<?php
// apply_company.php
session_start();
require_once 'db.php';
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}
$student_id = intval($_SESSION['student_id']);
$company_id = intval($_GET['id'] ?? 0);

// check existence and eligibility quickly (optional)
$stmt = $conn->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->bind_param("i",$company_id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$company) {
    die("Company not found.");
}

// check already applied
$chk = $conn->prepare("SELECT COUNT(*) FROM applications WHERE student_id=? AND company_id=?");
$chk->bind_param("ii", $student_id, $company_id);
$chk->execute();
$chk->bind_result($cnt);
$chk->fetch();
$chk->close();
if ($cnt > 0) {
    header("Location: student_dashboard.php");
    exit();
}

// insert application
$ins = $conn->prepare("INSERT INTO applications (student_id, company_id) VALUES (?, ?)");
$ins->bind_param("ii", $student_id, $company_id);
$ins->execute();
$ins->close();

header("Location: student_dashboard.php");
exit();
