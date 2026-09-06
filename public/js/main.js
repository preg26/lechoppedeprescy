// Gallery filter and pagination functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const galleryGrid = document.getElementById('gallery-grid');
    const loadMoreBtn = document.getElementById('load-more-btn');
    let currentCategory = '';
    let currentOffset = 6;
    
    // Smooth scroll manuel avec animation pour les liens d'ancre du menu
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#' || href === '') return;
            
            e.preventDefault();
            const targetId = href.substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - 100;
                const startPosition = window.pageYOffset;
                const distance = targetPosition - startPosition;
                const duration = 500; // 500ms
                let start = null;
                
                function animation(currentTime) {
                    if (start === null) start = currentTime;
                    const timeElapsed = currentTime - start;
                    const run = ease(timeElapsed, startPosition, distance, duration);
                    window.scrollTo(0, run);
                    if (timeElapsed < duration) requestAnimationFrame(animation);
                }
                
                // Fonction d'easing pour un mouvement fluide
                function ease(t, b, c, d) {
                    t /= d / 2;
                    if (t < 1) return c / 2 * t * t + b;
                    t--;
                    return -c / 2 * (t * (t - 2) - 1) + b;
                }
                
                requestAnimationFrame(animation);
            }
        });
    });
    
    // Image carousel with zoom + fade effect and dots navigation
    function initImageCarousel() {
        const galleryItems = document.querySelectorAll('.gallery-item');
        
        galleryItems.forEach(item => {
            const cardImages = item.querySelectorAll('.card-image');
            const dots = item.querySelectorAll('.card-dot');
            
            if (cardImages.length <= 1) return;
            
            let currentIndex = 0;
            let autoSlideInterval = null;
            let isHovering = false;
            let transitionTimeouts = []; // Stocker les timeouts pour pouvoir les annuler
            
            // Fonction pour changer d'image avec effet zoom + fondu + dézoom
            function changeImage(newIndex) {
                if (newIndex === currentIndex || newIndex >= cardImages.length) return;
                
                // Annuler tous les timeouts de la transition précédente
                transitionTimeouts.forEach(timeout => clearTimeout(timeout));
                transitionTimeouts = [];
                
                const currentImage = cardImages[currentIndex];
                const nextImage = cardImages[newIndex];
                
                // RESET COMPLET : Réinitialiser toutes les images à leur état de base
                cardImages.forEach((img, idx) => {
                    // Retirer les classes active/inactive
                    img.classList.remove('active', 'inactive');
                    
                    // Réinitialiser les styles inline
                    img.style.transform = '';
                    img.style.opacity = '';
                    
                    // Remettre les bonnes classes selon l'index
                    if (idx === currentIndex) {
                        img.classList.add('active');
                    } else {
                        img.classList.add('inactive');
                    }
                });
                
                // Forcer un reflow pour que les styles CSS de base soient appliqués
                void currentImage.offsetWidth;
                
                // Maintenant, préparer la transition
                // L'image actuelle est visible (opacity: 1, scale: 1)
                // On va la zoomer puis la faire disparaître
                
                // Préparer la nouvelle image en arrière-plan (invisible mais zoomée)
                nextImage.style.opacity = '0';
                nextImage.style.transform = 'scale(1.15)';
                nextImage.classList.remove('inactive');
                nextImage.classList.add('active');
                
                // Forcer un reflow avant de démarrer l'animation
                void nextImage.offsetWidth;
                
                // Étape 1: Zoom progressif sur l'image actuelle (800ms)
                currentImage.style.transform = 'scale(1.15)';
                
                // Étape 2: Après le zoom, transition d'opacité (500ms)
                const timeout1 = setTimeout(() => {
                    currentImage.style.opacity = '0';
                    nextImage.style.opacity = '1';
                    
                    // Étape 3: Dézoom de la nouvelle image (600ms)
                    const timeout2 = setTimeout(() => {
                        nextImage.style.transform = 'scale(1)';
                        
                        // Nettoyage de l'ancienne image
                        const timeout3 = setTimeout(() => {
                            currentImage.classList.remove('active');
                            currentImage.classList.add('inactive');
                            currentImage.style.transform = 'scale(1)';
                            currentImage.style.opacity = '1';
                        }, 600);
                        transitionTimeouts.push(timeout3);
                    }, 500);
                    transitionTimeouts.push(timeout2);
                }, 800);
                transitionTimeouts.push(timeout1);
                
                // Mettre à jour les dots
                dots.forEach((dot, idx) => {
                    dot.classList.toggle('active', idx === newIndex);
                });
                
                currentIndex = newIndex;
            }
            
            // Démarrer le sliding automatique : 3 secondes d'attente + temps de transition
            function startAutoSlide() {
                if (autoSlideInterval) return;
                
                autoSlideInterval = setInterval(() => {
                    if (isHovering) {
                        const nextIndex = (currentIndex + 1) % cardImages.length;
                        changeImage(nextIndex);
                    }
                }, 4900); // 3000ms d'attente + 1900ms de transition (800+500+600)
            }
            
            // Arrêter le sliding automatique
            function stopAutoSlide() {
                if (autoSlideInterval) {
                    clearInterval(autoSlideInterval);
                    autoSlideInterval = null;
                }
            }
            
            // Hover : démarrer le sliding automatique
            item.addEventListener('mouseenter', function() {
                isHovering = true;
                startAutoSlide();
            });
            
            item.addEventListener('mouseleave', function() {
                isHovering = false;
                stopAutoSlide();
                
                // Reset à la première image
                if (currentIndex !== 0) {
                    cardImages.forEach((img, idx) => {
                        img.style.transform = 'scale(1)';
                        img.style.opacity = '1';
                        if (idx === 0) {
                            img.classList.add('active');
                            img.classList.remove('inactive');
                        } else {
                            img.classList.remove('active');
                            img.classList.add('inactive');
                        }
                    });
                    
                    dots.forEach((dot, idx) => {
                        dot.classList.toggle('active', idx === 0);
                    });
                    
                    currentIndex = 0;
                }
            });
            
            // Clic sur les dots
            dots.forEach((dot, index) => {
                dot.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Arrêter complètement l'auto-slide
                    stopAutoSlide();
                    // Changer l'image
                    changeImage(index);
                    // Redémarrer l'auto-slide depuis zéro si on est en hover
                    if (isHovering) {
                        startAutoSlide();
                    }
                });
            });
        });
    }
    
    // Initialize carousel for existing items
    initImageCarousel();
    
    // Filter functionality - Fetch products by category
    if (filterButtons.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                currentCategory = this.getAttribute('data-category');
                currentOffset = 0; // Reset à 0 pour charger depuis le début
                
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Vider la galerie
                galleryGrid.innerHTML = '';
                
                // Fetch les produits de la catégorie
                const url = `/api/creations/load-more?offset=0&limit=6${currentCategory ? '&category=' + currentCategory : ''}`;
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.creations.length === 0) {
                            galleryGrid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: var(--gris); padding: 3rem;">Aucune création dans cette catégorie pour le moment.</p>';
                            if (loadMoreBtn) {
                                loadMoreBtn.style.display = 'none';
                            }
                            return;
                        }
                        
                        data.creations.forEach(creation => {
                            const card = document.createElement('div');
                            card.className = 'card gallery-item';
                            card.setAttribute('data-category', creation.category);
                            
                            // Ajouter les images pour le carousel
                            if (creation.images && creation.images.length > 0) {
                                card.setAttribute('data-images', JSON.stringify(creation.images));
                            }
                            
                            card.style.transition = 'var(--transition)';
                            card.style.opacity = '0';
                            
                            // Créer le HTML avec la nouvelle structure
                            let imagesHTML = '';
                            let dotsHTML = '';
                            
                            if (creation.images && creation.images.length > 0) {
                                creation.images.forEach((img, idx) => {
                                    imagesHTML += `<div class="card-image ${idx === 0 ? 'active' : 'inactive'}" style="background-image: url('/uploads/${img}');"></div>`;
                                });
                                
                                if (creation.images.length > 1) {
                                    dotsHTML = '<div class="card-dots">';
                                    creation.images.forEach((img, idx) => {
                                        dotsHTML += `<div class="card-dot ${idx === 0 ? 'active' : ''}" data-index="${idx}"></div>`;
                                    });
                                    dotsHTML += '</div>';
                                }
                            } else {
                                imagesHTML = '<div class="card-image active" style="background-image: url(\'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=600&q=75\');"></div>';
                            }
                            
                            card.innerHTML = `
                                <div class="card-image-container">
                                    ${imagesHTML}
                                    <div class="card-badge">${creation.categoryName}</div>
                                    ${dotsHTML}
                                </div>
                                <div class="card-content">
                                    <h3>${creation.title}</h3>
                                    <p>${creation.description}</p>
                                    <div class="card-footer">
                                        <span class="card-price">Sur devis</span>
                                        <a href="#contact" class="btn" style="padding: 8px 20px; font-size: 0.9rem;">Discutons-en</a>
                                    </div>
                                </div>
                            `;
                            
                            galleryGrid.appendChild(card);
                            setTimeout(() => {
                                card.style.opacity = '1';
                            }, 100);
                        });
                        
                        // Réinitialiser le carousel pour les nouvelles cartes
                        initImageCarousel();
                        
                        // Mettre à jour l'offset
                        currentOffset = 6;
                        
                        // Afficher/masquer le bouton "Voir plus"
                        if (loadMoreBtn) {
                            loadMoreBtn.style.display = data.hasMore ? 'inline-block' : 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors du chargement des créations:', error);
                        galleryGrid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: var(--gris); padding: 3rem;">Erreur lors du chargement des créations.</p>';
                    });
            });
        });
    }

    // Load more functionality
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const url = `/api/creations/load-more?offset=${currentOffset}${currentCategory ? '&category=' + currentCategory : ''}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    data.creations.forEach(creation => {
                        const card = document.createElement('div');
                        card.className = 'card gallery-item';
                        card.setAttribute('data-category', creation.category);
                        
                        // Ajouter les images pour le carousel
                        if (creation.images && creation.images.length > 0) {
                            card.setAttribute('data-images', JSON.stringify(creation.images));
                        }
                        
                        card.style.transition = 'var(--transition)';
                        card.style.opacity = '0';
                        
                        // Créer le HTML avec la structure correcte (identique au filtre)
                        let imagesHTML = '';
                        let dotsHTML = '';
                        
                        if (creation.images && creation.images.length > 0) {
                            creation.images.forEach((img, idx) => {
                                imagesHTML += `<div class="card-image ${idx === 0 ? 'active' : 'inactive'}" style="background-image: url('/uploads/${img}');"></div>`;
                            });
                            
                            if (creation.images.length > 1) {
                                dotsHTML = '<div class="card-dots">';
                                creation.images.forEach((img, idx) => {
                                    dotsHTML += `<div class="card-dot ${idx === 0 ? 'active' : ''}" data-index="${idx}"></div>`;
                                });
                                dotsHTML += '</div>';
                            }
                        } else {
                            imagesHTML = '<div class="card-image active" style="background-image: url(\'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=600&q=75\');"></div>';
                        }
                        
                        card.innerHTML = `
                            <div class="card-image-container">
                                ${imagesHTML}
                                <div class="card-badge">${creation.categoryName}</div>
                                ${dotsHTML}
                            </div>
                            <div class="card-content">
                                <h3>${creation.title}</h3>
                                <p>${creation.description}</p>
                                <div class="card-footer">
                                    <span class="card-price">Sur devis</span>
                                    <a href="#contact" class="btn" style="padding: 8px 20px; font-size: 0.9rem;">Discutons-en</a>
                                </div>
                            </div>
                        `;
                        
                        galleryGrid.appendChild(card);
                        setTimeout(() => {
                            card.style.opacity = '1';
                        }, 100);
                    });

                    // Réinitialiser le carousel pour les nouvelles cartes
                    initImageCarousel();

                    currentOffset += 9;

                    if (!data.hasMore) {
                        loadMoreBtn.style.display = 'none';
                    }
                });
        });
    }

    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const nav = document.querySelector('nav');
    const body = document.body;
    
    if (mobileMenuToggle) {
        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'mobile-overlay';
        overlay.style.zIndex = '10'; // Derrière le menu (10000) mais au-dessus du contenu
        body.appendChild(overlay);
        
        mobileMenuToggle.addEventListener('click', function() {
            nav.classList.toggle('active');
            overlay.classList.toggle('active');
        });
        
        // Close menu when clicking overlay
        overlay.addEventListener('click', function() {
            nav.classList.remove('active');
            overlay.classList.remove('active');
        });
        
        // Close menu when clicking a link
        const navLinks = nav.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                nav.classList.remove('active');
                overlay.classList.remove('active');
            });
        });
    }
});
