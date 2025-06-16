<?php
$index = $_GET["index"] ?? null;
$fileToDelete = $_GET["file"] ?? null;
$ideaFile = "ideas.json";

if (!isset($index, $fileToDelete) || !file_exists($ideaFile)) {
    die("Invalid request.");
}

$ideas = json_decode(file_get_contents($ideaFile), true);
if (!isset($ideas[$index])) {
    die("Idea not found.");
}

// Secure file deletion
$baseDir = realpath("uploads");
$realPath = realpath($fileToDelete);

if ($realPath && strpos($realPath, $baseDir) === 0 && file_exists($realPath)) {
    unlink($realPath);
}

// Remove the file path from the idea
if (isset($ideas[$index]["file"]) && $ideas[$index]["file"] === $fileToDelete) {
    $ideas[$index]["file"] = "";
}

file_put_contents($ideaFile, json_encode($ideas));

// ✅ Redirect back to edit
header("Location: edit.php?index=$index");
exit();
