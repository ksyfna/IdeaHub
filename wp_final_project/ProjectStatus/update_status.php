<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
    $new_status = $_POST['status'] ?? '';

    $allowed_statuses = ['Not Started', 'In Progress', 'Completed'];

    if ($project_id > 0 && in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE projects SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $project_id);
        if ($stmt->execute()) {
            header("Location: project_dashboard_kanban.php");
            exit();
        }
    }

    header("Location: project_dashboard_kanban.php?error=update");
    exit();
}
?>
