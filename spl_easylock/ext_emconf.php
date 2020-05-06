<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'EasyLock',
    'description' => 'This extension provides password security for frontend pages without user sections.',
    'category' => 'fe',
    'author' => 'Christian Reifenscheid',
    'author_email' => 'christian.reifenscheid.2112@gmail.com',
    'version' => '10.0.0',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearcacheonload' => true,
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ]
];
