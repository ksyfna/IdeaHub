<?php
session_start();
require_once '../db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Login/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user-owned projects
$project_stmt = $conn->prepare("SELECT id, title FROM projects WHERE created_by = ?");
$project_stmt->bind_param("i", $user_id);
$project_stmt->execute();
$project_result = $project_stmt->get_result();

// Get all other users
$user_stmt = $conn->prepare("SELECT id, username FROM users WHERE id != ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invite Collaborator</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .invite-wrapper {
            max-width: 600px;
            margin: 100px auto;
            background: #fff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .invite-wrapper h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        select, input[type="text"], textarea {
    width: 100%;
    padding: 10px;
    border: 2px solid #ccc;
    border-radius: 8px;
    box-sizing: border-box;
}

.invite-btn {
    background-color: #D30000;
    color: white;
    border: none; 
    padding: 12px 25px;
    font-size: 1rem;
    border-radius: 8px;
    cursor: pointer;
    width: 100%;
    transition: background 0.3s ease;
}

.back-btn {
    margin-top: 10px;
    display: block;
    text-align: center;
    color: var(--primary-color);
    text-decoration: none;
    border: none; 
}

#searchInput {
    margin-top: 10px;
    margin-bottom: 8px;
    border: 2px solid #ccc;
    border-radius: 8px;
    padding: 10px;
}       
    </style>
</head>
<body>

<div class="invite-wrapper">
    <h2>Invite Collaborator</h2>
    <form method="POST" action="send_invite.php">
        <div class="form-group">
            <label for="project_id">Select Project:</label>
            <select name="project_id" id="project_id" required>
                <option value="">-- Choose a Project --</option>
                <?php while ($row = $project_result->fetch_assoc()) : ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="to_user_id">Select Collaborator:</label>
            <input type="text" id="searchInput" placeholder="Search user..." onkeyup="filterUsers()">

            <select id="userSelect" name="to_user_id" required>
                <option value="">-- Choose a User --</option>
                <?php while ($row = $user_result->fetch_assoc()) : ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['username']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="message">Message (optional):</label>
            <textarea name="message" id="message" rows="3" placeholder="Write a message..."></textarea>
        </div>

        <button type="submit" class="invite-btn">Send Invitation</button>
    </form>
    <a href="../dashboard.php" class="back-btn">← Back to Dashboard</a>
</div>

<script>
function filterUsers() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const select = document.getElementById("userSelect");
    const options = select.getElementsByTagName("option");

    for (let i = 0; i < options.length; i++) {
        const txtValue = options[i].textContent || options[i].innerText;
        options[i].style.display = txtValue.toLowerCase().includes(input) ? "" : "none";
    }
}
</script>

</body>
</html>
