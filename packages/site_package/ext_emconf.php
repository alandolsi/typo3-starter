<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'TYPO3 Cloud Starter Site Package',
    'description' => 'Site Package für das TYPO3 Cloud Starter Boilerplate mit grundlegendem TypoScript Setup.',
    'category' => 'templates',
    'author' => 'Lotfi Landolsi',
    'author_email' => 'info@landolsi.de',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.0.0-13.99.99',
            'fluid_styled_content' => '13.0.0-13.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
