<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$projects = [];
$project_path = 'ideas.json';

if (file_exists($project_path)) {
    $projects = json_decode(file_get_contents($project_path), true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IdeaPlatform Dashboard</title>
    <link rel="stylesheet" href="../Idea/css/index.css">
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
            <img src="https://raw.githubusercontent.com/wannurraudhah/project_IdeaHub/main/logo_red.png" alt="IdeaHub Logo" class="logo">
                <h2>IdeaHub</h2>
                <p>Welcome, <?= htmlspecialchars($_SESSION['username']); ?></p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="../dashboard/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="index.php"><i class="fas fa-lightbulb"></i> Ideas</a></li>
                    <li><a href="../Idea/myprojects.php"><i class="fas fa-diagram-project"></i> Projects</a></li>
                    <li><a href="team.php"><i class="fas fa-users"></i> Team</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <div class = "mainn-wrapper">
            <!-- Header -->
            <header class="main-header">
                <h1>Idea</h1>
                <div class="user-actions">
                    <span class="notification-badge"><i class="fas fa-bell"></i><span>3</span></span>
                    <div class="user-profile">
                        <img src="../bakak.jpg" alt="User Profile">
                    </div>
                </div>
            </header>

            <!-- Quick Actions -->
            <section class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="add.php" class="action-btn">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add New Idea</span>
                    </a>
                    <a href="projectList.php" class="action-btn">
                        <i class="fas fa-project-diagram"></i>
                        <span>My Project</span>
                    </a>
                    <a href="invite_team.php" class="action-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>Invite Team</span>
                    </a>
                    <a href="shareIdea.php" class="action-btn">
                        <i class="fas fa-search"></i>
                        <span>Explore Ideas</span>
                    </a>
                </div>
            </section>

            <!-- Recently Added Projects -->
            <section class="projects-section" style="margin-top: 40px;">
                <div class="section-header">
                    <h2>Recently Added Projects</h2>
                    <a href="projects.php" class="view-all">View All</a>
                </div>
                <div class="projects-grid">
                    <?php
                    $recent = array_slice(array_reverse($projects), 0, 4);
                    foreach ($recent as $i => $project):
                        $actualIndex = array_search($project, $projects);
                        $status = strtolower($project['status'] ?? 'not-started');
                    ?>
                        <div class="project-card">
                            <h3><?= htmlspecialchars($project['title']) ?></h3>
                            <div class="project-meta">
                                <span class="status-badge <?= $status ?>">
                                    <?= ucfirst($project['status'] ?? 'Not Started') ?>
                                </span>
                                <?php if (!empty($project['deadline'])): ?>
                                    <span class="deadline">
                                        <i class="far fa-calendar-alt"></i>
                                        <?= htmlspecialchars($project['deadline']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p><?= htmlspecialchars(substr($project['short_desc'] ?? '', 0, 100)) ?>...</p>
                            <?php if ($actualIndex !== false): ?>
                                <a href="edit.php?index=<?= $actualIndex ?>" class="project-link">Edit Project</a>
                            <?php else: ?>
                                <em style="color: red;">Index not found</em>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
                            </div>
    </main>
</div>

<!-- JS for Sidebar -->
<script>
    const toggleBtn = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    toggleBtn.addEventListener('click', () => {
        overlay.classList.toggle('hidden');
    });

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === "Escape") {
            overlay.classList.add('hidden');
        }
    });
</script>

</body>
</html>
