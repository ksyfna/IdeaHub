<?php
session_start();
require_once __DIR__ . '/../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_query->fetch_assoc();

// Fetch projects/ideas
$projects = [];
$data_path = 'ideas.json';
if (file_exists($data_path)) {
    $json = file_get_contents($data_path);
    $projects = json_decode($json, true);
}

// Count projects by status
$status_counts = [
    'Not Started' => 0,
    'In Progress' => 0,
    'Completed' => 0
];

foreach ($projects as $project) {
    if (isset($project['status']) && isset($status_counts[$project['status']])) {
        $status_counts[$project['status']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | IdeaHub</title>
    <link rel="stylesheet" href="dashboard.css">
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
                <li><a href="../dashboard/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../Idea/index.php"><i class="fas fa-lightbulb"></i> Ideas</a></li>
                <li><a href="projects.php"><i class="fas fa-diagram-project"></i> Projects</a></li>
                <li><a href="team.php"><i class="fas fa-users"></i> Team</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>
</div>


        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <h1>Dashboard</h1>
                <div class="user-actions">
                    <span class="notification-badge"><i class="fas fa-bell"></i> <span>3</span></span>
                    <div class="user-profile">
                        <img src="https://via.placeholder.com/40" alt="User Profile">
                    </div>
                </div>
            </header>

            <!-- Stats -->
            <section class="stats-section">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #BCE0DA;">
                        <i class="fas fa-lightbulb" style="color: #921224;"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Ideas</h3>
                        <p><?php echo count($projects); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #FFE5D9;">
                        <i class="fas fa-tasks" style="color: #E76F51;"></i>
                    </div>
                    <div class="stat-info">
                        <h3>In Progress</h3>
                        <p><?php echo $status_counts['In Progress']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #D8E2DC;">
                        <i class="fas fa-check-circle" style="color: #2A9D8F;"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Completed</h3>
                        <p><?php echo $status_counts['Completed']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: #F4ACB7;">
                        <i class="fas fa-user-friends" style="color: #9D8189;"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Team Members</h3>
                        <p>5</p>
                    </div>
                </div>
            </section>

            <!-- Recent Projects -->
            <section class="projects-section">
                <div class="section-header">
                    <h2>Recent Projects</h2>
                    <a href="project_dashboard_kanban.php" class="view-all">View All</a>
                </div>
                <div class="projects-grid">
                    <?php 
                    $recent_projects = array_slice(array_reverse($projects), 0, 4);
                    foreach ($recent_projects as $project): 
                        $statusClass = strtolower(str_replace(' ', '-', $project['status'] ?? 'not-started'));
                    ?>
                    <div class="project-card">
                        <h3><?= htmlspecialchars($project['title']) ?></h3>
                        <div class="project-meta">
                            <span class="status-badge <?= $statusClass ?>">
                                <?= htmlspecialchars($project['status'] ?? 'Not Started') ?>
                            </span>
                            <?php if (!empty($project['deadline'])): ?>
                                <span class="deadline"><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($project['deadline']) ?></span>
                            <?php endif; ?>
                        </div>
                        <p><?= htmlspecialchars(substr($project['short_desc'] ?? $project['description'] ?? '', 0, 100)) ?>...</p>
                        <a href="project_dashboard_kanban.php" class="project-link">View Project</a>
                    </div>
                    <?php endforeach; ?>

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

                </div>
            </section>

        </main>
    </div>
</body>
</html>
