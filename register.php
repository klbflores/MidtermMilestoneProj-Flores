<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_check.php';
require_guest();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/Validator.php';
require_once __DIR__ . '/classes/AuthService.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = Validator::sanitize($_POST['name'] ?? '');
    $email = Validator::sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $auth = new AuthService($pdo);
    $errors = $auth->register($name, $email, $password);

    if (empty($errors)) {
        header("Location: login.php?registered=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join TipidKusina - Barangay Student Meals</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <main class="auth-card">
        <h1>🍳 TipidKusina</h1>
        <p class="subtitle">Barangay Student Budget Meal Sharing</p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" novalidate id="register-form">
            <div class="form-group">
                <label for="name">Complete Name / Nickname</label>
                <input type="text" id="name" name="name" required minlength="2" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Student Email Address</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password (min. 6 characters)</label>
                <input type="password" id="password" name="password" minlength="6" required>
            </div>
            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>
        <p class="auth-footer">Already a member? <a href="login.php">Log in here</a></p>
    </main>
    <script src="app.js"></script>
</body>
</html>