<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/FavoriteService.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$recipeId = (int)($input['recipe_id'] ?? 0);

if ($recipeId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid recipe ID']);
    exit;
}

$favService = new FavoriteService($pdo);
$favorited = $favService->toggle(current_user_id(), $recipeId);

echo json_encode(['success' => true, 'favorited' => $favorited]);