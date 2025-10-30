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
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="Style_acceuil.css"> <!-- Ajout pour récupérer les styles du panier -->
</head>
<body>
    <nav style="padding: 20px 50px; display: flex; justify-content: space-between; align-items: center; background: rgba(15, 12, 41, 0.8); backdrop-filter: blur(10px);">
    <a href="acceuil.php" style="font-size: 28px; font-weight: bold; color: #fff; text-decoration: none;"> Dribbleur Store</a>
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

        <div style="text-align: center; margin-top: 40px;">
            <a href="acceuil.php" class="cta-button" style="padding: 12px 30px; font-size: 16px; text-decoration: none;">Retour à l'accueil</a>
        </div>

    </div>

    <!-- Modal du panier (copié depuis acceuil.php) -->
    <div class="modal" id="cartModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeCart()">&times;</span>
            <h2>🛒 Votre Panier</h2>
            <div id="cartItems"></div>
            <button class="cta-button" style="width: 100%; margin-top: 20px;" onclick="checkout()">Procéder au paiement</button>
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
            if (cart.length === 0) {
                alert('Votre panier est vide.');
                return;
            }

            // Amélioration UX : désactiver le bouton et afficher un message de chargement
            const checkoutButton = document.querySelector('#cartModal .cta-button');
            const originalButtonText = checkoutButton.textContent;
            checkoutButton.disabled = true;
            checkoutButton.textContent = 'Préparation du paiement...';

            fetch('checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(cart)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.paymentUrl) {
                    // Afficher un message de confirmation avant de rediriger
                    const message = "Vous allez être redirigé vers Wave pour finaliser votre paiement.\n\nIMPORTANT : Après le paiement, veuillez nous envoyer une capture d'écran de la transaction sur WhatsApp au 77 507 29 36 pour valider votre commande.\n\nCliquez sur 'OK' pour continuer.";
                    
                    if (confirm(message)) {
                        // Si l'utilisateur clique sur OK, on le redirige
                        cart = [];
                        updateCart();
                        closeCart();
                        window.location.href = data.paymentUrl;
                    } else {
                        // Si l'utilisateur annule, on réactive le bouton
                        checkoutButton.disabled = false;
                        checkoutButton.textContent = originalButtonText;
                    }
                } else {
                    alert('Erreur lors de la création du paiement : ' + (data.message || 'Réponse invalide du serveur.'));
                    checkoutButton.disabled = false;
                    checkoutButton.textContent = originalButtonText;
                }
            }).catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur technique est survenue. Veuillez réessayer.');
                checkoutButton.disabled = false;
                checkoutButton.textContent = originalButtonText;
            });
        }

        // Initialiser le compteur au chargement de la page
        document.addEventListener('DOMContentLoaded', updateCart);
    </script>
</body>
</html>
