<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/Login/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$projectId = $_GET["id"] ?? null;

if (!$projectId || !is_numeric($projectId)) {
    die("Invalid project ID.");
}

$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->bind_param("i", $projectId);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();

if (!$project) {
    die("Project not found.");
}

if ($project['created_by'] != $userId) {
    die("You do not have permission to delete this project.");
}

if (!empty($project['file']) && file_exists("../uploads/" . $project['file'])) {
    unlink("../uploads/" . $project['file']);
}

$deleteStmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
$deleteStmt->bind_param("i", $projectId);
$deleteStmt->execute();

header("Location: ../dashboard.php");
exit();
?>