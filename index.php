<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_check.php';
require_auth();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/RecipeService.php';
require_once __DIR__ . '/classes/FavoriteService.php';

$recipeService = new RecipeService($pdo);
$favService = new FavoriteService($pdo);

$search = trim($_GET['q'] ?? '');
$catId = (int)($_GET['category'] ?? 0);

$categories = $recipeService->getCategories();
$recipes = $recipeService->getLatest($search, $catId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TipidKusina - Barangay Student Meals</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="brand">🍳 TipidKusina</a>
            <nav class="nav-links">
                <span>Mabuhay, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>!</span>
                <a href="recipe_create.php" class="btn btn-sm btn-primary">+ Share Recipe</a>
                <a href="my_favorites.php" class="btn btn-sm btn-secondary">❤️ My Favorites</a>
                <a href="logout.php" class="btn btn-sm btn-outline">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <!-- Search and Category Filter -->
        <section class="filter-card">
            <form method="GET" action="index.php" class="filter-form">
                <input type="text" name="q" placeholder="Search recipes (e.g., Sardinas, Tokwa)..." value="<?= htmlspecialchars($search) ?>" class="search-input">
                <select name="category" class="filter-select">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $catId === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <?php if ($search || $catId): ?>
                    <a href="index.php" class="btn btn-outline">Clear</a>
                <?php endif; ?>
            </form>
        </section>

        <!-- Recipes Grid -->
        <section>
            <h2>Latest Budget Recipes</h2>
            <?php if (empty($recipes)): ?>
                <div class="empty-state">
                    <p>No student recipes found matching your filter. Be the first to share one!</p>
                </div>
            <?php else: ?>
                <div class="recipe-grid">
                    <?php foreach ($recipes as $r): 
                        $isFav = $favService->isFavorited(current_user_id(), (int)$r['id']);
                    ?>
                        <article class="recipe-card">
                            <div class="recipe-card-header">
                                <span class="badge category-badge"><?= htmlspecialchars($r['category_name']) ?></span>
                                <span class="badge budget-badge">Est: ₱<?= number_format((float)$r['estimated_cost'], 2) ?></span>
                                <button class="fav-btn <?= $isFav ? 'active' : '' ?>" data-id="<?= $r['id'] ?>" title="Toggle Favorite">
                                    <?= $isFav ? '❤️' : '🤍' ?>
                                </button>
                            </div>
                            <h3><a href="recipe_detail.php?id=<?= $r['id'] ?>"><?= htmlspecialchars($r['title']) ?></a></h3>
                            <p class="recipe-desc"><?= htmlspecialchars(mb_strimwidth($r['description'], 0, 110, '...')) ?></p>
                            <div class="recipe-meta">
                                <span>Cook: <?= htmlspecialchars($r['author_name']) ?></span>
                                <?php if ($r['is_edited']): ?>
                                    <span class="edited-tag">(edited)</span>
                                <?php endif; ?>
                            </div>
                            <a href="recipe_detail.php?id=<?= $r['id'] ?>" class="view-link">View Recipe & Steps →</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script src="app.js"></script>
</body>
</html>