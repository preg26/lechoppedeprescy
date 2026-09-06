# L'échoppe de Prescy

Site web vitrine pour une bijouterie artisanale spécialisée dans la création, la réparation et la transformation de bijoux en or et argent.

## 🎯 À propos

L'échoppe de Prescy est une bijouterie artisanale où chaque pièce est façonnée à la main. Le site présente le savoir-faire de l'artisan, ses créations et permet aux clients de prendre contact pour leurs projets.

## ✨ Fonctionnalités

### Site public (One-page)
- **Hero** : Présentation de l'atelier avec animation subtile
- **Savoir-faire** : Mise en avant des 3 services principaux (Création, Réparation, Transformation)
- **Galerie de créations** : Affichage des réalisations avec filtres par catégorie
- **Processus de travail** : Explication en 3 étapes du déroulement d'un projet
- **Contact** : Formulaire de demande et lien vers Facebook Messenger
- **Navigation smooth scroll** : Navigation fluide entre les sections
- **Design responsive** : Optimisé pour tous les écrans

### Panel d'administration
- **Gestion des créations** : CRUD complet avec upload et réorganisation d'images par drag & drop
- **Gestion des catégories** : Organisation des créations par type
- **Gestion des demandes de contact** : Suivi et traitement des demandes clients
- **Gestion du contenu** : Édition de tous les textes du site
- **Gestion du logo** : Upload du logo personnalisé
- **Gestion des utilisateurs** : Système de rôles avec permissions granulaires

### Système de rôles
- **ROLE_ADMIN** : Accès complet à toutes les fonctionnalités
- **ROLE_CONTENT_MANAGER** : Gestion des contenus, créations et catégories
- **ROLE_CONTACT_MANAGER** : Gestion des demandes de contact uniquement

## 🛠️ Technologies

- **Framework** : Symfony 7.4
- **Template Engine** : Twig
- **Base de données** : SQLite (via Doctrine ORM)
- **Frontend** : HTML5, CSS3, JavaScript vanilla
- **Design** : Mobile-first, responsive

## 📋 Prérequis

- PHP 8.2 ou supérieur
- Composer
- SQLite3

## 🚀 Installation

1. **Cloner le projet**
```bash
git clone <repository-url>
cd lechoppe-de-prescy
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Créer la base de données**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

4. **Initialiser les données**
```bash
php bin/console app:init-data
```
Cette commande crée :
- Les contenus par défaut du site
- Les catégories de base (Bagues, Bracelets, Boucles d'oreilles)

5. **Lancer le serveur de développement**
```bash
symfony server:start
```
ou
```bash
php -S localhost:8000 -t public
```

6. **Accéder au site**
- Site public : `http://localhost:8000`
- Panel admin : `http://localhost:8000/admin/login`

## 📁 Structure du projet

```
lechoppe-de-prescy/
├── config/              # Configuration Symfony
├── public/
│   ├── css/            # Styles (main.css, admin.css)
│   ├── js/             # Scripts (main.js, admin.js)
│   ├── images/         # Images du site
│   └── uploads/        # Images uploadées (créations)
├── src/
│   ├── Command/        # Commandes CLI
│   ├── Controller/     # Contrôleurs (Main, Admin)
│   ├── Entity/         # Entités Doctrine
│   └── Repository/     # Repositories
├── templates/
│   ├── main/           # Templates publics
│   └── admin/          # Templates admin
└── migrations/         # Migrations de base de données
```

## 🎨 Charte graphique

- **Couleur principale** : #27715A (vert profond)
- **Couleurs secondaires** : 
  - #3A9B7F (vert clair)
  - #1A4D3C (vert foncé)
- **Accents** :
  - Or : #C9A961
  - Argent : #B8B8B8

## 📝 Fonctionnalités avancées

### Gestion des images
- Upload multiple d'images
- Réorganisation par drag & drop
- Suppression individuelle avec confirmation
- Badge "NEW" sur les nouvelles images
- Placeholder visuel pendant le drag
- Prévisualisation avant upload

### Formulaire de contact
- Validation côté serveur
- Système de statuts (À traiter / Traitée)
- Page de détail pour chaque demande
- Filtres et recherche

### Édition de contenu
- Tous les textes du site sont éditables depuis l'admin
- Organisation par sections
- Recherche dans les contenus

## 🔄 Commandes utiles

```bash
# Créer un nouvel utilisateur admin
php bin/console app:create-user

# Réinitialiser les données par défaut
php bin/console app:init-data

# Vider le cache
php bin/console cache:clear

# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate
```

## 📦 Déploiement

Pour le déploiement en production :

1. Configurer les variables d'environnement dans `.env.local`
2. Passer en mode production : `APP_ENV=prod`
3. Installer les dépendances : `composer install --no-dev --optimize-autoloader`
4. Vider le cache : `php bin/console cache:clear --env=prod`
5. Exécuter les migrations : `php bin/console doctrine:migrations:migrate --no-interaction`

## 📄 Licence

Projet privé - Tous droits réservés

## 👤 Auteur

L'échoppe de Prescy - Bijouterie artisanale
