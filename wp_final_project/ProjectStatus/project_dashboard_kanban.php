<?php
session_start();
require '../db.php';

$userId = $_SESSION['user_id'];

$sql = "
    SELECT p.*
    FROM projects p
    WHERE p.created_by = ?
       OR p.id IN (
           SELECT project_id FROM project_collaborators 
           WHERE user_id = ? AND status = 'active'
       )
    ORDER BY p.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();

$grouped_projects = [
    'Not Started' => [],
    'In Progress' => [],
    'Completed' => []
];

$index = 0;
while ($project = $result->fetch_assoc()) {
    $project['index'] = $index++;

    $status_raw = strtolower(trim($project['status'] ?? 'not_started'));
    switch ($status_raw) {
        case 'in progress':
        case 'in_progress':
            $project['status'] = 'In Progress';
            $grouped_projects['In Progress'][] = $project;
            break;
        case 'completed':
            $project['status'] = 'Completed';
            $grouped_projects['Completed'][] = $project;
            break;
        default:
            $project['status'] = 'Not Started';
            $grouped_projects['Not Started'][] = $project;
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .main-header h1 {
            color: #921224;
        }
        .back-button {
            background: #921224;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
        }
        .kanban-board {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .kanban-column {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            flex: 1;
        }
        .kanban-column h3 {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
            color: #921224;
        }
        .project-card {
            background: #f5f5f5;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .project-card h4 {
            margin: 0 0 5px;
            font-size: 1rem;
            color: #333;
        }
        .project-card p {
            margin: 0 0 10px;
            font-size: 0.9rem;
            color: #555;
        }
        .project-card select {
            width: 100%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <div class="main-header">
        <h1>Project Dashboard</h1>
        <a href="../dashboard.php" class="back-button">&larr; Back to Dashboard</a>
    </div>

    <div class="kanban-board">
        <?php foreach ($grouped_projects as $status => $list): ?>
            <div class="kanban-column">
                <h3><?= $status ?></h3>
                <?php if (empty($list)): ?>
                    <p style="color:#aaa; font-size:0.9rem;">No projects.</p>
                <?php else: ?>
                    <?php foreach ($list as $idea): ?>
                        <div class="project-card">
                            <h4><?= htmlspecialchars($idea['title']) ?></h4>
                            <p><?= htmlspecialchars($idea['short_desc'] ?? '') ?></p>
                            <form method="POST" action="update_status.php">
                                <input type="hidden" name="project_id" value="<?= $idea['id'] ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="Not Started" <?= $idea['status'] === 'Not Started' ? 'selected' : '' ?>>Not Started</option>
                                    <option value="In Progress" <?= $idea['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="Completed" <?= $idea['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
