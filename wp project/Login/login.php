<?php
session_start();
require_once __DIR__ . '/../db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION["user_id"] = $user['id'];
            $_SESSION["username"] = $user['username'];
            header("Location: ../dashboard/dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password!";
        }
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login - IdeaHub</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="login-container">

    <!-- Left Side -->
    <div class="login-form-section">
        <div class="login-box">
            <h2>Log In</h2>

            <?php if (!empty($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>

                <button type="submit">Login</button>
            </form>

            <div class="register-prompt">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>

    <!-- Right Side -->
    <div class="branding-section">
        <img src="https://raw.githubusercontent.com/wannurraudhah/project_IdeaHub/main/logo_white.png" alt="IdeaHub Logo" class="branding-logo" />
        
        <p>ignite ideas</p>
    </div>
</div>
</body>
</html>
