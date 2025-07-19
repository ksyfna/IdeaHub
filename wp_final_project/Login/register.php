<?php
session_start();
require_once __DIR__ . '/../db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($username) < 4) {
        $errors['username'] = 'Username must be at least 4 characters';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    if (empty($errors)) {
        $check_user = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($check_user->num_rows > 0) {
            $errors['username'] = 'Username already taken';
        }

        $check_email = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check_email->num_rows > 0) {
            $errors['email'] = 'Email already registered';
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";

        if ($conn->query($sql) === TRUE) {
            $success = 'Registration successful! You can now login.';
            $username = $email = '';
        } else {
            $errors['database'] = 'Registration failed: ' . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register - IdeaHub</title>
  <style>
    :root {
      --primary: #921224;
      --background: #eaf3ec;
      --light-gray: #ccc;
      --white: #fff;
      --text: #333;
      --error: #b30000;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--white);
    }

    .login-container {
      display: flex;
      flex-direction: row;
      height: 100vh;
      width: 100%;
    }

    .login-form-section,
    .branding-section {
      flex: 1;
      min-width: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
    }

    .login-form-section {
      background-color: var(--white);
    }

    .login-box {
      width: 100%;
      max-width: 400px;
      background-color: var(--background);
      padding: 30px;
      border-radius: 12px;
      border: 1px solid var(--light-gray);
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    }

    .login-box h2 {
      text-align: center;
      color: var(--primary);
      margin-bottom: 20px;
    }

    .login-box input {
      width: 100%;
      padding: 12px;
      margin-bottom: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }

    .login-box button {
      width: 100%;
      padding: 12px;
      background-color: var(--primary);
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin-top: 10px;
    }

    .login-box button:hover {
      background-color: #750f1b;
    }

    .register-prompt {
      text-align: center;
      margin-top: 15px;
      color: var(--primary);
    }

    .register-prompt a {
      color: var(--primary);
      font-weight: bold;
      text-decoration: none;
    }

    .register-prompt a:hover {
      text-decoration: underline;
    }

    .error-message,
    .success-message {
      background-color: #ffe5e5;
      color: var(--error);
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 6px;
      text-align: center;
    }

    .success-message {
      background-color: #e6ffec;
      color: green;
    }

    .error-text {
      color: red;
      font-size: 0.85em;
      margin-bottom: 5px;
    }

    .branding-section {
      background-color: var(--primary);
      color: white;
      flex-direction: column;
      text-align: center;
    }

    .branding-section h1 {
      font-size: 3rem;
      margin-bottom: 10px;
    }

    .branding-section p {
      font-size: 1.5rem;
      font-style: italic;
    }

    .branding-logo {
      width: 280px;
      height: auto;
      animation: float 4s ease-in-out infinite;
      filter: drop-shadow(2px 4px 4px rgba(0, 0, 0, 0.3));
      margin-bottom: 20px;
    }

    @keyframes float {
      0% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0); }
    }

    @media (max-width: 768px) {
      .login-container {
        flex-direction: column;
      }

      .login-form-section, .branding-section {
        min-width: 100%;
        padding: 20px;
      }

      .branding-section h1 {
        font-size: 2rem;
      }

      .branding-section p {
        font-size: 1rem;
      }

      .login-box {
        padding: 20px;
      }

      .branding-logo {
        width: 180px;
      }
    }
  </style>
</head>
<body>

<div class="login-container">
  <!-- Left side: Form -->
  <div class="login-form-section">
    <div class="login-box">
      <h2>Register</h2>

      <?php if (!empty($success)): ?>
        <div class="success-message"><?= $success ?></div>
      <?php endif; ?>

      <?php if (!empty($errors['database'])): ?>
        <div class="error-message"><?= $errors['database'] ?></div>
      <?php endif; ?>

      <form method="POST" action="register.php">
        <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($username ?? '') ?>" required>
        <?php if (!empty($errors['username'])): ?>
          <div class="error-text"><?= $errors['username'] ?></div>
        <?php endif; ?>

        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email ?? '') ?>" required>
        <?php if (!empty($errors['email'])): ?>
          <div class="error-text"><?= $errors['email'] ?></div>
        <?php endif; ?>

        <input type="password" name="password" placeholder="Password" required>
        <?php if (!empty($errors['password'])): ?>
          <div class="error-text"><?= $errors['password'] ?></div>
        <?php endif; ?>

        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <?php if (!empty($errors['confirm_password'])): ?>
          <div class="error-text"><?= $errors['confirm_password'] ?></div>
        <?php endif; ?>

        <button type="submit">Register</button>
      </form>

      <div class="register-prompt">
        Already have an account? <a href="../index.php">Log in here</a>
      </div>
    </div>
  </div>

  <!-- Right side: Branding -->
  <div class="branding-section">
    <img src="https://raw.githubusercontent.com/wannurraudhah/project_IdeaHub/main/logo_white.png" alt="IdeaHub Logo" class="branding-logo" />
    <p>ignite ideas</p>
  </div>
</div>

</body>
</html>
