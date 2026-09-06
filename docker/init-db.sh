#!/bin/bash
set -e

echo "🚀 Initialisation de la base de données pour L'échoppe de Prescy"
echo "================================================================"

# Attendre que le container soit prêt
sleep 2

# Se placer dans le répertoire de l'application
cd /var/www/app

echo ""
echo "📦 Vérification de l'environnement..."
if [ ! -f ".env" ]; then
    echo "❌ Erreur: fichier .env manquant"
    exit 1
fi

echo "✅ Environnement configuré"

echo ""
echo "🗄️  Création de la base de données..."
php bin/console doctrine:database:create --if-not-exists --no-interaction

echo ""
echo "📋 Exécution des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo ""
echo "🌱 Initialisation des données (mode production)..."
php bin/console app:init-data --production --no-interaction

echo ""
echo "🧹 Nettoyage du cache..."
php bin/console cache:clear --no-warmup
php bin/console cache:warmup

echo ""
echo "✅ Base de données initialisée avec succès !"
echo ""
echo "📊 Contenu créé :"
echo "   - Tous les contenus de page (hero, savoir-faire, créations, atelier, contact, footer)"
echo "   - 3 catégories (Créations, Réparations, Transformations)"
echo "   - 0 créations (à ajouter via l'admin)"
echo "   - 0 demandes de contact"
echo ""
echo "🔐 N'oubliez pas de créer un utilisateur admin :"
echo "   php bin/console app:create-user"
echo ""
echo "================================================================"
echo "✨ Initialisation terminée !"
