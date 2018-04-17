<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'EasyLock',
    'description' => 'Password security for frontend pages without user sections',
    'category' => 'fe',
    'author' => 'Christian Reifenscheid',
    'author_email' => 'sepp@einundzwanzig12.de',
    'version' => '1.0.0',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearcacheonload' => true,
    'constraints' => array(
        'depends' => array(
            'typo3' => '7.6.0-8.7.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
