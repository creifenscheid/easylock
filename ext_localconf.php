<?php

defined('TYPO3_MODE') || die();

(function ($extKey) {
    
    # add new field type to NodeFactory
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1487112284] = [
        'nodeName' => 'securedPassword',
        'priority' => '70',
        'class' => \ChristianReifenscheid\Easylock\Form\Element\SecuredPasswordElement::class
    ];
    
})('easylock');