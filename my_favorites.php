<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_check.php';
require_auth();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/FavoriteService.php';

$favService = new FavoriteService($pdo);
$favorites = $favService->getUserFavorites(current_user_id());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Saved Recipes - TipidKusina</title>
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
        <h2>❤️ My Saved Recipes</h2>
        <?php if (empty($favorites)): ?>
            <div class="empty-state">
                <p>You haven't bookmarked any recipes yet.</p>
                <a href="index.php" class="btn btn-primary" style="margin-top: 1rem;">Browse Recipes</a>
            </div>
        <?php else: ?>
            <div class="recipe-grid">
                <?php foreach ($favorites as $r): ?>
                    <article class="recipe-card">
                        <div class="recipe-card-header">
                            <span class="badge category-badge"><?= htmlspecialchars($r['category_name']) ?></span>
                            <span class="badge budget-badge">₱<?= number_format((float)$r['estimated_cost'], 2) ?></span>
                            <button class="fav-btn active" data-id="<?= $r['id'] ?>" title="Remove Bookmark">❤️</button>
                        </div>
                        <h3><a href="recipe_detail.php?id=<?= $r['id'] ?>"><?= htmlspecialchars($r['title']) ?></a></h3>
                        <p class="recipe-desc"><?= htmlspecialchars(mb_strimwidth($r['description'], 0, 110, '...')) ?></p>
                        <a href="recipe_detail.php?id=<?= $r['id'] ?>" class="view-link">Open Recipe →</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="app.js"></script>
</body>
</html>