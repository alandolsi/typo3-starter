#!/bin/bash
set -e

# Sicherstellen, dass www-data Schreibrechte auf var/ hat (auch bei Volume-Mounts)
chown -R www-data:www-data /var/www/html/var
chown -R www-data:www-data /var/www/html/public/typo3temp

# TYPO3 Cache leeren und Extensions einrichten (generiert frischen Cache für diese Umgebung)
su -s /bin/bash www-data -c "php /var/www/html/vendor/bin/typo3 cache:flush" || true
su -s /bin/bash www-data -c "php /var/www/html/vendor/bin/typo3 extension:setup" || true

# Apache starten (Default CMD des Base-Images)
exec apache2-foreground
