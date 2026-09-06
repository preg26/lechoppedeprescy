#!/bin/bash
set -e

echo "🚀 Démarrage de L'échoppe de Prescy"

# Créer et configurer les permissions du dossier uploads
echo "📁 Configuration du dossier uploads..."
mkdir -p /var/www/app/public/uploads
chown -R www-data:www-data /var/www/app/public/uploads
chmod -R 775 /var/www/app/public/uploads

echo "✅ Permissions configurées"
echo "   Propriétaire: $(stat -c '%U:%G' /var/www/app/public/uploads)"
echo "   Permissions: $(stat -c '%a' /var/www/app/public/uploads)"

# Démarrer PHP-FPM et Nginx
echo "🌐 Démarrage des services..."
php-fpm &
nginx -g 'daemon off;'
