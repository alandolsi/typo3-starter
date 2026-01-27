# TYPO3 Cloud Starter

[![CI](https://github.com/alandolsi/landolsi-starter/actions/workflows/ci.yml/badge.svg)](https://github.com/alandolsi/typo3-starter/actions/workflows/ci.yml)
[![Release](https://github.com/alandolsi/landolsi-starter/actions/workflows/release.yml/badge.svg)](https://github.com/alandolsi/typo3-starter/actions/workflows/release.yml)

Ein "Cloud-Ready" TYPO3 v13 Boilerplate für Deployment via Coolify.

## Tech Stack

- **CMS**: TYPO3 v13 LTS
- **PHP**: 8.3
- **Webserver**: Apache
- **Datenbank**: MariaDB 10.11
- **Lokale Entwicklung**: DDEV
- **Production Hosting**: Coolify (Docker)
- **CI/CD**: GitHub Actions
- **Container Registry**: GitHub Container Registry (ghcr.io)

## Features

- ✅ Docker-basiertes Deployment
- ✅ Automatische Umgebungserkennung (DDEV/Production)
- ✅ 12-Factor App Prinzipien
- ✅ CI/CD Pipeline mit GitHub Actions
- ✅ Code Quality Checks (PHP-CS-Fixer, PHPStan)
- ✅ Security Audits
- ✅ Deutsche Lokalisierung (de_DE.UTF-8)
- ✅ Site Package mit Custom Branding
- ✅ Docker Image Snapshots für Rollback
- ✅ DDEV Provider für Production-Pull

## Voraussetzungen

- [DDEV](https://ddev.readthedocs.io/en/stable/) (für lokale Entwicklung)
- [Composer](https://getcomposer.org/download/)

## Lokale Entwicklung

```bash
# Projekt klonen
git clone https://github.com/alandolsi/typo3-starter.git
cd typo3-starter

# Abhängigkeiten installieren
ddev composer install

# Projekt starten
ddev start

# TYPO3 Setup starten (erstes Mal)
ddev exec ./vendor/bin/typo3 setup

# Browser öffnen
ddev launch

# TYPO3 Backend
ddev launch /typo3

# Cache leeren
ddev typo3 cache:flush
```

## Production-Daten lokal synchronisieren

Mit dem DDEV Coolify Provider kannst du Datenbank und Dateien von Production ziehen:

```bash
# 1. Konfiguration erstellen
cp .ddev/.env.coolify.example .ddev/.env.coolify
# Zugangsdaten in .ddev/.env.coolify eintragen

# 2. Production-Daten pullen
ddev pull coolify
```

## CI/CD Pipeline

### Continuous Integration (bei jedem Push/PR)

| Job | Beschreibung |
|-----|-------------|
| **Code Quality** | Composer validate, Dependencies prüfen |
| **PHP-CS-Fixer** | Code Style nach PSR-12 |
| **PHPStan** | Statische Code-Analyse |
| **Security** | Composer Audit für Vulnerabilities |
| **Docker Build** | Test-Build des Docker Images |

### Continuous Deployment

| Trigger | Aktion |
|---------|--------|
| Push auf `main` | Auto-Deploy zu Coolify (via Webhook) |
| Git Tag `v*` | Docker Image → GitHub Container Registry + GitHub Release |

## Backup & Rollback

### Docker Image Snapshots

Bei jedem Release (Git Tag) wird ein Docker Image in GHCR gespeichert:

```bash
# Image für Rollback verwenden
docker pull ghcr.io/alandolsi/typo3-starter:v1.0.0
```

### Rollback in Coolify

1. Gehe zu deiner App in Coolify
2. Ändere **Image** zu: `ghcr.io/alandolsi/typo3-starter:v1.0.0`
3. Oder wähle einen älteren **Git Tag** unter Branch/Tag

## Deployment (Coolify)

1. Repository in Coolify hinzufügen
2. Build-Methode: **Dockerfile**
3. Environment Variables setzen:

```env
TYPO3_CONTEXT=Production
TYPO3_DB_HOST=<mariadb-service>
TYPO3_DB_PORT=3306
TYPO3_DB_NAME=typo3db
TYPO3_DB_USERNAME=typo3user
TYPO3_DB_PASSWORD=<secret>
IS_DOCKER_ENV=true
```

4. Ports Exposes: `80`
5. Post-Deployment Command:
```bash
chown -R www-data:www-data /var/www/html/var && php /var/www/html/vendor/bin/typo3 cache:flush
```
6. Deploy

## Projektstruktur

```
├── .github/
│   └── workflows/
│       ├── ci.yml           # CI Pipeline
│       └── release.yml      # Release + Docker Image
├── .ddev/
│   ├── providers/
│   │   └── coolify.yaml     # DDEV Pull Provider
│   └── .env.coolify.example # Zugangsdaten Template
├── config/
│   ├── sites/main/          # Site-Konfiguration
│   └── system/
│       ├── settings.php     # Basis-Konfiguration
│       └── additional.php   # Umgebungs-spezifische Config
├── packages/
│   └── site_package/        # Site Package mit TypoScript
├── public/                  # Document Root
├── var/                     # Cache, Logs, Sessions
├── Dockerfile               # Production Docker Image
└── .php-cs-fixer.php        # Code Style Konfiguration
```

## Architektur

Dieses Projekt folgt dem **12-Factor App** Ansatz:

- ✅ **Codebase** - Ein Repository, mehrere Deployments
- ✅ **Dependencies** - Explizit via Composer
- ✅ **Config** - Environment Variables statt Hardcoding
- ✅ **Backing Services** - DB als attached Resource
- ✅ **Build, Release, Run** - Strikte Trennung
- ✅ **Processes** - Stateless Container
- ✅ **Port Binding** - Self-contained via Apache
- ✅ **Dev/Prod Parity** - DDEV ≈ Production

## Lizenz

GPL-2.0 or later
