#!/bin/bash
set -e

# Sicherstellen, dass www-data Schreibrechte auf var/ hat (auch bei Volume-Mounts)
chown -R www-data:www-data /var/www/html/var

# Apache starten (Default CMD des Base-Images)
exec apache2-foreground
