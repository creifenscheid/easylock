<?php

defined('TYPO3_MODE') or die();

if (TYPO3_MODE === 'BE') {

    // Register CSS
    $GLOBALS['TBE_STYLES']['skins']['easylock'] = [
        'name' => 'EasyLock',
        'stylesheetDirectories' => [
            'css' => 'EXT:easylock/Resources/Public/Css/'
        ]
    ];
}