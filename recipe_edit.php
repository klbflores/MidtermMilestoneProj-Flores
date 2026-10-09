<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_check.php';
require_auth();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/Validator.php';
require_once __DIR__ . '/classes/RecipeService.php';

$recipeId = (int)($_GET['id'] ?? 0);
$recipeService = new RecipeService($pdo);
$recipe = $recipeService->getById($recipeId);

if (!$recipe || (int)$recipe['user_id'] !== current_user_id()) {
    die("Access denied. You can only edit your own recipes. <a href='index.php'>Return to feed</a>");
}

$categories = $recipeService->getCategories();
$ingredients = $recipeService->getIngredients($recipeId);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = Validator::sanitize($_POST['title'] ?? '');
    $catId = (int)($_POST['category_id'] ?? 0);
    $desc = Validator::sanitize($_POST['description'] ?? '');
    $instr = Validator::sanitize($_POST['instructions'] ?? '');
    $cost = (float)($_POST['estimated_cost'] ?? 0);
    $items = $_POST['ingredient_name'] ?? [];
    $quants = $_POST['ingredient_qty'] ?? [];

    if (!Validator::validateString($title, 3, 150)) $errors[] = "Title must be between 3 and 150 characters.";
    if ($catId <= 0) $errors[] = "Select a valid category.";
    if (!Validator::validateString($desc, 5)) $errors[] = "Short description is required.";
    if (!Validator::validateString($instr, 5)) $errors[] = "Instructions are required.";
    if (!Validator::validateCost($cost)) $errors[] = "Estimated budget must be a positive number.";
    if (empty(array_filter($items))) $errors[] = "At least one ingredient is required.";

    if (empty($errors)) {
        $recipeService->update($recipeId, current_user_id(), $catId, $title, $desc, $instr, $cost, $items, $quants);
        header("Location: recipe_detail.php?id=" . $recipeId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe - TipidKusina</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="brand">🍳 TipidKusina</a>
            <nav class="nav-links">
                <a href="recipe_detail.php?id=<?= $recipe['id'] ?>" class="btn btn-sm btn-outline">Cancel</a>
            </nav>
        </div>
    </header>

    <main class="container form-page">
        <div class="card form-card">
            <h2>Edit Recipe: <?= htmlspecialchars($recipe['title']) ?></h2>
            <form method="POST" action="recipe_edit.php?id=<?= $recipeId ?>">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? $recipe['title']) ?>">
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ((int)$cat['id'] === (int)($recipe['category_id'])) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="estimated_cost">Budget (₱)</label>
                        <input type="number" step="0.50" id="estimated_cost" name="estimated_cost" required value="<?= htmlspecialchars((string)($_POST['estimated_cost'] ?? $recipe['estimated_cost'])) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Short Description</label>
                    <textarea id="description" name="description" rows="2" required><?= htmlspecialchars($_POST['description'] ?? $recipe['description']) ?></textarea>
                </div>

                <fieldset class="ingredients-fieldset">
                    <legend>Ingredients</legend>
                    <div id="ingredient-rows">
                        <?php foreach ($ingredients as $ing): ?>
                            <div class="ingredient-row">
                                <input type="text" name="ingredient_name[]" value="<?= htmlspecialchars($ing['item_name']) ?>" required>
                                <input type="text" name="ingredient_qty[]" value="<?= htmlspecialchars($ing['quantity']) ?>" required>
                                <button type="button" class="btn btn-danger btn-remove-row">✕</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="btn-add-ingredient" class="btn btn-sm btn-secondary">+ Add More Ingredient</button>
                </fieldset>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label for="instructions">Cooking Instructions</label>
                    <textarea id="instructions" name="instructions" rows="5" required><?= htmlspecialchars($_POST['instructions'] ?? $recipe['instructions']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Save Changes (Mark as Edited)</button>
            </form>
        </div>
    </main>

    <script src="app.js"></script>
</body>
</html>