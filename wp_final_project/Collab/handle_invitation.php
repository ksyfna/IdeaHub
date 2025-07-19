<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Unauthorized or invalid request.");
}

$user_id = $_SESSION['user_id'];
$invitation_id = intval($_POST['invitation_id']);
$action = $_POST['action'];
$message = "";

if ($action === 'accept') {
    // Get project_id from the invitation
    $project_stmt = $conn->prepare("SELECT project_id FROM invitations WHERE id = ? AND recipient_id = ?");
    $project_stmt->bind_param("ii", $invitation_id, $user_id);
    $project_stmt->execute();
    $project_result = $project_stmt->get_result();

    if ($project_result && $project_result->num_rows > 0) {
        $project = $project_result->fetch_assoc();
        $project_id = $project['project_id'];

        // Check if already a collaborator
        $check_stmt = $conn->prepare("SELECT id FROM project_collaborators WHERE project_id = ? AND user_id = ?");
        $check_stmt->bind_param("ii", $project_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            // Add as collaborator with 'active' status
            $add_stmt = $conn->prepare("
                INSERT INTO project_collaborators (project_id, user_id, invitation_id, status)
                VALUES (?, ?, ?, 'active')
            ");
            $add_stmt->bind_param("iii", $project_id, $user_id, $invitation_id);
            $add_stmt->execute();
        }

        // Update invitation status
        $update_stmt = $conn->prepare("UPDATE invitations SET status = 'accepted' WHERE id = ?");
        $update_stmt->bind_param("i", $invitation_id);
        $update_stmt->execute();

        $message = "Invitation accepted.";
    } else {
        $message = "Invalid invitation.";
    }
} elseif ($action === 'reject') {
    $reject_stmt = $conn->prepare("UPDATE invitations SET status = 'rejected' WHERE id = ? AND recipient_id = ?");
    $reject_stmt->bind_param("ii", $invitation_id, $user_id);
    $reject_stmt->execute();
    $message = "Invitation rejected.";
} else {
    $message = "Invalid action.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="2;url=view_invitations.php">
    <link rel="stylesheet" href="style.css">
    <title>Invitation Response</title>
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <div class="message-box">
            <h3><?= htmlspecialchars($message) ?></h3>
            <p>Redirecting back to invitations...</p>
        </div>
    </div>
</body>
</html>