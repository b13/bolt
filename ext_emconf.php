<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Bolt - An easy TYPO3 integration basis',
    'description' => 'This package ships best defaults for integrators',
    'category' => 'fe',
    'version' => '2.0.0',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearcacheonload' => true,
    'author' => 'Benni Mack',
    'author_email' => '',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0',
        ]
    ]
];

