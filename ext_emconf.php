<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Bolt - An easy TYPO3 integration basis',
    'description' => 'Connect a Site configuration to a Site package / extension',
    'category' => 'fe',
    'version' => '2.2.0',
    'state' => 'stable',
    'clearcacheonload' => true,
    'author' => 'Benni Mack',
    'author_email' => 'typo3@b13.com',
    'author_company' => 'b13 GmbH',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-12.99.99',
        ]
    ]
];
