# Backend Administration - L'échoppe de Prescy

## 🎯 Système complet mis en place

### ✅ Fonctionnalités implémentées

#### 1. **Authentification sécurisée**
- Système de connexion avec email/mot de passe
- Protection des routes admin (ROLE_ADMIN requis)
- Déconnexion sécurisée

#### 2. **Gestion des catégories**
- Créer, modifier, supprimer des catégories
- Slug automatique généré
- Description optionnelle
- Compteur de créations par catégorie

#### 3. **Gestion des créations**
- Ajouter des créations avec image
- Upload d'images dans `public/uploads/`
- Associer à une catégorie
- Statut publié/brouillon
- Modifier et supprimer des créations

#### 4. **Gestion des demandes de contact**
- Liste de toutes les demandes
- Statuts : "À traiter" / "Traitée"
- Marquer comme traitée avec date
- Affichage des demandes en attente sur le dashboard

#### 5. **Gestion du contenu**
- Modifier les textes du site (système PageContent)
- Organisation par section
- Édition simple via interface admin

#### 6. **Gestion du logo**
- Upload du logo via interface admin
- Sauvegarde automatique dans `public/images/logo.png`
- Prévisualisation du logo actuel

#### 7. **Galerie avec pagination**
- Affichage des 6 dernières créations sur la page d'accueil
- Filtrage par catégorie
- Bouton "Voir plus" pour charger 9 créations supplémentaires
- Pagination AJAX sans rechargement de page

---

## 🔐 Accès à l'administration

### Connexion
**URL:** http://localhost:8000/admin/login

**Identifiants par défaut:**
- Email: `admin@lechoppe.fr`
- Mot de passe: `admin123`

⚠️ **IMPORTANT:** Changez ce mot de passe en production !

---

## 📂 Structure de la base de données

### Entités créées

1. **User** - Utilisateurs admin
   - email (unique)
   - password (hashé)
   - roles (ROLE_ADMIN)

2. **Category** - Catégories de créations
   - name
   - slug (unique, auto-généré)
   - description
   - creations (relation OneToMany)

3. **Creation** - Créations/bijoux
   - title
   - description
   - image (nom du fichier)
   - category (relation ManyToOne)
   - isPublished (boolean)
   - createdAt (date)

4. **ContactRequest** - Demandes de contact
   - name
   - email
   - phone
   - message
   - status (pending/processed)
   - createdAt
   - processedAt

5. **PageContent** - Contenus éditables
   - key (unique)
   - value (texte)
   - section (optionnel)

---

## 🚀 Utilisation

### Créer des catégories
1. Aller dans **Catégories** > **+ Nouvelle catégorie**
2. Remplir le nom (ex: "Bagues", "Bracelets", "Boucles d'oreilles")
3. Ajouter une description (optionnel)
4. Le slug sera généré automatiquement

### Ajouter des créations
1. Aller dans **Créations** > **+ Nouvelle création**
2. Remplir le titre et la description
3. Sélectionner une catégorie
4. Uploader une image (JPG, PNG)
5. Cocher "Publier immédiatement" ou laisser en brouillon
6. Cliquer sur "Créer la création"

### Gérer les demandes de contact
1. Aller dans **Demandes de contact**
2. Voir toutes les demandes avec leur statut
3. Cliquer sur "Marquer comme traitée" pour une demande en attente
4. Les demandes traitées affichent la date de traitement

### Changer le logo
1. Aller dans **Logo**
2. Cliquer sur "Choisir un fichier"
3. Sélectionner votre logo (PNG avec fond transparent recommandé)
4. Cliquer sur "Mettre à jour le logo"
5. Le logo sera automatiquement affiché sur le site

---

## 🎨 Frontend - Galerie avec pagination

### Fonctionnement
- **Page d'accueil:** Affiche les 6 dernières créations
- **Filtres:** Boutons pour filtrer par catégorie (générés dynamiquement)
- **Bouton "Voir plus":** Charge 9 créations supplémentaires
- **AJAX:** Pas de rechargement de page

### API
**Endpoint:** `/api/creations/load-more`

**Paramètres:**
- `offset` : Position de départ (0, 9, 18...)
- `category` : ID de la catégorie (optionnel)

**Réponse JSON:**
```json
{
  "creations": [
    {
      "id": 1,
      "title": "Bague sur mesure",
      "description": "...",
      "image": "abc123.jpg",
      "category": "bagues",
      "categoryName": "Bagues"
    }
  ],
  "hasMore": true
}
```

---

## 📝 Formulaire de contact

Le formulaire de contact sur le site envoie les données vers `/contact/submit`.

Les demandes sont automatiquement enregistrées dans la base de données avec le statut "pending".

---

## 🛠️ Commandes utiles

### Créer un nouvel admin
```bash
php bin/console app:create-admin
```

### Créer une migration
```bash
php bin/console make:migration
```

### Exécuter les migrations
```bash
php bin/console doctrine:migrations:migrate
```

### Lancer le serveur
```bash
symfony server:start
# ou
php -S localhost:8000 -t public
```

---

## 📁 Fichiers importants

### Contrôleurs
- `src/Controller/AdminController.php` - Toutes les routes admin
- `src/Controller/SecurityController.php` - Login/logout
- `src/Controller/MainController.php` - Frontend + API

### Entités
- `src/Entity/User.php`
- `src/Entity/Category.php`
- `src/Entity/Creation.php`
- `src/Entity/ContactRequest.php`
- `src/Entity/PageContent.php`

### Templates Admin
- `templates/admin/base.html.twig` - Layout admin
- `templates/admin/dashboard.html.twig` - Tableau de bord
- `templates/admin/categories/` - Gestion catégories
- `templates/admin/creations/` - Gestion créations
- `templates/admin/contacts/` - Gestion demandes
- `templates/admin/content/` - Gestion contenus
- `templates/admin/logo.html.twig` - Gestion logo

### Configuration
- `config/packages/security.yaml` - Configuration sécurité

---

## 🎯 Prochaines étapes recommandées

1. **Créer des catégories** (Bagues, Bracelets, Boucles d'oreilles)
2. **Ajouter des créations** avec de vraies photos
3. **Uploader le logo** fourni
4. **Tester la galerie** avec filtres et pagination
5. **Changer le mot de passe admin** en production

---

## 🔒 Sécurité

- ✅ Mots de passe hashés avec Symfony PasswordHasher
- ✅ Protection CSRF sur tous les formulaires
- ✅ Routes admin protégées par ROLE_ADMIN
- ✅ Validation des uploads d'images
- ⚠️ Changez le mot de passe par défaut !

---

## 📞 Support

Pour toute question sur le système backend, référez-vous à ce document ou consultez la documentation Symfony officielle.
