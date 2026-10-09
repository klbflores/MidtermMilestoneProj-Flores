<?php
declare(strict_types=1);

class FavoriteService {
    public function __construct(private PDO $pdo) {}

    public function isFavorited(int $userId, int $recipeId): bool {
        $stmt = $this->pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$userId, $recipeId]);
        return (bool) $stmt->fetch();
    }

    public function toggle(int $userId, int $recipeId): bool {
        if ($this->isFavorited($userId, $recipeId)) {
            $stmt = $this->pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
            $stmt->execute([$userId, $recipeId]);
            return false; // Removed
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)");
            $stmt->execute([$userId, $recipeId]);
            return true; // Added
        }
    }

    public function getUserFavorites(int $userId): array {
        $stmt = $this->pdo->prepare("
            SELECT r.*, c.name AS category_name, u.name AS author_name 
            FROM favorites f
            JOIN recipes r ON f.recipe_id = r.id
            JOIN categories c ON r.category_id = c.id
            JOIN users u ON r.user_id = u.id
            WHERE f.user_id = ?
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}