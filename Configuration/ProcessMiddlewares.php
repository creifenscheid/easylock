<?php

/**
 * An array consisting of implementations of middlewares for a middleware stack to be registered
 *
 *  'stackname' => [
 *      'middleware-identifier' => [
 *         'target' => classname or callable
 *         'before/after' => array of dependencies
 *      ]
 *   ]
 */
return [
    'frontend' => [
        'christianreifenscheid/easylock/content-protector' => [
            'target' => \ChristianReifenscheid\Easylock\Middleware\ContentProtector::class,
            'before' => 'typo3/cms-frontend/content-length-headers'
        ]
    ]
];