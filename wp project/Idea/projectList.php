<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Load ideas.json (your project list)
$ideas = [];
$idea_file = "ideas.json";
if (file_exists($idea_file)) {
    $ideas = json_decode(file_get_contents($idea_file), true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Projects | IdeaPlatform</title>
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
        <header class="main-header">
            <h1>My Projects</h1>
        </header>

        <section class="projects-section">
            <?php if (empty($ideas)): ?>
                <p>No projects found.</p>
            <?php else: ?>
                <div class="projects-grid">
                    <?php foreach ($ideas as $index => $project): ?>
                        <div class="project-card">
                            <h3><?= htmlspecialchars($project['title'] ?? 'Untitled') ?></h3>
                            <p><?= htmlspecialchars($project['short_desc'] ?? 'No description.') ?></p>

                            <div class="project-meta">
                                <span class="badge <?= ($project['visibility'] ?? 'public') === 'public' ? 'badge-public' : 'badge-private' ?>">
                                    <i class="fas fa-eye<?= ($project['visibility'] ?? 'public') === 'private' ? '-slash' : '' ?>"></i>
                                    <?= ucfirst($project['visibility'] ?? 'public') ?>
                                </span>

                                <?php if (!empty($project['file']) && file_exists($project['file'])): ?>
                                    <span class="badge file">
                                        <i class="fas fa-file"></i>
                                        <a href="<?= $project['file'] ?>" target="_blank">Download</a>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($project['tags'])): ?>
                                    <?php
                                        $tags = explode(',', $project['tags']);
                                        foreach ($tags as $tag):
                                    ?>
                                        <span class="badge"><?= htmlspecialchars(trim($tag)) ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <a href="edit.php?index=<?= $index ?>" class="project-link"><i class="fas fa-edit"></i> Edit</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

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

        </section>
    </main>
</div>
</body>
</html>
