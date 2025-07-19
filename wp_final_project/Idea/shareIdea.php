<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/Login/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$search = $_GET['q'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

// ✅ Build SQL dynamically
$sql = "SELECT * FROM projects WHERE visibility = 'public'";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

if (!empty($categoryFilter)) {
    $sql .= " AND category = ?";
    $params[] = $categoryFilter;
    $types .= "s";
}

// ✅ Prepare and execute
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$filteredIdeas = $result->fetch_all(MYSQLI_ASSOC);
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
    <!-- Sidebar Toggle -->
    <button id="sidebarToggle" class="sidebar-toggle" aria-label="Open Menu"><i class="fas fa-bars"></i></button>

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
                    <li><a href="../Collab/collaborators.php"><i class="fas fa-users"></i> Collaboration</a></li>
                    <li><a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
    </div>

    <main class="main-wrapper">
        <h1>Explore Public Ideas</h1>

        <form method="GET" class="search-filter-form">
            <input type="text" name="q" placeholder="Search ideas..." value="<?= htmlspecialchars($search) ?>">
            <select name="category">
                <option value="">All Categories</option>
                <?php
                $categories = ['Technology', 'Education', 'Health', 'Social', 'Others'];
                foreach ($categories as $cat) {
                    $selected = ($categoryFilter === $cat) ? 'selected' : '';
                    echo "<option value=\"$cat\" $selected>$cat</option>";
                }
                ?>
            </select>
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>

        <div class="ideas-grid">
            <?php if (empty($filteredIdeas)): ?>
                <p>No ideas found.</p>
            <?php else: ?>
                <?php foreach ($filteredIdeas as $idea): ?>
                    <div class="idea-card">
                        <h3><?= htmlspecialchars($idea['title']) ?></h3>
                        <p><strong><?= htmlspecialchars(substr($idea['description'], 0, 100)) ?>...</strong></p>
                        <div class="meta">
                            <span class="badge"><?= htmlspecialchars($idea['category']) ?></span>
                            <span class="badge"><?= htmlspecialchars($idea['visibility']) ?></span>
                            <?php if (!empty($idea['tags'])): ?>
                                <span class="badge">#<?= htmlspecialchars($idea['tags']) ?></span>
                            <?php endif; ?>
                            <span class="badge"><?= date("Y-m-d", strtotime($idea['created_at'])) ?></span>
                        </div>
                        <a href="Idea/viewIdea.php?id=<?= $idea['id'] ?>" class="project-link">
                            <i class="fas fa-eye"></i> View Idea
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
// Sidebar JS
document.getElementById('sidebarToggle').addEventListener('click', () => {
    document.getElementById('sidebarOverlay').classList.toggle('hidden');
});
document.getElementById('sidebarOverlay').addEventListener('click', (e) => {
    if (e.target.id === 'sidebarOverlay') {
        e.target.classList.add('hidden');
    }
});
document.addEventListener('keydown', function(e) {
    if (e.key === "Escape") {
        document.getElementById('sidebarOverlay').classList.add('hidden');
    }
});
</script>
</body>
</html>