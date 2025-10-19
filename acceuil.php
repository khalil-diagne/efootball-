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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: #fff;
            overflow-x: hidden;
        }

        nav {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            background: rgba(15, 12, 41, 0.8);
            backdrop-filter: blur(10px);
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            background: linear-gradient(45deg, #00d4ff, #0099ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            transition: all 0.3s;
        }

        .nav-links a:hover {
            color: #00d4ff;
        }

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

        .cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 212, 255, 0.4);
        }

        .nav-actions { display:flex; gap:10px; align-items:center }
        .profile-btn { background: linear-gradient(45deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02)); padding:8px 12px; border-radius:25px; color:#fff; text-decoration:none }
        .profile-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.25) }

        .cart-count {
            background: #ff0055;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: bold;
        }

        .hero {
            height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        #canvas3d {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .hero-content {
            position: relative;
            z-index: 10;
            max-width: 900px;
            padding: 20px;
        }

        .hero h1 {
            font-size: 72px;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #00d4ff, #0099ff, #00ff88);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { filter: drop-shadow(0 0 20px rgba(0, 212, 255, 0.5)); }
            to { filter: drop-shadow(0 0 40px rgba(0, 153, 255, 0.8)); }
        }

        .hero p {
            font-size: 24px;
            margin-bottom: 40px;
            opacity: 0.9;
        }

        .hero-badges {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .badge {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
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

        .cta-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0, 212, 255, 0.6);
        }

        .section-title {
            text-align: center;
            font-size: 48px;
            margin-bottom: 60px;
            background: linear-gradient(45deg, #00d4ff, #00ff88);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .accounts-section {
            padding: 100px 50px;
        }

        .filters {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 12px 30px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-btn:hover, .filter-btn.active {
            background: linear-gradient(45deg, #00d4ff, #0099ff);
            border-color: #00d4ff;
            transform: translateY(-3px);
        }

        .accounts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .account-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s;
            cursor: pointer;
            position: relative;
        }

        .account-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 212, 255, 0.3);
            border-color: #00d4ff;
        }

        .card-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(45deg, #ff0055, #ff3366);
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            z-index: 10;
        }

        .card-image {
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 64px;
            position: relative;
            overflow: hidden;
        }

        .card-image::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .card-content {
            padding: 25px;
        }

        .card-title {
            font-size: 22px;
            margin-bottom: 15px;
            color: #00d4ff;
            font-weight: bold;
        }

        .card-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }

        .stat {
            background: rgba(255, 255, 255, 0.05);
            padding: 10px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #00ff88;
        }

        .stat-label {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 5px;
        }

        .card-features {
            margin-bottom: 20px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .feature-item::before {
            content: '✓';
            color: #00ff88;
            font-weight: bold;
        }

        .card-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .price {
            font-size: 32px;
            font-weight: bold;
            background: linear-gradient(45deg, #00d4ff, #00ff88);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .old-price {
            text-decoration: line-through;
            opacity: 0.5;
            font-size: 18px;
        }

        .buy-btn {
            padding: 12px 30px;
            background: linear-gradient(45deg, #00d4ff, #0099ff);
            border: none;
            border-radius: 25px;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
        }

        .buy-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(0, 212, 255, 0.5);
        }

        .features-section {
            padding: 100px 50px;
                background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.1);
        }

        .feature-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .testimonials {
            padding: 100px 50px;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .testimonial-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stars {
            color: #ffd700;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .testimonial-text {
            font-style: italic;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(45deg, #00d4ff, #0099ff);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        footer {
            padding: 60px 50px;
            background: rgba(15, 12, 41, 0.95);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        .footer-section h3 {
            color: #00d4ff;
            margin-bottom: 20px;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 10px;
            opacity: 0.8;
        }

        .footer-section ul li a {
            color: #fff;
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer-section ul li a:hover {
            color: #00d4ff;
            padding-left: 5px;
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

        @media (max-width: 768px) {
            .hero h1 { font-size: 42px; }
            .nav-links { display: none; }
            .accounts-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo">
            <!-- logo image (fallback to text if not loaded) -->
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ9SBs4aa8Qgupeysy-THcIR8-bRBQHiw1ITQ&s" alt="Best Dribbleur Store" style="height:40px;border-radius:6px;object-fit:cover">
            <span class="sr-only">Dribbleur Store</span>
        </div>
        <div class="nav-links">
            <a href="#home">Accueil</a>
            <a href="#comptes">Comptes</a>
            <a href="#garanties">Garanties</a>
            <a href="#avis">Avis</a>
        </div>
        <div class="nav-actions" style="display:flex;gap:10px;align-items:center">
            <button class="cart-btn" onclick="openCart()" title="Voir le panier">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 6h15l-1.5 9h-12L4 2H2" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span style="margin-left:6px">Panier</span>
                <span class="cart-count" id="cartCount">0</span>
            </button>

            <a class="profile-btn" href="profile.php" title="Mon profil" style="display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:25px;background:rgba(255,255,255,0.04);color:#fff;text-decoration:none">
                <!-- profile icon SVG -->
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0"><circle cx="12" cy="8" r="3.2" stroke="#fff" stroke-width="1.4"/><path d="M4 20c0-3.3 2.7-6 6-6h4c3.3 0 6 2.7 6 6" stroke="#fff" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Profil</span>
            </a>
        </div>
    </nav>

    <section class="hero" id="home">
        <canvas id="canvas3d"></canvas>
        <div class="hero-content">
            <h1>Comptes eFootball Premium</h1>
            <p>Les meilleurs comptes avec joueurs légendaires et coins illimités</p>
            
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
                    <div style=" background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);padding:16px;border-radius:12px;border:1px solid rgba(255,255,255,0.06)">
                        <?php if (!empty($art['image'])): ?>
                            <img src="uploads/articles/<?php echo htmlspecialchars($art['image']); ?>" alt="" style="width:100%;height:160px;object-fit:cover;border-radius:8px;margin-bottom:12px">
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

    <script>
        // Données des comptes
        const accounts = [
            {
                id: 1,
                type: 'starter',
                title: 'Compte Starter',
                icon: '⚽',
                rating: 2800,
                coins: '50K',
                players: 15,
                legendaries: 2,
                price: 4999,
                oldPrice: 7999,
                features: ['15 Joueurs Premium', '2 Légendaires', '50K Coins', 'Niveau 25+']
            },
            {
                id: 2,
                type: 'premium',
                title: 'Compte Premium',
                icon: '🏆',
                rating: 3500,
                coins: '150K',
                players: 35,
                legendaries: 8,
                price: 12999,
                oldPrice: 19999,
                features: ['35 Joueurs Premium', '8 Légendaires', '150K Coins', 'Niveau 50+']
            },
            {
                id: 3,
                type: 'elite',
                title: 'Compte Elite',
                icon: '👑',
                rating: 4200,
                coins: '500K',
                players: 60,
                legendaries: 20,
                price: 29999,
                oldPrice: 49999,
                features: ['60 Joueurs Premium', '20 Légendaires', '500K Coins', 'Niveau 100+']
            },
            {
                id: 4,
                type: 'starter',
                title: 'Compte Débutant Pro',
                icon: '⚽',
                rating: 3000,
                coins: '75K',
                players: 20,
                legendaries: 3,
                price: 6999,
                oldPrice: 9999,
                features: ['20 Joueurs Premium', '3 Légendaires', '75K Coins', 'Niveau 30+']
            },
            {
                id: 5,
                type: 'premium',
                title: 'Compte VIP',
                icon: '💎',
                rating: 3800,
                coins: '250K',
                players: 45,
                legendaries: 12,
                price: 17999,
                oldPrice: 27999,
                features: ['45 Joueurs Premium', '12 Légendaires', '250K Coins', 'Niveau 70+']
            },
            {
                id: 6,
                type: 'elite',
                title: 'Compte Ultimate',
                icon: '🌟',
                rating: 4500,
                coins: '1M',
                players: 80,
                legendaries: 30,
                price: 49999,
                oldPrice: 79999,
                features: ['80 Joueurs Premium', '30 Légendaires', '1M Coins', 'Niveau 150+']
            }
        ];

        let cart = [];
        let currentFilter = 'all';

        function renderAccounts(filter = 'all') {
            const grid = document.getElementById('accountsGrid');
            const filtered = filter === 'all' ? accounts : accounts.filter(acc => acc.type === filter);
            
            grid.innerHTML = filtered.map(account => `
                <div class="account-card">
                    <div class="card-badge">${account.type.toUpperCase()}</div>
                    <div class="card-image">${account.icon}</div>
                    <div class="card-content">
                        <h3 class="card-title">${account.title}</h3>
                        <div class="card-stats">
                            <div class="stat">
                                <div class="stat-value">${account.rating}</div>
                                <div class="stat-label">Rating</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value">${account.coins}</div>
                                <div class="stat-label">Coins</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value">${account.players}</div>
                                <div class="stat-label">Joueurs</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value">${account.legendaries}</div>
                                <div class="stat-label">Légendaires</div>
                            </div>
                        </div>
                        <div class="card-features">
                            ${account.features.map(f => `<div class="feature-item">${f}</div>`).join('')}
                        </div>
                        <div class="card-price">
                            <div>
                                <div class="old-price">${account.oldPrice} FCFA</div>
                                <div class="price">${account.price} FCFA</div>
                            </div>
                            <button class="buy-btn" onclick="addToCart(${account.id})">Acheter</button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function filterAccounts(type, btn) {
            currentFilter = type;
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            if (btn && btn.classList) btn.classList.add('active');
            renderAccounts(type);
        }

        function addToCart(id) {
            const account = accounts.find(a => a.id === id);
            if (!account) return;
            cart.push(account);
            updateCartCount();
            showNotification('✓ Ajouté au panier !');
        }

        function updateCartCount() {
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
                            <div style="font-size:12px;opacity:0.8">${it.coins} • ${it.players} joueurs</div>
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

        function checkout() {
             if (cart.length === 0) {
                 alert('Votre panier est vide.');
                 return;
             }
 
             // Envoyer les données du panier au serveur
             fetch('checkout.php', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                 },
                 body: JSON.stringify(cart)
             })
             .then(response => response.json())
             .then(data => {
                 if (data.success) {
                     alert('Commande réussie ! Votre numéro de commande est : ' + data.order_id);
                     cart = []; // Vider le panier
                     updateCartCount();
                     closeCart();
                 } else {
                     alert('Erreur: ' + data.message);
                 }
             })
             .catch(error => {
                 console.error('Erreur lors du checkout:', error);
                 alert('Une erreur technique est survenue.');
             });
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartCount();
            openCart();
        }

        // initialisation
        renderAccounts();
        updateCartCount();
