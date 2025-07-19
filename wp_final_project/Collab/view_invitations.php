<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$query = $conn->prepare("
    SELECT i.*, u.username AS sender_name, p.title AS project_title 
    FROM invitations i 
    JOIN users u ON i.sender_id = u.id 
    JOIN projects p ON i.project_id = p.id 
    WHERE i.recipient_id = ?
    ORDER BY i.created_at DESC
");
$query->bind_param("i", $user_id);
$query->execute();
$invitations = $query->get_result();

$alert = '';
if (isset($_SESSION['invitation_response'])) {
    $alert = $_SESSION['invitation_response'];
    unset($_SESSION['invitation_response']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Invitations</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        h2 {
            color: #921224;
            margin-bottom: 20px;
            text-align: center;
        }
        .alert {
            background-color: #d4edda;
            color: #155724;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }
        .search-bar input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .invitation {
            border-left: 5px solid #D30000;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
        }
        .invitation h3 {
            margin: 0;
            font-size: 1.1rem;
            color: #921224;
        }
        .invitation small {
            color: #666;
        }
        .message-box {
            margin-top: 10px;
            color: #333;
            font-size: 0.95rem;
        }
        .message-box .collapse-btn {
            color: #D30000;
            cursor: pointer;
            font-size: 0.9rem;
            display: inline-block;
            margin-top: 5px;
        }
        .invitation form {
            margin-top: 15px;
        }
        .btn {
            padding: 8px 14px;
            font-size: 0.9rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .accept-btn {
            background-color: #921224;
            color: white;
            margin-right: 10px;
        }
        .reject-btn {
            background-color: #6c757d;
            color: white;
        }
        .back-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background: #D30000;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
        }

        @media (max-width: 600px) {
            .invitation h3 {
                font-size: 1rem;
            }
            .btn {
                display: block;
                margin-bottom: 10px;
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>My Invitations</h2>

    <?php if ($alert): ?>
        <div class="alert"><?= htmlspecialchars($alert) ?></div>
    <?php endif; ?>

    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search by sender or project...">
    </div>

    <div id="invitationList">
        <?php if ($invitations->num_rows === 0): ?>
            <p>No invitations found.</p>
        <?php else: ?>
            <?php while ($inv = $invitations->fetch_assoc()): ?>
                <div class="invitation" data-search="<?= strtolower($inv['sender_name'] . ' ' . $inv['project_title']) ?>">
                    <h3><?= htmlspecialchars($inv['project_title']) ?></h3>
                    <small>From: <?= htmlspecialchars($inv['sender_name']) ?> | Status: <strong><?= ucfirst($inv['status']) ?></strong></small>

                    <?php if (!empty($inv['message'])): ?>
                        <div class="message-box">
                            <span class="collapse-btn" onclick="toggleMessage(this)">Show message</span>
                            <p style="display:none; margin-top: 5px;"><?= nl2br(htmlspecialchars($inv['message'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($inv['status'] === 'pending'): ?>
                        <form method="POST" action="handle_invitation.php">
                            <input type="hidden" name="invitation_id" value="<?= $inv['id'] ?>">
                            <input type="hidden" name="sender_id" value="<?= $inv['sender_id'] ?>">
                            <button type="submit" name="action" value="accept" class="btn accept-btn">Accept</button>
                            <button type="submit" name="action" value="reject" class="btn reject-btn">Reject</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <a href="../dashboard.php" class="back-btn">← Back to Dashboard</a>
</div>

<script>
function toggleMessage(el) {
    const msg = el.nextElementSibling;
    if (msg.style.display === "none") {
        msg.style.display = "block";
        el.textContent = "Hide message";
    } else {
        msg.style.display = "none";
        el.textContent = "Show message";
    }
}

document.getElementById('searchInput').addEventListener('input', function () {
    const query = this.value.toLowerCase();
    const cards = document.querySelectorAll('#invitationList .invitation');

    cards.forEach(card => {
        const match = card.getAttribute('data-search').includes(query);
        card.style.display = match ? '' : 'none';
    });
});
</script>
</body>
</html>