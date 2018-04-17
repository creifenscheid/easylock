<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

// Configure new fields:
$columns = array(
    'tx_spleasylock_password' => array(
        'label' => 'LLL:EXT:spl_easylock/Resources/Private/Language/locallang_db.xlf:pages.tx_spleasylock.password',
        'exclude' => 1,
        'config' => array(
            'type' => 'input',
            'eval' => 'trim,password', //md5 - TYPO3 bug: clearing the field is not possible - change is not stored in database
            'size' => 13
        )
    )
);

// Add columns to TCA of pages
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $columns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--div--;LLL:EXT:' . 'spl_easylock' . '/Resources/Private/Language/locallang_db.xml:pages.palette_title,
    tx_spleasylock_password'
);

// Add new palette
$GLOBALS['TCA']['sys_file_reference']['palettes']['spleasylockPalette'] = $GLOBALS['TCA']['sys_file_reference']['palettes']['basicoverlayPalette'];
$GLOBALS['TCA']['sys_file_reference']['palettes']['spleasylockPalette']['showitem'] = '';