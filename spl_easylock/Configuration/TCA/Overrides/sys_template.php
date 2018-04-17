<?php

if (!defined ('TYPO3_MODE')) {
    die('Access denied.');
}

// add static typoscript configuration
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile ('spl_easylock', 'Configuration/TypoScript', 'EasyLock');