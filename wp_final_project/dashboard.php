<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Login/Login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user info
$user_query = $conn->query("SELECT * FROM users WHERE id = $user_id");
if (!$user_query || $user_query->num_rows === 0) {
    die("User not found.");
}
$user = $user_query->fetch_assoc();

// Load project data (owned + collaborated)
$projects = [];
$stmt = $conn->prepare("
    SELECT p.*, u.username AS creator_name
    FROM projects p
    JOIN users u ON p.created_by = u.id
    WHERE p.created_by = ?
       OR p.id IN (
           SELECT project_id FROM project_collaborators 
           WHERE user_id = ? AND status = 'active'
       )
       OR p.visibility = 'public'
    ORDER BY p.created_at DESC
    LIMIT 20
");

$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

// Count pending invitations
$inv_count = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM invitations WHERE recipient_id = $user_id AND status = 'pending'");
if ($row = $result->fetch_assoc()) {
    $inv_count = $row['total'];
}

// Count project statuses
$status_counts = [
    'Not Started' => 0,
    'In Progress' => 0,
    'Completed' => 0
];

foreach ($projects as &$project) {
    $status = strtolower(trim($project['status'] ?? 'not started'));

    if ($status === 'in progress') {
        $project['status'] = 'In Progress';
        $status_counts['In Progress']++;
    } elseif ($status === 'completed') {
        $project['status'] = 'Completed';
        $status_counts['Completed']++;
    } else {
        $project['status'] = 'Not Started';
        $status_counts['Not Started']++;
    }
}
unset($project);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - IdeaHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="Collab/Collab/inv_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>IdeaHub</h2>
            <p>Welcome, <?= htmlspecialchars($user['username']); ?></p>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li class="active"><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="ProjectStatus/project_dashboard_kanban.php"><i class="fas fa-project-diagram"></i> Project Status</a></li>
                <li><a href="Idea/idea.php"><i class="fas fa-lightbulb"></i> Ideas</a></li>
                <li><a href="Collab/invite.php"><i class="fas fa-handshake"></i> Collaboration</a></li>
                <li><a href="Login/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="main-header">
            <h1>Dashboard</h1>
            <div class="user-actions">
                <a href="Collab/view_invitations.php" class="notification-badge" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-bell"></i>
                    <span><?= $inv_count ?></span>
                </a>
                <div class="user-profile">
                    <img src="https://t4.ftcdn.net/jpg/02/15/84/43/240_F_215844325_ttX9YiIIyeaR7Ne6EaLLjMAmy4GvPC69.jpg" alt="User Profile">
                </div>
            </div>
        </header>

        <!-- Stats -->
        <section class="stats-section">
            <div class="stat-card"><div class="stat-icon" style="background-color: #BCE0DA;"><i class="fas fa-lightbulb" style="color: #921224;"></i></div><div class="stat-info"><h3>Total Projects</h3><p><?= count($projects); ?></p></div></div>
            <div class="stat-card"><div class="stat-icon" style="background-color: #FFE5D9;"><i class="fas fa-tasks" style="color: #E76F51;"></i></div><div class="stat-info"><h3>In Progress</h3><p><?= $status_counts['In Progress']; ?></p></div></div>
            <div class="stat-card"><div class="stat-icon" style="background-color: #D8E2DC;"><i class="fas fa-check-circle" style="color: #2A9D8F;"></i></div><div class="stat-info"><h3>Completed</h3><p><?= $status_counts['Completed']; ?></p></div></div>
            <div class="stat-card"><div class="stat-icon" style="background-color: #F4ACB7;"><i class="fas fa-user-friends" style="color: #9D8189;"></i></div><div class="stat-info"><h3>Total Users</h3><p>5</p></div></div>
        </section>

        <!-- Recent Projects -->
        <section class="projects-section">
            <div class="section-header">
                <h2>All Projects</h2>
                <a href="Idea/projectList.php" class="view-all">Your Projects</a>
            </div>
            <div class="projects-grid">
                <?php foreach (array_slice($projects, 0, 4) as $project): ?>
    <div class="project-card">
        <h3><?= htmlspecialchars($project['title'] ?? 'Untitled'); ?></h3>
        <div class="project-meta">
            <span class="status-badge <?= strtolower(str_replace(' ', '-', $project['status'])) ?>">
                <?= htmlspecialchars($project['status']); ?>
            </span>

            <?php
                $visibility = $project['visibility'] ?? 'public';
                $visClass = $visibility === 'private' ? 'badge-private' : 'badge-public';
                $visIcon = $visibility === 'private' ? 'fa-eye-slash' : 'fa-eye';
            ?>
            <span class="badge <?= $visClass ?>">
                <i class="fas <?= $visIcon ?>"></i> <?= ucfirst($visibility) ?>
            </span>
        </div>
        <p><?= htmlspecialchars(substr($project['description'] ?? '', 0, 100)); ?>...</p>
        <a href="Idea/viewIdea.php?id=<?= $project['id'] ?>" class="project-link">View Project</a>
    </div>
<?php endforeach; ?>

            </div>
        </section>

        <!-- Quick Actions -->
        <section class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="Idea/add.php" class="action-btn"><i class="fas fa-plus"></i><span>Add New Idea</span></a>
                <a href="ProjectStatus/project_dashboard_kanban.php" class="action-btn"><i class="fas fa-project-diagram"></i><span>Project Status</span></a>
                <a href="Collab/invite.php" class="action-btn"><i class="fas fa-user-plus"></i><span>Invite Collaborator</span></a>
                <a href="#" class="action-btn"><i class="fas fa-file-export"></i><span>Generate Report</span></a>
            </div>
        </section>
    </main>
</div>
</body>
</html>