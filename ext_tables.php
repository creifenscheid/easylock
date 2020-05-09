<?php

defined('TYPO3_MODE') || die();

(function ($extKey) {
    
    // Register CSS
    $GLOBALS['TBE_STYLES']['skins'][$extKey] = [
        'name' => 'LLL:EXT:easylock/Resources/Private/Language/locallang_general.xml:extension.name',
        'stylesheetDirectories' => [
            'css' => 'EXT:easylock/Resources/Public/Css/'
        ]
    ];
    
})('easylock');