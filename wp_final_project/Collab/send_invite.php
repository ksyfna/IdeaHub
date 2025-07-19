<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$from_user_id = $_SESSION['user_id'];
$project_id = $_POST['project_id'] ?? null;
$to_user_id = $_POST['to_user_id'] ?? null;
$message = $_POST['message'] ?? '';

if (!$project_id || !$to_user_id) {
    echo "<script>alert('Missing project or user.'); window.location.href = 'invite.php';</script>";
    exit;
}

// Check if invitation already exists
$check_stmt = $conn->prepare("SELECT * FROM invitations WHERE sender_id = ? AND recipient_id = ? AND project_id = ?");
$check_stmt->bind_param("iii", $from_user_id, $to_user_id, $project_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo "<script>alert('Invitation already sent to this user for this project.'); window.location.href = '../dashboard.php';</script>";
    exit;
}

// Insert new invitation with message
$insert_stmt = $conn->prepare("INSERT INTO invitations (sender_id, recipient_id, project_id, message, status) VALUES (?, ?, ?, ?, 'pending')");
$insert_stmt->bind_param("iiis", $from_user_id, $to_user_id, $project_id, $message);

if ($insert_stmt->execute()) {
    echo "<script>alert('Invitation successfully sent!'); window.location.href = '../dashboard.php';</script>";
    exit;
} else {
    echo "<script>alert('Failed to send invitation. Please try again.'); window.location.href = 'invite.php';</script>";
    exit;
}
?>