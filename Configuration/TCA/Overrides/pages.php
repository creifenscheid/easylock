<?php
defined('TYPO3_MODE') or die();

// Configure new fields:
$columns = [
    'tx_easylock_password' => [
        'label' => 'LLL:EXT:easylock/Resources/Private/Language/locallang_db.xlf:pages.tx_easylock.password',
        'exclude' => 1,
        'config' => [
            'type' => 'input',
            'renderType' => 'securedPassword',
            'eval' => 'trim,password,ChristianReifenscheid\\Easylock\\Evaluation\\SaltedMd5Evaluation', //typo3 md5 eval - bug: clearing the field is not possible - change is not stored in database
            'size' => 13
        ]
    ]
];

// Add columns to TCA of pages
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $columns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--div--;LLL:EXT:easylock/Resources/Private/Language/locallang_general.xml:extension.name,
    tx_easylock_password'
);