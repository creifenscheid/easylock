<?php

if (!defined ('TYPO3_MODE')) {
    die('Access denied.');
}

# init contentPostProc-output hook - tslib_fe.php
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] = 'EXT:spl_easylock/Classes/Hooks/Frontend/CheckPassword.php:SPL\SplEasylock\Hooks\Frontend\\EasyLock->checkPassword';