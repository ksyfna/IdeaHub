<?php
session_start();

require '../db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/Login/login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) {
    die("Invalid access: ID not provided.");
}

// Fetch project details
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$idea = $result->fetch_assoc();

if (!$idea) {
    die("Idea not found.");
}

$isCreator = $idea['created_by'] == $_SESSION['user_id'];

// Check if user is a collaborator
$isCollaborator = false;
$stmt = $conn->prepare("SELECT 1 FROM project_collaborators WHERE project_id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $isCollaborator = true;
}

if (!$isCreator && !$isCollaborator) {
    die("Access denied: You are not allowed to edit this project.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"];
    $description = $_POST["description"] ?? '';
    $details = $_POST["detailed_desc"] ?? '';
    $link = $_POST["link"] ?? '';
    $tags = $_POST["tags"] ?? '';
    $category = $_POST["category"] ?? 'Others';
    $status = $_POST["status"] ?? 'Not Started';
    $visibility = $_POST["visibility"] ?? 'public';
    $collab = isset($_POST["collab"]) ? 1 : 0;
    $filePath = $idea["file"];

    // Handle file upload
    if (!empty($_FILES["upload_file"]["name"])) {
        if (!empty($filePath) && file_exists($filePath)) {
            unlink($filePath);
        }
        $fileName = basename($_FILES["upload_file"]["name"]);
        $targetDir = "uploads/";
        $newPath = $targetDir . time() . "_" . $fileName;
        move_uploaded_file($_FILES["upload_file"]["tmp_name"], $newPath);
        $filePath = $newPath;
    }

    // Update the database
    $stmt = $conn->prepare("UPDATE projects SET title = ?, description = ?, detailed_desc = ?, link = ?, tags = ?, category = ?, status = ?, visibility = ?, file = ?, collab = ? WHERE id = ?");
    $stmt->bind_param("ssssssssssi", $title, $description, $details, $link, $tags, $category, $status, $visibility, $filePath, $collab, $id);
    $stmt->execute();

    header("Location: projectList.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Idea</title>
    <link rel="stylesheet" href="../Idea/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="container">
    <div class="edit-form-wrapper">
        <h2><i class="fas fa-pen-to-square"></i> Edit Idea</h2>
        <p class="subtitle"><b>Update the details of your shared idea below.</b></p>
        <form method="POST" action="edit.php?id=<?= $id ?>" enctype="multipart/form-data" class="styled-form">
            <label for="title"><i class="fas fa-heading"></i> Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($idea['title'] ?? '') ?>" required>

            <label for="description"><i class="fas fa-align-left"></i> Description</label>
            <textarea name="description" rows="2" required><?= htmlspecialchars($idea['description'] ?? '') ?></textarea>

            <label for="detailed_desc"><i class="fas fa-align-left"></i> Detailed Description</label>
            <textarea name="detailed_desc" rows="5"><?= htmlspecialchars($idea['detailed_desc'] ?? '') ?></textarea>

            <label for="tags"><i class="fas fa-tags"></i> Tags</label>
            <input type="text" name="tags" value="<?= htmlspecialchars($idea['tags'] ?? '') ?>">

            <label for="category"><i class="fas fa-folder-open"></i> Category</label>
            <select name="category">
                <option value="Technology" <?= ($idea['category'] ?? '') === 'Technology' ? 'selected' : '' ?>>Technology</option>
                <option value="Education" <?= ($idea['category'] ?? '') === 'Education' ? 'selected' : '' ?>>Education</option>
                <option value="Health" <?= ($idea['category'] ?? '') === 'Health' ? 'selected' : '' ?>>Health</option>
                <option value="Social" <?= ($idea['category'] ?? '') === 'Social' ? 'selected' : '' ?>>Social Impact</option>
                <option value="Others" <?= ($idea['category'] ?? '') === 'Others' ? 'selected' : '' ?>>Others</option>
            </select>

            <label for="link"><i class="fas fa-link"></i> External Link</label>
            <input type="text" name="link" value="<?= htmlspecialchars($idea['link'] ?? '') ?>">

            <?php if (!empty($idea['file'])): ?>
                <p><strong><i class="fas fa-paperclip"></i> Current File:</strong>
                    <a href="<?= $idea['file'] ?>" target="_blank"><?= basename($idea['file']) ?></a> |
                    <a href="delete_file.php?id=<?= $id ?>&file=<?= urlencode($idea['file']) ?>" onclick="return confirm('Delete this file only?')">Delete File</a>
                </p>
            <?php else: ?>
                <p><em>No file uploaded yet.</em></p>
            <?php endif; ?>

            <label for="upload_file"><i class="fas fa-upload"></i> Upload New File (optional)</label>
            <input type="file" id="upload_file" name="upload_file">

            <label for="status"><i class="fas fa-tasks"></i> Status</label>
            <select id="status" name="status">
                <option value="Not Started" <?= ($idea['status'] ?? '') === 'Not Started' ? 'selected' : '' ?>>Not Started</option>
                <option value="In Progress" <?= ($idea['status'] ?? '') === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="Completed" <?= ($idea['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
            </select>

            <label for="collab"><i class="fas fa-users"></i> Accepting Collaborators?</label>
            <input type="checkbox" name="collab" id="collab" <?= !empty($idea['collab']) ? 'checked' : '' ?>>

            <label for="visibility"><i class="fas fa-eye"></i> Visibility</label>
            <select name="visibility" id="visibility">
                <option value="public" <?= ($idea['visibility'] ?? 'public') === 'public' ? 'selected' : '' ?>>Public</option>
                <option value="private" <?= ($idea['visibility'] ?? '') === 'private' ? 'selected' : '' ?>>Private</option>
            </select>

            <div class="form-buttons">
                <button type="submit"><i class="fas fa-save"></i> Update Idea</button>
                <a href="projectList.php"><button type="button"><i class="fas fa-arrow-left"></i> Back</button></a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
