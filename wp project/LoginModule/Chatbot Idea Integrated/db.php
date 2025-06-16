<?php
$host = "localhost";
$user = "root";
$pass = ""; // Your MySQL password
$db   = "idea_platform";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
