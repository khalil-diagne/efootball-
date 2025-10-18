<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
try {
    $pdo = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4','root','');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query('SELECT id, title, slug, image, author_username, created_at FROM articles ORDER BY created_at DESC LIMIT 50');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur BDD: '.$e->getMessage());
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Articles</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2 class="page-title">Articles</h2>
        <?php if (isset($_GET['created'])): ?>
            <p class="muted" style="color:#7ee4a8">Article créé.</p>
        <?php endif; ?>

        <?php if (empty($rows)): ?>
            <p class="muted">Aucun article publié.</p>
        <?php else: ?>
            <div class="articles-grid">
                <?php foreach($rows as $r): ?>
                    <article class="article-card">
                        <?php if ($r['image']): ?>
                            <img class="article-image" src="uploads/articles/<?php echo htmlspecialchars($r['image']); ?>" alt="">
                        <?php endif; ?>
                        <h3 class="article-title"><?php echo htmlspecialchars($r['title']); ?></h3>
                        <div class="article-meta">Par <?php echo htmlspecialchars($r['author_username']); ?> — <?php echo htmlspecialchars($r['created_at']); ?></div>
                        <div style="margin-top:8px"><a class="btn btn-outline" href="/efootball/list_articles.php?slug=<?php echo urlencode($r['slug']); ?>">Acheter</a></div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
