<?php

defined('TYPO3_MODE') or die();

// add static typoscript configuration
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile ('easylock', 'Configuration/TypoScript', 'EasyLock');