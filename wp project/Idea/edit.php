<?php
$index = isset($_GET["index"]) ? intval($_GET["index"]) : null;
$ideaFile = "ideas.json";

if (!isset($index) || !file_exists($ideaFile)) {
    die("Invalid access: index or file not found.");
}

$ideas = json_decode(file_get_contents($ideaFile), true);
if (!isset($ideas[$index])) {
    die("Invalid access: idea does not exist.");
}


$idea = $ideas[$index];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"];
    $short_desc = $_POST["short_desc"] ?? '';
    $detailed_desc = $_POST["detailed_desc"] ?? '';    
    $link = $_POST["link"];
    $tags = $_POST["tags"] ?? '';
    $category = $_POST["category"] ?? 'Others';
    $status = $_POST["status"] ?? 'in_progress';
    $collab = isset($_POST["collab"]) ? true : false;
    $visibility = $_POST["visibility"] ?? 'public';


    $filePath = $idea["file"];
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

    $ideas[$index] = [
        "title" => $title,
        "short_desc" => $short_desc,
        "detailed_desc" => $detailed_desc,
        "link" => $link,
        "tags" => $tags,
        "category" => $category,
        "status" => $status,
        "collab" => $collab,
        "visibility" => $visibility,
        "file" => $filePath

    ];
    
    file_put_contents($ideaFile, json_encode($ideas));
    header("Location: index.php");
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
        <p class="subtitle"><b>Update the details of your shared idea below.<b></p>
        <form method="POST" enctype="multipart/form-data" class="styled-form">
            <label for="title"><i class="fas fa-heading"></i> Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($idea['title'] ?? '') ?>" required>

            <label for="description"><i class="fas fa-align-left"></i> Description</label>
            <textarea name="short_desc" rows="2" required><?= htmlspecialchars($idea['short_desc'] ?? '') ?></textarea>
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
                    <a href="delete_file.php?index=<?= $index ?>&file=<?= urlencode($idea['file']) ?>" onclick="return confirm('Delete this file only?')">Delete File</a>
                </p>
            <?php else: ?>
                <p><em>No file uploaded yet.</em></p>
            <?php endif; ?>

            <label for="upload_file"><i class="fas fa-upload"></i> Upload New File (optional)</label>
            <input type="file" id="upload_file" name="upload_file">

            <label for="status"><i class="fas fa-tasks"></i> Status</label>
                <select id="status" name="status">
                    <option value="draft" <?= ($idea['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="in_progress" <?= ($idea['status'] ?? 'in_progress') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="completed" <?= ($idea['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
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
                <a href="index.php"><button type="button"><i class="fas fa-arrow-left"></i> Back</button></a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
