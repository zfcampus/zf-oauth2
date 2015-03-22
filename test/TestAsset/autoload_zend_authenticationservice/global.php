<?php
/**
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

return array(
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'zf-oauth2' => array(
        'storage' => 'ZF\OAuth2\Adapter\PdoAdapter',
        'db' => array(
            'dsn'      => 'sqlite:' . __DIR__ . '/dbtest.sqlite',
        ),
        'allow_implicit' => true,
        'enforce_state'  => true,
    ),
    'service_manager' => array(
        'invokables' => array(
            'ZF\OAuth2\Provider\UserId' => 'ZF\OAuth2\Provider\UserId\AuthenticationService',
            'Zend\Authentication\AuthenticationService' => 'Zend\Authentication\AuthenticationService',
        ),
    ),
);
