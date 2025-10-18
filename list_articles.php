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
<html lang="fr"><head><meta charset="utf-8"><title>Articles</title></head><body style="font-family:Arial;padding:20px">
    <h2>Articles</h2>
    <?php if (isset($_GET['created'])): ?><p style="color:green">Article créé.</p><?php endif; ?>
    <?php if (empty($rows)): ?>
        <p>Aucun article publié.</p>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:18px">
        <?php foreach($rows as $r): ?>
            <div style="border:1px solid #eee;padding:12px;border-radius:8px;background:#fff">
                <?php if ($r['image']): ?>
                    <img src="uploads/articles/<?php echo htmlspecialchars($r['image']); ?>" alt="" style="max-width:100%;height:150px;object-fit:cover;border-radius:6px"><br>
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($r['title']); ?></h3>
                <small>Par <?php echo htmlspecialchars($r['author_username']); ?> — <?php echo htmlspecialchars($r['created_at']); ?></small>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body></html>
