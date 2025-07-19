<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/Login/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$sql = "SELECT * FROM projects 
        WHERE visibility = 'public' 
           OR created_by = ? 
           OR FIND_IN_SET(?, collaborators)
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();

$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

$inv_count = 0;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $result = $conn->query("SELECT COUNT(*) as total FROM invitations WHERE recipient_id = $uid AND status = 'pending'");
    if ($row = $result->fetch_assoc()) {
        $inv_count = $row['total'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IdeaPlatform Dashboard</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar Toggle Button -->
    <button id="sidebarToggle" class="sidebar-toggle" aria-label="Open Menu">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div id="sidebarOverlay" class="sidebar-overlay hidden">
        <aside class="sidebar-drawer">
            <div class="sidebar-header">
                <h2>IdeaHub</h2>
                <p>Welcome, <?= htmlspecialchars($_SESSION['username']); ?></p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="../ProjectStatus/project_dashboard_kanban.php"><i class="fas fa-diagram-project"></i> Project Status</a></li>
                    <li><a href="idea.php"><i class="fas fa-lightbulb"></i> Ideas</a></li>
                    <li><a href="../Collab/invite.php"><i class="fas fa-users"></i> Collaboration</a></li>
                    <li><a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>

                </ul>
            </nav>
        </aside>
    </div>

    <!-- Main -->
    <main class="main-content">
        <header class="main-header">
            <h1>Idea</h1>
            <div class="user-actions">
                <a href="../Collab/view_invitations.php" class="notification-badge" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-bell"></i>
                    <span><?= $inv_count ?></span>
                </a>
                <div class="user-profile">
                    <img src="https://t4.ftcdn.net/jpg/02/15/84/43/240_F_215844325_ttX9YiIIyeaR7Ne6EaLLjMAmy4GvPC69.jpg" alt="User Profile">
                </div>
            </div>
        </header>

        <section class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="add.php" class="action-btn"><i class="fas fa-plus-circle"></i><span>Add New Idea</span></a>
                <a href="projectList.php" class="action-btn"><i class="fas fa-project-diagram"></i><span>My Project</span></a>
                <a href="../Collab/invite.php" class="action-btn"><i class="fas fa-user-plus"></i><span>Invite Team</span></a>
                <a href="shareIdea.php" class="action-btn"><i class="fas fa-search"></i><span>Explore Ideas</span></a>
            </div>
        </section>

        <section class="projects-section">
            <div class="section-header">
                <h2>Recently Added Projects</h2>
                <a href="projectList.php" class="view-all">View All</a>
            </div>
            <div class="projects-grid">
                <?php
                $recent = array_slice(array_reverse($projects), 0, 4);
                foreach ($recent as $project):
                    $status = strtolower($project['status'] ?? 'not-started');
                ?>
                    <div class="project-card">
                        <h3><?= htmlspecialchars($project['title']) ?></h3>
                        <div class="project-meta">
                            <span class="status-badge <?= $status ?>"><?= ucfirst($project['status'] ?? 'Not Started') ?></span>
                        </div>
                        <p><?= htmlspecialchars(substr($project['description'], 0, 100)) ?>...</p>
                        <a href="edit.php?id=<?= $project['id'] ?>" class="project-link">Edit Project</a>
                        <a href="delete.php?id=<?= $project['id'] ?>" class="project-link" style="color:red;" onclick="return confirm('Are you sure you want to delete this idea?');">Delete</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
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