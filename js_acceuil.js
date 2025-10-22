 
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


        
