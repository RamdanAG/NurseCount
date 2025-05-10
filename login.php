<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/style/login.css">
    <title>Login</title>
</head>
<body>
    <div class="bg">
        <div class="backgroundLogin">
            <h2 class="login">NURSECOUNT</h2>
            <form action="auth/login.php" method="POST">
                <input type="text" id="username" name="username" placeholder="Username" class="input-field" required>
                <input type="password" id="password" name="password" placeholder="Password" class="input-field" required>
                <button type="submit">Login</button>
            </form>
            <a href="register.php">Don't have an account? Sign up</a>
        </div>
    </div>
</body>
</html>