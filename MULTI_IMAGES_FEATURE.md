# Système Multi-Images - Documentation

## ✅ Améliorations implémentées

### **1. Images uniques téléchargées** ✓
- **22 images uniques** de bijoux haute qualité
- Suppression de tous les doublons (32 → 22)
- Nommage simplifié : `creation_{id}.jpg`
- Stockées dans `public/uploads/`

**Commande créée :**
```bash
php bin/console app:download-unique-images
```

---

### **2. Badge admin flottant** ✓

**Problème résolu :** Le badge revenait à la ligne sous le texte du menu.

**Solution :**
- Utilisation de `display: flex` sur les liens
- `justify-content: space-between` pour espacer
- `flex-shrink: 0` sur le badge pour éviter le rétrécissement
- `margin-left: auto` pour pousser le badge à droite

**Résultat :** Badge toujours aligné à droite, jamais de retour à la ligne.

---

### **3. Système multi-images par produit** ✓

#### **Base de données**
- Champ `image` (string) → `images` (JSON array)
- Migration créée et exécutée
- Support de plusieurs images par création

#### **Entité Creation**
```php
#[ORM\Column(type: Types::JSON, nullable: true)]
private ?array $images = null;

public function getImages(): ?array
public function setImages(?array $images): static
public function getFirstImage(): ?string  // Helper pour compatibilité
```

#### **Contrôleurs Admin**
- **newCreation** : Upload multiple d'images avec `name="images[]"`
- **editCreation** : Ajout de nouvelles images aux images existantes
- Gestion du tableau d'images dans les deux cas

#### **Templates Admin**

**Création (`new.html.twig`) :**
```html
<input type="file" name="images[]" accept="image/*" multiple>
```
- Sélection multiple d'images
- Message d'aide pour l'utilisateur

**Édition (`edit.html.twig`) :**
- Affichage de toutes les images existantes (grille)
- Compteur "+X" si plusieurs images
- Ajout de nouvelles images sans supprimer les anciennes

**Liste (`index.html.twig`) :**
- Affichage de la première image
- Badge "+X" pour indiquer le nombre d'images supplémentaires

---

### **4. Défilement d'images au survol** ✓

#### **Frontend - Fonctionnalité**
Au survol d'une carte de création :
- Les images défilent automatiquement toutes les **800ms**
- Transition smooth entre les images
- Retour à la première image quand la souris quitte la carte

#### **Implémentation JavaScript**
```javascript
// Stockage des images dans data-attribute
<div data-images='["image1.jpg", "image2.jpg", "image3.jpg"]'>

// Au mouseenter : démarrage de l'interval
// Au mouseleave : arrêt et reset à la première image
```

#### **Template Frontend**
```twig
<div class="card gallery-item" 
     data-category="{{ creation.category.slug }}" 
     data-images="{{ creation.images|json_encode|e('html_attr') }}">
```

#### **Fonctionnement**
1. Parse du JSON stocké dans `data-images`
2. Vérification qu'il y a plusieurs images
3. Création d'un interval au survol (800ms)
4. Changement du `background-image` de la carte
5. Reset à la première image au départ de la souris

---

## 📊 Résultats

**Images :**
- 22 images uniques (vs 32 doublons avant)
- Nommage propre et cohérent
- Taille optimisée (800x800, qualité 80)

**Admin :**
- Badge flottant sans retour à la ligne ✓
- Upload multiple d'images fonctionnel ✓
- Affichage des images existantes ✓
- Compteur d'images visible ✓

**Frontend :**
- Défilement automatique au survol ✓
- Transition smooth entre images ✓
- Reset automatique au départ ✓
- Compatible avec pagination AJAX ✓

---

## 🎯 Utilisation

### **Admin - Ajouter une création avec plusieurs images**
1. Aller dans **Créations** → **+ Nouvelle création**
2. Remplir le formulaire
3. Cliquer sur "Images (plusieurs possibles)"
4. **Sélectionner plusieurs images** (Ctrl+clic ou Cmd+clic)
5. Enregistrer

### **Admin - Ajouter des images à une création existante**
1. Aller dans **Créations**
2. Cliquer sur **Modifier** sur une création
3. Voir les images actuelles affichées en grille
4. Cliquer sur "Ajouter de nouvelles images"
5. Sélectionner de nouvelles images
6. Les nouvelles images s'ajoutent aux existantes

### **Frontend - Voir le défilement**
1. Aller sur la page d'accueil
2. Scroller jusqu'à la section "Créations"
3. **Survoler une carte** avec plusieurs images
4. Les images défilent automatiquement toutes les 800ms
5. Quitter la carte → retour à la première image

---

## 🔧 Technique

### **Fichiers modifiés**

**Entité :**
- `src/Entity/Creation.php` - Champ `images` (JSON array)

**Migration :**
- `migrations/Version20260604000949.php` - Changement de colonne

**Contrôleurs :**
- `src/Controller/AdminController.php` - Upload multiple

**Templates Admin :**
- `templates/admin/creations/new.html.twig` - Input multiple
- `templates/admin/creations/edit.html.twig` - Grille d'images + ajout
- `templates/admin/creations/index.html.twig` - Compteur d'images

**Templates Frontend :**
- `templates/main/home.html.twig` - Data-attribute images

**JavaScript :**
- `public/js/main.js` - Fonction `initImageCarousel()`

**CSS :**
- `public/css/admin.css` - Badge flottant (flexbox)

**Commande :**
- `src/Command/DownloadUniqueImagesCommand.php` - Images uniques

---

## ✨ Avantages

**Pour l'admin :**
- Upload multiple en une fois
- Ajout progressif d'images
- Visualisation claire des images existantes
- Badge flottant propre et lisible

**Pour les visiteurs :**
- Découverte de plusieurs angles du produit
- Interaction engageante au survol
- Pas de clic nécessaire
- Expérience fluide et moderne

**Technique :**
- Stockage JSON flexible
- Compatibilité avec l'existant
- Performance optimisée
- Code maintenable

---

## 🎨 Personnalisation

### **Modifier la vitesse de défilement**
Dans `public/js/main.js` ligne 31 :
```javascript
}, 800); // Changer la valeur (en millisecondes)
```

### **Modifier le style du badge compteur**
Dans `templates/admin/creations/index.html.twig` ligne 36 :
```html
<span style="font-size: 0.7rem; color: #6B6B6B;">+{{ creation.images|length - 1 }}</span>
```

---

Votre système multi-images est maintenant complet et fonctionnel ! 🎉📸
