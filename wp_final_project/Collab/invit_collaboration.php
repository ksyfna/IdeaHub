<?php
require_once '../db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit();
}

$recipient_id = $_POST['user_id'] ?? 0;

$stmt = $conn->prepare("SELECT id, username FROM users WHERE id = ?");
$stmt->bind_param("i", $recipient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found");
}
$recipient = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];
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
    <title>Invite Collaboration</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="Collab/Collab/inv_style.css">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .invite-container {
            max-width: 600px;
            margin: 60px auto;
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }

        .invite-header {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .user-info {
            background-color: #f0f8ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        textarea {
            width: 100%;
            min-height: 100px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .send-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 5px;
            cursor: pointer;
        }

        .send-button:hover {
            background-color: #218838;
        }

        .back-button {
            text-decoration: none;
            background-color: #007bff;
            color: white;
            padding: 10px 18px;
            border-radius: 5px;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="invite-container">
        <div class="invite-header">Invite to Collaborate</div>

        <?php if (isset($success)): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="user-info">
            You're inviting: <strong><?php echo htmlspecialchars($recipient['username']); ?></strong>
        </div>

        <form method="POST">
            <input type="hidden" name="user_id" value="<?php echo $recipient_id; ?>">

            <div class="form-group">
                <label for="message">Message:</label>
                <textarea id="message" name="message" required></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="send-button">Send Invitation</button>
                <a href="Idea/add.php" class="back-button">Back</a>
            </div>
        </form>
    </div>
</body>
</html>

