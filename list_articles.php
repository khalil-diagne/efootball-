<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
try {
    $pdo = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4','root','');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query('SELECT id, title, slug, price, image, author_username, created_at FROM articles ORDER BY created_at DESC LIMIT 50');
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
    <link rel="stylesheet" href="styles.css"> <!-- Assurez-vous que ce fichier existe et est correct -->
    <style>
        /* Styles pour le panier (copiés depuis acceuil.php pour la cohérence) */
        .cart-btn {
            background: linear-gradient(45deg, #00d4ff, #0099ff);
            padding: 10px 25px;
            border-radius: 25px;
            border: none;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .cart-count {
            background: #ff0055;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: bold;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            padding: 40px;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            position: relative;
            border: 1px solid rgba(0, 212, 255, 0.3);
            color: #fff;
        }
        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 30px;
            cursor: pointer;
            color: #fff;
        }
        .modal h2 {
            color: #00d4ff;
            margin-bottom: 20px;
        }
        .cta-button {
            padding: 18px 45px;
            font-size: 18px;
            background: linear-gradient(45deg, #00d4ff, #0099ff);
            border: none;
            border-radius: 50px;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 40px rgba(0, 212, 255, 0.4);
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav style="padding: 20px 50px; display: flex; justify-content: space-between; align-items: center; background: rgba(15, 12, 41, 0.8); backdrop-filter: blur(10px);">
    <a href="acceuil.php" style="font-size: 28px; font-weight: bold; color: #fff; text-decoration: none;">eFootball Store</a>
        <div>



            <button class="cart-btn" onclick="openCart()" title="Voir le panier">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 6h15l-1.5 9h-12L4 2H2" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span style="margin-left:6px">Panier</span>
                <span class="cart-count" id="cartCount">0</span>
            </button>
        </div>
    </nav>

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
                        <div class="article-meta">Publié par BEST DRIBBLEUR SN  </div>
                        <div class="article-meta" style="font-weight: bold; color: #00ff88; font-size: 1.2em; margin-top: 8px;"><?php echo htmlspecialchars(number_format($r['price'], 0, ',', ' ')); ?> FCFA</div>
                        <div style="margin-top:8px">
                            <button class="btn btn-primary" onclick='addArticleToCart(<?php echo json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Acheter</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal du panier (copié depuis acceuil.php) -->
    <div class="modal" id="cartModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeCart()">&times;</span>
            <h2>🛒 Votre Panier</h2>
            <div id="cartItems"></div>
            <button class="cta-button" style="width: 100%; margin-top: 20px;" onclick="checkout()">
                Procéder au paiement
            </button>
        </div>
    </div>

    <script>
        let cart = JSON.parse(localStorage.getItem('efootball_cart')) || [];

        function addArticleToCart(article) {
            // On s'assure que l'article a les bonnes propriétés (id, title, price)
            const item = {
                id: article.id,
                title: article.title,
                price: parseFloat(article.price)
            };
            cart.push(item);
            updateCart();
            showNotification('✓ Article ajouté au panier !');
        }

        function updateCart() {
            localStorage.setItem('efootball_cart', JSON.stringify(cart));
            const el = document.getElementById('cartCount');
            if (el) el.textContent = cart.length;
        }

        function showNotification(message) {
            const n = document.createElement('div');
            n.textContent = message;
            n.style.position = 'fixed';
            n.style.right = '20px';
            n.style.bottom = '20px';
            n.style.background = 'linear-gradient(45deg,#00d4ff,#00ff88)';
            n.style.color = '#042';
            n.style.padding = '10px 14px';
            n.style.borderRadius = '8px';
            n.style.boxShadow = '0 6px 20px rgba(0,0,0,0.2)';
            n.style.zIndex = 3000;
            document.body.appendChild(n);
            setTimeout(() => { n.style.opacity = '0'; n.style.transition = 'opacity 400ms'; }, 1400);
            setTimeout(() => n.remove(), 2000);
        }

        function openCart() {
            const modal = document.getElementById('cartModal');
            const cartItems = document.getElementById('cartItems');
            if (!modal || !cartItems) return;

            if (cart.length === 0) {
                cartItems.innerHTML = '<p style="text-align: center; padding: 40px; opacity: 0.7;">Votre panier est vide</p>';
            } else {
                cartItems.innerHTML = cart.map((it, idx) => `
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.03)">
                        <div>
                            <div style="font-weight:600">${it.title}</div>
                        </div>
                        <div style="text-align:right">
                            <div style="font-weight:700">${it.price} FCFA</div>
                            <button onclick="removeFromCart(${idx})" style="margin-top:6px;padding:6px 8px;border-radius:6px;background:#ff4d6d;color:#fff;border:none">Supprimer</button>
                        </div>
                    </div>
                `).join('');
            }
            modal.style.display = 'flex';
        }

        function closeCart() {
            const modal = document.getElementById('cartModal');
            if (modal) modal.style.display = 'none';
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCart();
            openCart(); // Rafraîchir la vue du panier
        }

        function checkout() {
            if (cart.length === 0) { alert('Votre panier est vide.'); return; }
            fetch('checkout.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(cart) })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Commande réussie ! Votre numéro de commande est : ' + data.order_id);
                    cart = [];
                    updateCart();
                    closeCart();
                } else { alert('Erreur: ' + data.message); }
            }).catch(error => { console.error('Erreur:', error); alert('Une erreur technique est survenue.'); });
        }

        // Initialiser le compteur au chargement de la page
        document.addEventListener('DOMContentLoaded', updateCart);
    </script>
</body>
</html>
