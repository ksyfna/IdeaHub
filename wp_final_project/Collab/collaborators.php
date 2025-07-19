<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Login/Login/login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$query = "SELECT id, username, email FROM users WHERE id != ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collaborator List</title>
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="inv_style.css">
    <style>
        .collab-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }

        .collab-container h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 28px;
        }

        .collab-container input[type="text"] {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .collab-container .search-button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            margin-top: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .collab-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .collab-table th, .collab-table td {
            padding: 12px;
            text-align: left;
        }

        .collab-table thead {
            background-color: #f0f8ff;
        }

        .collab-table tbody tr {
            border-bottom: 1px solid #ddd;
        }

        .invite-btn {
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .invite-btn:hover {
            background-color: #218838;
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="collab-container">
        <h1>Collaborator List</h1>

        <input type="text" id="searchInput" placeholder="Search users...">
        <button class="search-button" id="searchButton">Search</button>

        <table class="collab-table" id="userTable">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']); ?></td>
                        <td><?= htmlspecialchars($user['email']); ?></td>
                        <td style="text-align: center;">
                            <form action="invit_collaboration.php" method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                <button type="submit" class="invite-btn">Invite</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="../dashboard.php" class="back-button">Back to Dashboard</a>
    </div>

    <script src="collab.js" defer></script>
</body>
</html>

