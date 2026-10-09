<?php
declare(strict_types=1);

class RecipeService {
    public function __construct(private PDO $pdo) {}

    public function getCategories(): array {
        return $this->pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    }

    public function getLatest(string $search = '', int $categoryId = 0): array {
        $sql = "SELECT r.*, c.name AS category_name, u.name AS author_name 
                FROM recipes r
                JOIN categories c ON r.category_id = c.id
                JOIN users u ON r.user_id = u.id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (r.title LIKE ? OR r.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($categoryId > 0) {
            $sql .= " AND r.category_id = ?";
            $params[] = $categoryId;
        }

        $sql .= " ORDER BY r.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT r.*, c.name AS category_name, u.name AS author_name 
            FROM recipes r
            JOIN categories c ON r.category_id = c.id
            JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        $recipe = $stmt->fetch();
        return $recipe ?: null;
    }

    public function getIngredients(int $recipeId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM ingredients WHERE recipe_id = ? ORDER BY id ASC");
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public function create(int $userId, int $catId, string $title, string $desc, string $instr, float $cost, array $items, array $quants): int {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                INSERT INTO recipes (user_id, category_id, title, description, instructions, estimated_cost)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $catId, $title, $desc, $instr, $cost]);
            $recipeId = (int)$this->pdo->lastInsertId();

            $ingStmt = $this->pdo->prepare("INSERT INTO ingredients (recipe_id, item_name, quantity) VALUES (?, ?, ?)");
            for ($i = 0; $i < count($items); $i++) {
                $item = trim($items[$i] ?? '');
                $qty = trim($quants[$i] ?? '');
                if ($item !== '') {
                    $ingStmt->execute([$recipeId, $item, $qty]);
                }
            }

            $this->pdo->commit();
            return $recipeId;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function update(int $recipeId, int $userId, int $catId, string $title, string $desc, string $instr, float $cost, array $items, array $quants): bool {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                UPDATE recipes 
                SET category_id = ?, title = ?, description = ?, instructions = ?, estimated_cost = ?, is_edited = 1
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$catId, $title, $desc, $instr, $cost, $recipeId, $userId]);

            if ($stmt->rowCount() === 0) {
                // Verify user owns recipe
                $check = $this->pdo->prepare("SELECT id FROM recipes WHERE id = ? AND user_id = ?");
                $check->execute([$recipeId, $userId]);
                if (!$check->fetch()) {
                    $this->pdo->rollBack();
                    return false;
                }
            }

            // Sync ingredients
            $del = $this->pdo->prepare("DELETE FROM ingredients WHERE recipe_id = ?");
            $del->execute([$recipeId]);

            $ingStmt = $this->pdo->prepare("INSERT INTO ingredients (recipe_id, item_name, quantity) VALUES (?, ?, ?)");
            for ($i = 0; $i < count($items); $i++) {
                $item = trim($items[$i] ?? '');
                $qty = trim($quants[$i] ?? '');
                if ($item !== '') {
                    $ingStmt->execute([$recipeId, $item, $qty]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function delete(int $recipeId, int $userId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM recipes WHERE id = ? AND user_id = ?");
        return $stmt->execute([$recipeId, $userId]);
    }
}