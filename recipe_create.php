<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_check.php';
require_auth();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/Validator.php';
require_once __DIR__ . '/classes/RecipeService.php';

$recipeService = new RecipeService($pdo);
$categories = $recipeService->getCategories();
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
    if ($catId <= 0) $errors[] = "Please select a valid recipe category.";
    if (!Validator::validateString($desc, 5)) $errors[] = "Please provide a short description.";
    if (!Validator::validateString($instr, 5)) $errors[] = "Cooking steps are required.";
    if (!Validator::validateCost($cost)) $errors[] = "Estimated budget must be a positive number.";
    if (empty(array_filter($items))) $errors[] = "At least one ingredient is required.";

    if (empty($errors)) {
        $newId = $recipeService->create(current_user_id(), $catId, $title, $desc, $instr, $cost, $items, $quants);
        header("Location: recipe_detail.php?id=" . $newId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Budget Recipe - TipidKusina</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="brand">🍳 TipidKusina</a>
            <nav class="nav-links">
                <a href="index.php" class="btn btn-sm btn-outline">← Back to Feed</a>
            </nav>
        </div>
    </header>

    <main class="container form-page">
        <div class="card form-card">
            <h2>Share a Student Budget Meal</h2>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="recipe_create.php" id="recipe-form">
                <div class="form-group">
                    <label for="title">Recipe Title</label>
                    <input type="text" id="title" name="title" required placeholder="e.g., Crispy Corned Beef Lumpia" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label for="category_id">Meal Category</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">-- Choose Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="estimated_cost">Estimated Budget (₱)</label>
                        <input type="number" step="0.50" id="estimated_cost" name="estimated_cost" required min="0" placeholder="e.g., 65.00" value="<?= htmlspecialchars((string)($_POST['estimated_cost'] ?? '50.00')) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Short Description / Pitch</label>
                    <textarea id="description" name="description" rows="2" required placeholder="Why is this dish perfect for busy, broke students?"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <fieldset class="ingredients-fieldset">
                    <legend>Ingredients List</legend>
                    <p class="hint">Specify items and their approximate cost or quantity.</p>
                    <div id="ingredient-rows">
                        <div class="ingredient-row">
                            <input type="text" name="ingredient_name[]" placeholder="Item name (e.g., Canned Tuna)" required>
                            <input type="text" name="ingredient_qty[]" placeholder="Quantity (e.g., 1 can / 155g)" required>
                            <button type="button" class="btn btn-danger btn-remove-row" style="display:none;">✕</button>
                        </div>
                    </div>
                    <button type="button" id="btn-add-ingredient" class="btn btn-sm btn-secondary">+ Add More Ingredient</button>
                </fieldset>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label for="instructions">Cooking Steps</label>
                    <textarea id="instructions" name="instructions" rows="5" required placeholder="Step 1: Sauté garlic... Step 2: Add water..."><?= htmlspecialchars($_POST['instructions'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Publish Recipe</button>
            </form>
        </div>
    </main>

    <script src="app.js"></script>
</body>
</html>