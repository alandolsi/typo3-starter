# TYPO3 Cloud Starter

[![CI](https://github.com/alandolsi/typo3-starter/actions/workflows/ci.yml/badge.svg)](https://github.com/alandolsi/typo3-starter/actions/workflows/ci.yml)
[![Release](https://github.com/alandolsi/typo3-starter/actions/workflows/release.yml/badge.svg)](https://github.com/alandolsi/typo3-starter/actions/workflows/release.yml)

Ein **Cloud-Ready TYPO3 v13 Boilerplate** für modernes Deployment via [Coolify](https://coolify.io). Folgt dem 12-Factor App Ansatz — keine Passwörter im Code, konfiguriert über Environment Variables.

## Tech Stack

| | |
|---|---|
| **CMS** | TYPO3 v13 LTS |
| **PHP** | 8.3 |
| **Webserver** | Apache |
| **Datenbank** | MariaDB 10.11 |
| **Lokale Entwicklung** | DDEV |
| **Hosting** | Coolify (Docker) |
| **CI/CD** | GitHub Actions |

## Features

- ✅ Docker-basiertes Deployment via Coolify
- ✅ Automatische Umgebungserkennung (DDEV / Production)
- ✅ 12-Factor App Prinzipien — keine Secrets im Git
- ✅ DDEV Provider: Production-Daten lokal synchronisieren
- ✅ CI/CD Pipeline mit Code Quality, Security Audit & Docker Build
- ✅ Docker Image Snapshots für Rollback
- ✅ Site Package mit TypoScript

---

## Schnellstart (lokale Entwicklung)

### Voraussetzungen

- [DDEV](https://ddev.readthedocs.io/en/stable/)
- Git

### Setup

```bash
git clone https://github.com/alandolsi/typo3-starter.git
cd typo3-starter
ddev start
ddev composer install
ddev typo3 setup
ddev launch
```

TYPO3 Backend: `https://typo3-starter.ddev.site/typo3`

---

## Entwicklungs-Workflow

### 1. Täglich starten — Production-Stand lokal holen

```bash
ddev auth ssh                    # SSH-Key in DDEV-Agent laden (einmalig pro Session)
ddev pull production -y          # DB + fileadmin von Production → lokal
```

Nach dem Pull ist der lokale Stand identisch mit Production. Cache wird automatisch geleert.

**Optionen:**
```bash
ddev pull production --skip-files -y   # Nur Datenbank
ddev pull production --skip-db -y      # Nur fileadmin
```

### 2. Lokal entwickeln

```bash
ddev typo3 cache:flush           # Cache leeren
ddev composer require vendor/paket
```

### 3. Code deployen → Production

```bash
git add .
git commit -m "feat: ..."
git push origin main
# → Coolify baut Docker Image und deployt automatisch
```

### 4. Inhalte nach Production übertragen (optional)

```bash
ddev push production -y          # ⚠️ Lokal → Production (10s Warnung)
```

Nur nötig wenn du lokal neue Seiten, Inhalte oder Dateien erstellt hast.

### Überblick: Was liegt wo?

| | Git → Coolify | `ddev push production` |
|---|:---:|:---:|
| PHP Code / Extensions | ✅ | — |
| composer.json / .lock | ✅ | — |
| TypoScript / Config | ✅ | — |
| Datenbank (Seiten, Inhalte) | — | ✅ |
| fileadmin (Bilder, PDFs…) | — | ✅ |

---

## Deployment (Coolify)

### Einrichtung

1. Repository in Coolify hinzufügen
2. Build-Methode: **Dockerfile**
3. Branch: `main`
4. Port: `80`

### Environment Variables

```env
TYPO3_CONTEXT=Production
TYPO3_DB_HOST=<mariadb-service-name>
TYPO3_DB_PORT=3306
TYPO3_DB_NAME=<dbname>
TYPO3_DB_USERNAME=<dbuser>
TYPO3_DB_PASSWORD=<secret>
IS_DOCKER_ENV=true
```

### DDEV Provider konfigurieren

Damit `ddev pull production` / `ddev push production` funktioniert, passe die Variablen in `.ddev/providers/production.yaml` an dein Coolify-Setup an:

```yaml
environment_variables:
  COOLIFY_SSH: "root@<your-server>"
  COOLIFY_APP_LABEL: "coolify.resourceName=<your-app-label>"
  COOLIFY_DB_LABEL: "coolify.resourceName=<your-db-label>"
  COOLIFY_DB_NAME: "<dbname>"
  COOLIFY_DB_USER: "<dbuser>"
```

Container werden automatisch per Coolify-Label gefunden — funktioniert auch nach Re-Deploys mit neuen Container-Namen. Das DB-Passwort wird zur Laufzeit aus dem Container geholt, kein Hardcoding nötig.

---

## CI/CD Pipeline

### Continuous Integration (Push / Pull Request)

| Job | Beschreibung |
|-----|-------------|
| **Code Quality** | `composer validate`, Abhängigkeiten prüfen |
| **PHP-CS-Fixer** | Code Style nach PSR-12 |
| **PHPStan** | Statische Code-Analyse |
| **Security** | `composer audit` — bekannte CVEs |
| **Docker Build** | Test-Build des Docker Images |

### Continuous Deployment

| Trigger | Aktion |
|---------|--------|
| Push auf `main` | Auto-Deploy via Coolify Webhook |
| Git Tag `v*` | Docker Image → GitHub Container Registry + GitHub Release |

---

## Backup & Rollback

Bei jedem Git Tag wird ein Docker Image in der GitHub Container Registry gespeichert:

```bash
docker pull ghcr.io/alandolsi/typo3-starter:v1.0.0
```

**Rollback in Coolify:** App öffnen → Branch/Tag auf gewünschten Tag setzen → Redeploy.

---

## Projektstruktur

```
├── .github/workflows/
│   ├── ci.yml                   # CI Pipeline
│   └── release.yml              # Release + Docker Image
├── .ddev/
│   └── providers/
│       └── production.yaml      # DDEV Pull/Push Provider
├── config/
│   ├── sites/                   # TYPO3 Site-Konfiguration
│   └── system/
│       ├── settings.php         # Basis-Konfiguration
│       └── additional.php       # Umgebungs-spezifische Config
├── packages/
│   └── site_package/            # Site Package mit TypoScript
├── public/                      # Document Root (Apache)
├── Dockerfile                   # Production Docker Image
└── entrypoint.sh                # Container-Start: Permissions + Apache
```

---

## Architektur

Dieses Projekt folgt dem **[12-Factor App](https://12factor.net)** Ansatz:

| Faktor | Umsetzung |
|--------|-----------|
| **Config** | Alle Secrets als Environment Variables — nichts im Git |
| **Dependencies** | Explizit via Composer, keine globalen Abhängigkeiten |
| **Dev/Prod Parity** | DDEV spiegelt Production: gleiche PHP-Version, gleiche DB |
| **Processes** | Stateless Container — kein State im Filesystem |
| **Logs** | TYPO3 schreibt in `var/log/`, Apache nach stdout |

---

## Lizenz

[GPL-2.0 or later](LICENSE)
