<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Bolt - An easy TYPO3 integration basis',
    'description' => 'This package ships best defaults for integrators',
    'category' => 'fe',
    'version' => '2.2.0',
    'state' => 'stable',
    'clearcacheonload' => true,
    'author' => 'Benni Mack',
    'author_email' => 'typo3@b13.com',
    'author_company' => 'b13 GmbH',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
        ]
    ]
];
