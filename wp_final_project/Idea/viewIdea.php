<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/Login/login.php");
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Guest';
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$projectId || !is_numeric($projectId)) {
    die("Invalid project ID.");
}

// ✅ Corrected access query
$stmt = $conn->prepare("
    SELECT p.*, u.username AS creator_name 
    FROM projects p 
    JOIN users u ON p.created_by = u.id 
    LEFT JOIN project_collaborators pc 
        ON pc.project_id = p.id AND pc.user_id = ? AND pc.status = 'active'
    WHERE p.id = ? AND (
        p.visibility = 'public' 
        OR p.created_by = ? 
        OR pc.user_id IS NOT NULL
    )
");

$stmt->bind_param("iii", $userId, $projectId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();

if (!$project) {
    die("Project not found or you do not have access.");
}

$isOwner = $userId == $project['created_by'];

$isShared = false;
$checkStmt = $conn->prepare("SELECT 1 FROM project_collaborators WHERE project_id = ? AND user_id = ?");
$checkStmt->bind_param("ii", $projectId, $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
if ($checkResult->num_rows > 0) {
    $isShared = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($project['title']) ?> | IdeaHub</title>
    <link rel="stylesheet" href="css/shareIdea.css">
    <link rel="stylesheet" href="css/viewIdea.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar Toggle Button -->
    <button id="sidebarToggle" class="sidebar-toggle"><i class="fas fa-bars"></i></button>

    <!-- Sidebar -->
    <div id="sidebarOverlay" class="sidebar-overlay hidden">
        <aside class="sidebar-drawer">
            <div class="sidebar-header">
                <h2>IdeaHub</h2>
                <p>Welcome, <?= htmlspecialchars($username); ?></p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../ProjectStatus/project_dashboard_kanban.php"><i class="fas fa-diagram-project"></i> Project Status</a></li>
                <li><a href="idea.php"><i class="fas fa-lightbulb"></i> Ideas</a></li>
                <li><a href="../Collab/invite.php"><i class="fas fa-users"></i> Collaboration</a></li>
                <li><a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </nav>
        </aside>
    </div>

    <!-- Main Content -->
    <main class="main-wrapper">
        <!-- Hero Banner -->
        <div class="idea-hero" style="background:#fefefe; border-left: 5px solid var(--primary-color); padding: 20px; margin-bottom: 30px;">
            <h1><?= htmlspecialchars($project['title']) ?></h1>
            <p class="subtitle"><?= nl2br(htmlspecialchars(substr($project['description'], 0, 150))) ?></p>

            <div class="meta" style="margin-top: 10px;">
                <span class="badge"><?= ucfirst($project['category'] ?? 'Uncategorized') ?></span>
                <span class="badge"><?= $project['visibility'] === 'public' ? '🌍 Public' : '🔒 Private' ?></span>
                <?php if (!empty($project['tags'])): ?>
                    <span class="badge">#<?= htmlspecialchars($project['tags']) ?></span>
                <?php endif; ?>
            </div>
            <p style="margin-top: 10px; font-style: italic; color: #555;">
                <i class="fas fa-user"></i> Created by <strong><?= htmlspecialchars($project['creator_name']) ?></strong>
            </p>
            <div style="margin-top: 15px;">
                <?php if ($project['collab']): ?>
                    <span class="badge" style="background:#2A9D8F;color:white;"><i class="fas fa-user-plus"></i> Open for Collaboration</span>
                    <!-- Optional Join Button -->
                    <a href="#" class="project-link" style="margin-left: 15px;">
                        <button style="padding: 8px 16px; background:#921224; color:white; border:none; border-radius: 5px; cursor: pointer;">
                            <i class="fas fa-handshake"></i> I Want to Collaborate
                        </button>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Detailed Description -->
        <section class="idea-details">
            <h2>Description</h2>
            <div class="idea-detail-box" style="line-height:1.6; margin-bottom: 30px;">
                <?= nl2br(htmlspecialchars($project['description'])) ?>
            </div>

            <?php if (!empty($project['file'])): ?>
                <p><i class="fas fa-paperclip"></i> <strong>Attachment:</strong>
                    <a href="<?= htmlspecialchars($project['file']) ?>" target="_blank"><?= basename($project['file']) ?></a>
                </p>
            <?php endif; ?>
        </section>

        <!-- Collaborators -->
        <section class="collaborators">
            <h2>👥 Collaborators</h2>
            <?php
                $stmt = $conn->prepare("
                    SELECT u.username FROM users u
                    JOIN project_collaborators pc ON u.id = pc.user_id
                    WHERE pc.project_id = ?
                ");
                $stmt->bind_param("i", $projectId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo "<ul>";
                    while ($row = $result->fetch_assoc()) {
                        echo "<li>" . htmlspecialchars($row['username']) . "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p>No collaborators yet.</p>";
                }
            ?>
        </section>

        <!-- Actions -->
        <?php if ($isOwner): ?>
            <section style="margin-top: 30px;">
                <a href="edit.php?id=<?= $projectId ?>"><button><i class="fas fa-pen"></i> Edit</button></a>
                <a href="delete.php?id=<?= $projectId ?>" onclick="return confirm('Delete this project?');">
                    <button class="danger"><i class="fas fa-trash-alt"></i> Delete</button>
                </a>
                <a href="#"><button><i class="fas fa-user-plus"></i> Add Collaborator</button></a>
            </section>
        <?php endif; ?>
    </main>
</div>

<script>
const toggleBtn = document.getElementById('sidebarToggle');
const overlay = document.getElementById('sidebarOverlay');
toggleBtn.addEventListener('click', () => overlay.classList.toggle('hidden'));
overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.add('hidden'); });
document.addEventListener('keydown', e => { if (e.key === "Escape") overlay.classList.add('hidden'); });
</script>

</body>
</html>