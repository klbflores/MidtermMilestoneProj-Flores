<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_check.php';
require_auth();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/RecipeService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipeId = (int)($_POST['recipe_id'] ?? 0);
    $recipeService = new RecipeService($pdo);
    $recipeService->delete($recipeId, current_user_id());
}

header("Location: index.php");
exit;