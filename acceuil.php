<?php
session_start();

if (!isset($_SESSION['username']) || !isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    // si non connecté, rediriger vers la page de connexion
    header('Location: connexion.php');
    exit();
}

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eFootball Store - Comptes Premium</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="js_acceuil.js"></script>
    <link href="Style_acceuil.css" rel="stylesheet" type="text/css">

    
</head>
<body> 
      
      





    
<nav style="padding: 20px 50px; display: flex; justify-content: space-between; align-items: center; background: rgba(15, 12, 41, 0.8); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.05);">
    <a href="acceuil.php" style="display: flex; flex-direction: column; align-items: center; text-decoration: none;">
        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ9SBs4aa8Qgupeysy-THcIR8-bRBQHiw1ITQ&s" alt="Best Dribbleur Store" style="height:60px; border-radius:6px;object-fit:cover">
        <span style="font-size: 23px; font-weight: bold; color: linear-gradient(45deg, #00d4ff, #0099ff); margin-top:5px;    background: linear-gradient(45deg, #00d4ff, #0099ff, #00ff88);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: glow 2s 
ease-in-out infinite alternate;">Dribbleur Store</span>
    </a>
   
    <div class="nav-actions" style="display:flex;gap:10px;align-items:center">
        <a href="list_articles.php" class="cart-btn" style="text-decoration: none;">Comptes</a>

        <button class="cart-btn" onclick="typeof openCart === 'function' && openCart()" title="Voir le panier">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 6h15l-1.5 9h-12L4 2H2" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span style="margin-left:6px">Panier</span>
            <span class="cart-count" id="cartCount">0</span>
        </button>
    
        <a href="order_history.php" class="cart-btn" title="Voir l'historique des commandes" style="text-decoration: none;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 8v4l3 3m6-3c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2s10 4.477 10 10z" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span style="margin-left:6px">Historique</span></a>

        <a class="profile-btn" href="profile.php" title="Mon profil" style="display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:25px;background:rgba(255,255,255,0.04);color:#fff;text-decoration:none">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0"><circle cx="12" cy="8" r="3.2" stroke="#fff" stroke-width="1.4"/><path d="M4 20c0-3.3 2.7-6 6-6h4c3.3 0 6 2.7 6 6" stroke="#fff" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>Profil</span>
        </a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a class="profile-btn" href="admin.php" title="Panneau d'administration" style="background: #ff0000ff; color: black; text-decoration: none;">Admin</a>
        <?php endif; ?>
    </div>
</nav>

    <section class="hero" id="home">
        <canvas id="canvas3d"></canvas>
        <div class="hero-content">
            <h1>Comptes eFootball Premium</h1>
            <p>Les meilleurs comptes avec joueurs légendaires</p>
            
            <div class="hero-badges">
                <div class="badge">✓ Livraison Immédiate</div>
                <div class="badge">✓ 100% Sécurisé</div>
            <div class="hero-badges">
                <div class="badge">✓ Livraison Instantanée</div>
                <div class="badge">✓ 100% Sécurisé</div>
                <div class="badge">✓ Garantie 30 Jours</div>
                <div class="badge">✓ Support 24/7</div>
            </div>
            <button class="cta-button"  id ="comptes"  onclick="document.getElementById('articles').scrollIntoView({behavior: 'smooth'})">
                Voir les Comptes
            </button>
        </div>
    </section>

    

<!-- Articles section -->
    <?php
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=efootball;charset=utf8mb4','root','');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->query('SELECT title, slug, content, image, created_at FROM articles ORDER BY created_at DESC LIMIT 6');
        $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $recent = [];
    }
    ?>

     <section class="features-section" id="articles" style="background:linear-gradient(180deg,#071426,#0f1724); padding:60px 50px;">
        <h2 class="section-title">Nos Articles </h2>
        <div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:24px">
            <?php if (empty($recent)): ?>
                <p style="color:#ccc;text-align:center;grid-column:1/-1">Aucun article pour le moment.</p>
            <?php else: ?>
                <?php foreach($recent as $art): ?>
                    <div class="article-preview-card">
                        <?php if (!empty($art['image'])): ?>
                            <img src="uploads/articles/<?php echo htmlspecialchars($art['image']); ?>" alt="" style=" width: 100%; height: 150px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 12px;
    display: block;">
    
                        <?php endif; ?>
                        <h3 style="color:#00d4ff;margin:0 0 8px"><?php echo htmlspecialchars($art['title']); ?></h3>
                        <p style="color:#d0d6db"><?php echo htmlspecialchars(mb_strimwidth(strip_tags($art['content']), 0, 140, '...')); ?></p>
                        <div style="margin-top:10px"><a href="/efootball/list_articles.php" style="color:#00ff88">Voir tous les articles</a></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="features-section" id="garanties">
        <h2 class="section-title">Pourquoi Nous Choisir ?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>Livraison Immédiate</h3>
                <p>Recevez votre compte en moins de 5 minutes après l'achat</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>100% Sécurisé</h3>
                <p>Tous nos comptes sont vérifiés et sécurisés</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💎</div>
                <h3>Qualité Premium</h3>
                <p>Comptes avec les meilleurs joueurs et coins</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🛡️</div>
                <h3>Garantie 30 Jours</h3>
                <p>Remboursement complet si problème</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💬</div>
                <h3>Support 24/7</h3>
                <p>Notre équipe disponible à tout moment</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💳</div>
                <h3>Paiement Sécurisé</h3>
                <p>Plusieurs méthodes de paiement acceptées</p>
            </div>
        </div>
    </section>

    

    <section class="testimonials" id="avis">
        <h2 class="section-title">Avis Clients</h2>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="stars">★★★★★</div>
                <p class="testimonial-text">"Excellent service ! J'ai reçu mon compte en 3 minutes avec tous les joueurs promis. Incroyable !"</p>
                <div class="testimonial-author">
                    <div class="author-avatar">M</div>
                    <div>
                        <div><strong>Mohamed</strong></div>
                        <div style="font-size: 12px; opacity: 0.7;">Il y a 2 jours</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="stars">★★★★★</div>
                <p class="testimonial-text">"Meilleur site pour acheter des comptes eFootball. Prix corrects et service rapide. Je recommande !"</p>
                <div class="testimonial-author">
                    <div class="author-avatar">A</div>
                    <div>
                        <div><strong>Ahmed</strong></div>
                        <div style="font-size: 12px; opacity: 0.7;">Il y a 1 semaine</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="stars">★★★★★</div>
                <p class="testimonial-text">"Super fiable, j'ai acheté 3 comptes et tous fonctionnent parfaitement. Support très réactif !"</p>
                <div class="testimonial-author">
                    <div class="author-avatar">Y</div>
                    <div>
                        <div><strong>Youssef</strong></div>
                        <div style="font-size: 12px; opacity: 0.7;">Il y a 3 jours</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>eFootball Store</h3>
                <p style="opacity: 0.8;">La meilleure boutique pour acheter des comptes eFootball premium avec garantie et support.</p>
            </div>
            <div class="footer-section">
                <h3>Liens Rapides</h3>
                <ul>
                    <li><a href="#comptes">Comptes</a></li>
                    <li><a href="#garanties">Garanties</a></li>
                    <li><a href="#avis">Avis Clients</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Support</h3>
                <ul>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Guide d'achat</a></li>
                    <li><a href="#">Conditions</a></li>
                    <li><a href="#">Politique de remboursement</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <ul>
                    <li>📧 diagneibeu10@gmail.com</li>
                    <li>💬 Discord: BEST DRIBBLEUR SN </li>
                    <li>📱 WhatsApp: +221 77 507 29 36</li>
                </ul>
            </div>
        </div>
        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
            <p>&copy; 2025 eFootball Store. Tous droits réservés.</p>
        </div>
    </footer>

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

   