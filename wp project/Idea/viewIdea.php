<?php
session_start();
$index = $_GET['index'] ?? null;
$ideasFile = 'ideas.json';

if (!isset($index) || !file_exists($ideasFile)) {
    die("Invalid access.");
}

$ideas = json_decode(file_get_contents($ideasFile), true);
$idea = $ideas[$index] ?? null;

if (!$idea) {
    die("Idea not found.");
}

$isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == ($idea['owner_id'] ?? null);
$username = $_SESSION['username'] ?? 'Guest';
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idea_index = $_POST['idea_index'];
    $name = $_POST['name'];
    $comment = $_POST['comment'];
    $date = date("Y-m-d H:i");

    $commentsFile = 'comments.json';
    $comments = file_exists($commentsFile) ? json_decode(file_get_contents($commentsFile), true) : [];

    $comments[] = [
        'idea_index' => (int)$idea_index,
        'name' => $name,
        'comment' => $comment,
        'date' => $date
    ];

    file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT));
    header("Location: viewIdea.php?index=" . $idea_index);
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($idea['title']) ?> | IdeaHub</title>
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
    <main class="main-wrapper">
        <!-- Hero Banner -->
        <div class="idea-hero" style="background:#fefefe; border-left: 5px solid var(--primary-color); padding: 20px; margin-bottom: 30px;">
            <h1><?= htmlspecialchars($idea['title']) ?></h1>
            <p class="subtitle"><?= htmlspecialchars($idea['short_desc']) ?></p>

            <div class="meta" style="margin-top: 10px;">
                <span class="badge"><?= ucfirst($idea['category'] ?? 'Uncategorized') ?></span>
                <span class="badge"><?= ucfirst($idea['status'] ?? 'Draft') ?></span>
                <span class="badge"><?= ($idea['visibility'] ?? 'public') === 'public' ? '🌍 Public' : '🔒 Private' ?></span>
                <?php if (!empty($idea['tags'])): ?>
                    <span class="badge">#<?= htmlspecialchars($idea['tags']) ?></span>
                <?php endif; ?>
            </div>

            <div style="margin-top: 15px;">
                <?php if (!empty($idea['collab'])): ?>
                    <span class="badge" style="background:#2A9D8F;color:white;"><i class="fas fa-user-plus"></i> Open for Collaboration</span>
                    <a href="join_idea.php?index=<?= $index ?>" class="project-link" style="margin-left: 15px;">
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
                <?= nl2br(htmlspecialchars($idea['detailed_desc'] ?? 'No description provided yet.')) ?>
            </div>

            <?php if (!empty($idea['file'])): ?>
                <p><i class="fas fa-paperclip"></i> <strong>Attachment:</strong>
                    <a href="<?= htmlspecialchars($idea['file']) ?>" target="_blank"><?= basename($idea['file']) ?></a>
                </p>
            <?php endif; ?>
        </section>

        <?php if (!empty($idea['preview_image'])): ?>
    <img src="<?= htmlspecialchars($idea['preview_image']) ?>" style="max-width: 100%; border-radius: 8px; margin: 20px 0;">
<?php endif; ?>


        <!-- Comments Section -->
<section class="comments">
    <h2>💬 Comments</h2>

    <?php
    $commentsFile = 'comments.json';
    $comments = file_exists($commentsFile) ? json_decode(file_get_contents($commentsFile), true) : [];
    $ideaComments = array_filter($comments, fn($c) => $c['idea_index'] == $index);
    ?>

    <?php if (!empty($ideaComments)): ?>
        <ul style="margin-bottom: 20px;">
            <?php foreach ($ideaComments as $c): ?>
                <li>
                    <strong><?= htmlspecialchars($c['name']) ?>:</strong>
                    <?= htmlspecialchars($c['comment']) ?>
                    <small style="color:gray; display:block;"><?= $c['date'] ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No comments yet. Be the first!</p>
    <?php endif; ?>

    <form action="comment_post.php" method="POST" style="margin-top: 20px;">
        <input type="hidden" name="idea_index" value="<?= $index ?>">
        <input type="text" name="name" placeholder="Your Name" required style="padding: 8px;">
        <textarea name="comment" placeholder="Write a comment..." required style="width:100%; padding: 8px; margin-top:10px;"></textarea>
        <button type="submit" style="margin-top:10px;">Post Comment</button>
    </form>
</section>


        <!-- Collaborators -->
        <section class="collaborators">
            <h2>👥 Collaborators</h2>
            <?php if (!empty($idea['collaborators'])): ?>
                <ul>
                    <?php foreach ($idea['collaborators'] as $collab): ?>
                        <li><?= htmlspecialchars($collab['name']) ?> - <?= htmlspecialchars($collab['role'] ?? 'Member') ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No collaborators yet.</p>
            <?php endif; ?>
        </section>

        <!-- Actions -->
        <?php if ($isOwner): ?>
            <section style="margin-top: 30px;">
                <a href="edit.php?index=<?= $index ?>"><button><i class="fas fa-pen"></i> Edit</button></a>
                <a href="delete.php?index=<?= $index ?>" onclick="return confirm('Delete this idea?');">
                    <button class="danger"><i class="fas fa-trash-alt"></i> Delete</button>
                </a>
                <a href="add_collab.php?index=<?= $index ?>"><button><i class="fas fa-user-plus"></i> Add Collaborator</button></a>
            </section>
        <?php endif; ?>

    </main>
</div>

<!-- Sidebar script -->
<script>
const toggleBtn = document.getElementById('sidebarToggle');
const overlay = document.getElementById('sidebarOverlay');
toggleBtn.addEventListener('click', () => overlay.classList.toggle('hidden'));
overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.add('hidden'); });
document.addEventListener('keydown', e => { if (e.key === "Escape") overlay.classList.add('hidden'); });
</script>

</body>
</html>
