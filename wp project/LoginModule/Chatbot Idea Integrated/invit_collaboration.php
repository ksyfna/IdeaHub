<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$recipient_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$recipient = [];
$stmt = $conn->prepare("SELECT id, username FROM users WHERE id = ?");
$stmt->bind_param("i", $recipient_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("User not found");
}
$recipient = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'] ?? '';
    $sender_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("INSERT INTO invitations (sender_id, recipient_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender_id, $recipient_id, $message);
    
    if ($stmt->execute()) {
        $success = "Invitation sent successfully!";
    } else {
        $error = "Error sending invitation: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invit Collaboration</title>
    <link rel="stylesheet" href="inv_style.css">
</head>
<body>
    <div class="container">
        <div class="header">Invite to Collaborate</div>
        
        <?php if (isset($success)): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="user-info">
            <p>You're inviting: <strong><?php echo htmlspecialchars($recipient['username']); ?></strong></p>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="message">Message:</label>
                <textarea id="message" name="message" class="message-input" required></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="send-button">Send Invitation</button>
                <a href="add.php" class="back-button">Back</a>
            </div>
        </form>
    </div>
</body>
</html>