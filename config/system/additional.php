<?php
// config/system/additional.php

// 1. Sicherheit: Keine Passwörter im Code!
// Wir lesen die Zugangsdaten aus den Umgebungsvariablen (Environment Variables).
// Diese werden später in Coolify gesetzt.

// DDEV setzt automatisch IS_DDEV_PROJECT=true
$isDdev = getenv('IS_DDEV_PROJECT') === 'true';

// Datenbank-Konfiguration
if ($isDdev) {
    // DDEV: Nutze die DDEV-Standard-Umgebungsvariablen
    $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default'] = array_merge(
        $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default'] ?? [],
        [
            'host' => 'db',
            'dbname' => 'db',
            'user' => 'db',
            'password' => 'db',
            'port' => '3306',
        ]
    );
} else {
    // Production: Nutze Umgebungsvariablen aus Coolify
    $dbHost = getenv('TYPO3_DB_HOST');
    $dbName = getenv('TYPO3_DB_NAME');
    $dbUser = getenv('TYPO3_DB_USERNAME');
    $dbPass = getenv('TYPO3_DB_PASSWORD');

    if ($dbHost && $dbName && $dbUser && $dbPass) {
        $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default'] = array_merge(
            $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default'] ?? [],
            [
                'host' => $dbHost,
                'dbname' => $dbName,
                'user' => $dbUser,
                'password' => $dbPass,
                'port' => getenv('TYPO3_DB_PORT') ?: '3306',
            ]
        );
    }
}

// 2. GraphicsMagick Konfiguration
// Im Docker-Container ist GM installiert, wir müssen den Pfad setzen.
if (getenv('IS_DOCKER_ENV') === 'true') {
    $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor'] = 'GraphicsMagick';
    $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_path'] = '/usr/bin/';
    $GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_path_lzw'] = '/usr/bin/';
}

// 3. Application Context (Production vs Development)
$typo3Context = getenv('TYPO3_CONTEXT') ?: ($isDdev ? 'Development' : 'Production');

if ($typo3Context === 'Development') {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'] = 1;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = '*';
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['exceptionalErrors'] = E_ALL;
} else {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'] = 0;
}

// 4. System Locale für UTF-8 Filesystem
$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLocale'] = 'de_DE.UTF-8';

// ------------------------------
// Trusted Hosts Pattern
// ------------------------------
// Production: Nur die erlaubten Domains, DDEV: alle erlauben
if ($isDdev) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = '.*';
} else {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = 'starter\.landolsi\.de';
}

// ------------------------------
// Reverse Proxy Konfiguration (Coolify/Traefik)
// ------------------------------
// Traefik terminiert SSL, daher muss TYPO3 wissen, dass die Verbindung HTTPS ist
if (getenv('IS_DOCKER_ENV') === 'true') {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyIP'] = '*';
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxySSL'] = '*';
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyHeaderMultiValue'] = 'first';
}