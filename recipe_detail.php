<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_check.php';
require_auth();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/RecipeService.php';
require_once __DIR__ . '/classes/FavoriteService.php';

$recipeId = (int)($_GET['id'] ?? 0);
$recipeService = new RecipeService($pdo);
$favService = new FavoriteService($pdo);

$recipe = $recipeService->getById($recipeId);
if (!$recipe) {
    die("Recipe not found. <a href='index.php'>Return to feed</a>");
}

$ingredients = $recipeService->getIngredients($recipeId);
$isFav = $favService->isFavorited(current_user_id(), $recipeId);

// Fetch Comments with authors
$cmtStmt = $pdo->prepare("
    SELECT c.*, u.name AS author_name 
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.recipe_id = ?
    ORDER BY c.created_at ASC
");
$cmtStmt->execute([$recipeId]);
$comments = $cmtStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($recipe['title']) ?> - TipidKusina</title>
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

    <main class="container">
        <article class="card detail-card">
            <div class="detail-header">
                <div>
                    <span class="badge category-badge"><?= htmlspecialchars($recipe['category_name']) ?></span>
                    <span class="badge budget-badge">Budget: ₱<?= number_format((float)$recipe['estimated_cost'], 2) ?></span>
                    <?php if ($recipe['is_edited']): ?>
                        <span class="edited-tag">(edited)</span>
                    <?php endif; ?>
                    <h1><?= htmlspecialchars($recipe['title']) ?></h1>
                    <p class="recipe-author">Shared by <strong><?= htmlspecialchars($recipe['author_name']) ?></strong> on <?= date('M d, Y', strtotime($recipe['created_at'])) ?></p>
                </div>
                <div>
                    <button class="fav-btn btn-large <?= $isFav ? 'active' : '' ?>" data-id="<?= $recipe['id'] ?>">
                        <?= $isFav ? '❤️ Saved' : '🤍 Save to Favorites' ?>
                    </button>
                </div>
            </div>

            <p class="recipe-lead"><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>

            <!-- Actions for Recipe Author -->
            <?php if ((int)$recipe['user_id'] === current_user_id()): ?>
                <div class="owner-actions">
                    <a href="recipe_edit.php?id=<?= $recipe['id'] ?>" class="btn btn-sm btn-secondary">Edit My Recipe</a>
                    <form method="POST" action="recipe_delete.php" onsubmit="return confirm('Are you sure you want to delete this recipe?');" style="display:inline;">
                        <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete Recipe</button>
                    </form>
                </div>
            <?php endif; ?>

            <hr>

            <div class="recipe-body-grid">
                <div>
                    <h3>🛒 Ingredients</h3>
                    <ul class="ingredient-list">
                        <?php foreach ($ingredients as $ing): ?>
                            <li><strong><?= htmlspecialchars($ing['quantity']) ?></strong> - <?= htmlspecialchars($ing['item_name']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h3>👩‍🍳 Cooking Instructions</h3>
                    <div class="instruction-box">
                        <?= nl2br(htmlspecialchars($recipe['instructions'])) ?>
                    </div>
                </div>
            </div>
        </article>

        <!-- Comments & Feedback Section -->
        <section class="card comments-card">
            <h3>Community Feedback & Cooking Tips (<?= count($comments) ?>)</h3>

            <?php if (empty($comments)): ?>
                <p class="hint">No comments yet. Have you tried this recipe? Leave your tip below!</p>
            <?php else: ?>
                <div class="comment-list">
                    <?php foreach ($comments as $c): ?>
                        <div class="comment-bubble" id="comment-<?= $c['id'] ?>">
                            <div class="comment-meta">
                                <strong><?= htmlspecialchars($c['author_name']) ?></strong>
                                <small><?= date('M d, Y h:i A', strtotime($c['created_at'])) ?></small>
                                <?php if ($c['is_edited']): ?>
                                    <span class="edited-tag">(edited)</span>
                                <?php endif; ?>
                            </div>
                            <p class="comment-text" id="comment-text-<?= $c['id'] ?>"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
                            
                            <!-- Comment Author Ownership Controls -->
                            <?php if ((int)$c['user_id'] === current_user_id()): ?>
                                <div class="comment-controls">
                                    <button type="button" class="btn-text btn-edit-comment" data-id="<?= $c['id'] ?>">Edit</button>
                                    <form method="POST" action="comment_actions.php" onsubmit="return confirm('Delete this comment?');" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                                        <button type="submit" class="btn-text text-danger">Delete</button>
                                    </form>
                                </div>
                                <!-- Inline Edit Form (Hidden by default) -->
                                <form method="POST" action="comment_actions.php" class="comment-edit-form" id="edit-form-<?= $c['id'] ?>" style="display:none; margin-top: 0.5rem;">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                                    <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                                    <textarea name="comment" rows="2" required><?= htmlspecialchars($c['comment']) ?></textarea>
                                    <button type="submit" class="btn btn-sm btn-secondary">Save</button>
                                    <button type="button" class="btn btn-sm btn-outline btn-cancel-edit" data-id="<?= $c['id'] ?>">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Post New Comment Form -->
            <form method="POST" action="comment_actions.php" class="add-comment-form">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                <div class="form-group">
                    <label for="new-comment">Add Feedback or Student Tip</label>
                    <textarea id="new-comment" name="comment" rows="3" required placeholder="What tweak did you make to save money?"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Post Feedback</button>
            </form>
        </section>
    </main>

    <script src="app.js"></script>
</body>
</html>