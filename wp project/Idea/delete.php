<?php
$index = $_GET["index"] ?? null;
$ideaFile = "ideas.json";

if (!isset($index) || !file_exists($ideaFile)) {
    die("Invalid request.");
}

$ideas = json_decode(file_get_contents($ideaFile), true);
if (!isset($ideas[$index])) {
    die("Idea not found.");
}

// Delete attached file (if any)
if (!empty($ideas[$index]["file"]) && file_exists($ideas[$index]["file"])) {
    unlink($ideas[$index]["file"]);
}

// Remove the idea from the list
unset($ideas[$index]);
$ideas = array_values($ideas); // Re-index

file_put_contents($ideaFile, json_encode($ideas));

// ✅ Redirect to main page
header("Location: index.php");
exit();
