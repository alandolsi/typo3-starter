<?php

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

// TypoScript als Static Template registrieren
ExtensionManagementUtility::addStaticFile(
    'site_package',
    'Configuration/TypoScript',
    'TYPO3 Cloud Starter'
);
