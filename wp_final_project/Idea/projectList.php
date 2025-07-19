<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/Login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

$statusFilter = $_GET['status'] ?? '';
$sortOrder = $_GET['sort'] ?? '';

$sql = "
    SELECT p.*
    FROM projects p
    WHERE (
        p.created_by = ?
        OR p.id IN (
            SELECT project_id FROM project_collaborators 
            WHERE user_id = ? AND status = 'active'
        )
    )
";

$types = "ii";
$params = [$user_id, $user_id];

if (!empty($statusFilter)) {
    $sql .= " AND status = ?";
    $types .= "s";
    $params[] = $statusFilter;
}

if ($sortOrder === 'asc') {
    $sql .= " ORDER BY title ASC";
} elseif ($sortOrder === 'desc') {
    $sql .= " ORDER BY title DESC";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$projects = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Projects | IdeaPlatform</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: bold;
            color: white;
            border-radius: 10px;
            margin-bottom: 5px;
        }
        .in-progress { background-color: #2a9d8f; }
        .completed { background-color: #6c757d; }
        .not-started { background-color: #e76f51; }
    </style>
</head>
<body>
<div class="dashboard-container">

<button id="sidebarToggle" class="sidebar-toggle" aria-label="Open Menu">
    <i class="fas fa-bars"></i>
</button>

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
                <li><a href="../Collab/invite.php"><i class="fas fa-handshake"></i> Collaboration</a></li>
                <li><a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>
</div>

<main class="main-content">
    <header class="main-header">
        <h1>My Projects</h1>
    </header>

    <section class="projects-section">
        <form method="GET" style="margin-bottom: 20px;">
            <label for="status">Filter by Status:</label>
            <select name="status" id="status">
                <option value="">-- All --</option>
                <option value="Not Started" <?= $statusFilter === 'Not Started' ? 'selected' : '' ?>>Not Started</option>
                <option value="In Progress" <?= $statusFilter === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="Completed" <?= $statusFilter === 'Completed' ? 'selected' : '' ?>>Completed</option>
            </select>

            <label for="sort">Sort by Title:</label>
            <select name="sort" id="sort">
                <option value="">-- None --</option>
                <option value="asc" <?= $sortOrder === 'asc' ? 'selected' : '' ?>>A to Z</option>
                <option value="desc" <?= $sortOrder === 'desc' ? 'selected' : '' ?>>Z to A</option>
            </select>

            <button type="submit">Apply</button>
        </form>

        <?php if (empty($projects)): ?>
            <p>No projects found.</p>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                    <div class="project-card">
                        <h3><?= htmlspecialchars($project['title'] ?? 'Untitled') ?></h3>
                        <?php
                            $status = $project['status'] ?? 'Not Started';
                            $statusClass = strtolower(str_replace(' ', '-', $status));
                        ?>
                        <span class="status-badge <?= $statusClass ?>"><?= strtoupper($status) ?></span>
                        <p><?= htmlspecialchars(substr($project['description'], 0, 100)) ?>...</p>

                        <div class="project-meta">
                            <span class="badge <?= ($project['visibility'] ?? 'public') === 'public' ? 'badge-public' : 'badge-private' ?>">
                                <i class="fas fa-eye<?= ($project['visibility'] ?? 'public') === 'private' ? '-slash' : '' ?>"></i>
                                <?= ucfirst($project['visibility'] ?? 'public') ?>
                            </span>

                            <?php if (!empty($project['file'])): ?>
                                <span class="badge file">
                                    <i class="fas fa-file"></i>
                                    <a href="<?= htmlspecialchars($project['file']) ?>" target="_blank">Download</a>
                                </span>
                            <?php endif; ?>

                            <?php if (!empty($project['tags'])): ?>
                                <?php
                                    $tags = explode(',', $project['tags']);
                                    $tagCount = 0;
                                    foreach ($tags as $tag):
                                        if ($tagCount >= 3) break;
                                        $tagCount++;
                                    ?>
                                    <span class="badge"><?= htmlspecialchars(trim($tag)) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        </div>

                        <?php
                            $projectId = $project['id'];
                            $isCreator = $project['created_by'] == $user_id;

                            $isCollaborator = false;
                            $checkStmt = $conn->prepare("SELECT 1 FROM project_collaborators WHERE project_id = ? AND user_id = ?");
                            $checkStmt->bind_param("ii", $projectId, $user_id);
                            $checkStmt->execute();
                            $checkResult = $checkStmt->get_result();
                            if ($checkResult->num_rows > 0) {
                                $isCollaborator = true;
                            }

                            if ($isCreator || $isCollaborator):
                            ?>
                                <a href="edit.php?id=<?= $projectId ?>" class="project-link"><i class="fas fa-edit"></i> Edit</a>
                                <a href="delete.php?id=<?= $projectId ?>" class="project-link delete" onclick="return confirm('Are you sure you want to delete this project?');">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
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