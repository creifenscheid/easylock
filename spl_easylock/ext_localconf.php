<?php

if (!defined ('TYPO3_MODE')) {
    die('Access denied.');
}

# add new field type to NodeFactory
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1487112284] = [
    'nodeName' => 'securedPassword',
    'priority' => '70',
    'class' => \SPL\SplEasylock\Form\Element\SecuredPasswordElement::class
];

# register md5 evaluation to be available in 'eval' of TCA
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['SPL\\SplEasylock\\Evaluation\\SaltedMd5Evaluation'] = '';

# init contentPostProc-output hook - tslib_fe.php
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] = 'SPL\\SplEasylock\\Hooks\\Frontend\\EasyLock->checkPassword';