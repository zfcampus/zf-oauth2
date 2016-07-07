<?php
/**
 * @copyright Copyright (c) 2016 João Dias <mail@joaodias.eu>
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

return [
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'ZFTest\OAuth2\Controller\CustomAdapter' => 'ZFTest\OAuth2\Controller\CustomAdapter',
        ],
    ],
    'zf-oauth2' => [
        'storage' => 'ZFTest\OAuth2\Controller\CustomAdapter',
        'allow_implicit' => true,
        'enforce_state'  => true,
    ],
];
