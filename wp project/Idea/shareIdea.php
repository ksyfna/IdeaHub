<?php
session_start();
$ideasFile = 'ideas.json';
$ideas = [];

if (file_exists($ideasFile)) {
    $ideas = json_decode(file_get_contents($ideasFile), true);
}

$search = $_GET['q'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

// Only public ideas
$publicIdeas = array_filter($ideas, fn($idea) => ($idea['visibility'] ?? 'public') === 'public');

// Filter ideas
$filteredIdeas = array_filter($publicIdeas, function ($idea) use ($search, $categoryFilter) {
    $matchesSearch = stripos($idea['title'], $search) !== false || stripos($idea['short_desc'], $search) !== false;
    $matchesCategory = $categoryFilter === '' || ($idea['category'] ?? '') === $categoryFilter;
    return $matchesSearch && $matchesCategory;
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Explore Ideas</title>
    <link rel="stylesheet" href="css/shareIdea.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
</head>


<body>
<div class="dashboard-container">
    <!-- Sidebar Toggle Button --><button id="sidebarToggle" class="sidebar-toggle" aria-label="Open Menu">
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

    <!--  Main Content -->
    <main class="main-wrapper">
        <h1>Explore Public Ideas</h1>

        <form method="GET" class="search-filter-form">
            <input type="text" name="q" placeholder="Search ideas..." value="<?= htmlspecialchars($search) ?>">
            <select name="category">
                <option value="">All Categories</option>
                <option value="Technology" <?= $categoryFilter === 'Technology' ? 'selected' : '' ?>>Technology</option>
                <option value="Education" <?= $categoryFilter === 'Education' ? 'selected' : '' ?>>Education</option>
                <option value="Health" <?= $categoryFilter === 'Health' ? 'selected' : '' ?>>Health</option>
                <option value="Social" <?= $categoryFilter === 'Social' ? 'selected' : '' ?>>Social Impact</option>
                <option value="Others" <?= $categoryFilter === 'Others' ? 'selected' : '' ?>>Others</option>
            </select>
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>

        <div class="ideas-grid">
            <?php if (empty($filteredIdeas)): ?>
                <p>No ideas found.</p>
            <?php else: ?>
                <?php foreach ($filteredIdeas as $index => $idea): ?>
                    <div class="idea-card">
                        <h3><?= htmlspecialchars($idea['title']) ?></h3>
                        <p><strong><?= htmlspecialchars($idea['short_desc']) ?></strong></p>
                        <div class="meta">
                            <span class="badge"><?= htmlspecialchars($idea['category'] ?? 'Others') ?></span>
                            <span class="badge"><?= htmlspecialchars($idea['visibility'] ?? 'public') ?></span>
                            <?php if (!empty($idea['tags'])): ?>
                                <span class="badge">#<?= htmlspecialchars($idea['tags']) ?></span>
                            <?php endif; ?>
                            <span class="badge"><?= date("Y-m-d") ?></span>
                        </div>
                        <a href="viewIdea.php?index=<?= $index ?>" class="project-link">
                            <i class="fas fa-eye"></i> View Idea
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

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
