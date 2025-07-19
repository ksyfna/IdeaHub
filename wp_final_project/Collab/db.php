<?php
$servername = "sql112.infinityfree.com";
$username = "if0_39246393";
$password = "PakGembus";
$dbname = "if0_39246393_pakgembus";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
