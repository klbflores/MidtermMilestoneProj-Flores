<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_check.php';
require_guest();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/AuthService.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $auth = new AuthService($pdo);
    $user = $auth->login($email, $password);

    if ($user) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Incorrect email or password combination.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TipidKusina</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <main class="auth-card">
        <h1>🍳 TipidKusina</h1>
        <p class="subtitle">Log in to view & share budget recipes</p>

        <?php if (!empty($_GET['registered'])): ?>
            <div class="alert alert-success">Account created! You can now log in.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><p><?= htmlspecialchars($error) ?></p></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Log In</button>
        </form>
        <p class="auth-footer">New home cook? <a href="register.php">Register here</a></p>
    </main>
</body>
</html>