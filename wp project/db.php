<?php
$host = "localhost";
$user = "root";
$password = "";  // leave empty if using default XAMPP config
$database = "idea_platform";  // make sure this DB exists

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
