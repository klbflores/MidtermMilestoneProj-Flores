<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_check.php';
require_auth();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/Validator.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $recipeId = (int)($_POST['recipe_id'] ?? 0);
    $commentText = Validator::sanitize($_POST['comment'] ?? '');
    $commentId = (int)($_POST['comment_id'] ?? 0);
    $userId = current_user_id();

    if ($action === 'create' && $recipeId > 0 && Validator::validateString($commentText, 1)) {
        $stmt = $pdo->prepare("INSERT INTO comments (recipe_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$recipeId, $userId, $commentText]);
    } elseif ($action === 'edit' && $commentId > 0 && Validator::validateString($commentText, 1)) {
        $stmt = $pdo->prepare("UPDATE comments SET comment = ?, is_edited = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$commentText, $commentId, $userId]);
    } elseif ($action === 'delete' && $commentId > 0) {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $stmt->execute([$commentId, $userId]);
    }

    header("Location: recipe_detail.php?id=" . $recipeId);
    exit;
}