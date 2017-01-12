<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2;

return [
    'controllers' => [
        'factories' => [
            'ZF\OAuth2\Controller\Auth' => Factory\AuthControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'oauth' => [
                'type' => 'literal',
                'options' => [
                    'route'    => '/oauth',
                    'defaults' => [
                        'controller' => 'ZF\OAuth2\Controller\Auth',
                        'action'     => 'token',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'revoke' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/revoke',
                            'defaults' => [
                                'action' => 'revoke',
                            ],
                        ],
                    ],
                    'authorize' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/authorize',
                            'defaults' => [
                                'action' => 'authorize',
                            ],
                        ],
                    ],
                    'resource' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/resource',
                            'defaults' => [
                                'action' => 'resource',
                            ],
                        ],
                    ],
                    'code' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/receivecode',
                            'defaults' => [
                                'action' => 'receiveCode',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'aliases' => [
            'ZF\OAuth2\Provider\UserId' => Provider\UserId\AuthenticationService::class,
        ],
        'factories' => [
            Adapter\PdoAdapter::class    => Factory\PdoAdapterFactory::class,
            Adapter\IbmDb2Adapter::class => Factory\IbmDb2AdapterFactory::class,
            Adapter\MongoAdapter::class  => Factory\MongoAdapterFactory::class,
            Provider\UserId\AuthenticationService::class => Provider\UserId\AuthenticationServiceFactory::class,
            'ZF\OAuth2\Service\OAuth2Server'  => Factory\OAuth2ServerFactory::class
        ]
    ],
    'view_manager' => [
        'template_map' => [
            'oauth/authorize'    => __DIR__ . '/../view/zf/auth/authorize.phtml',
            'oauth/receive-code' => __DIR__ . '/../view/zf/auth/receive-code.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'zf-oauth2' => [
        /*
         * Config can include:
         * - 'storage' => 'name of storage service' - typically ZF\OAuth2\Adapter\PdoAdapter
         * - 'db' => [ // database configuration for the above PdoAdapter
         *       'dsn'      => 'PDO DSN',
         *       'username' => 'username',
         *       'password' => 'password'
         *   ]
         * - 'storage_settings' => [ // configuration to pass to the storage adapter
         *       // see https://github.com/bshaffer/oauth2-server-php/blob/develop/src/OAuth2/Storage/Pdo.php#L57-L66
         *   ]
         */
        'grant_types' => [
            'client_credentials' => true,
            'authorization_code' => true,
            'password'           => true,
            'refresh_token'      => true,
            'jwt'                => true,
        ],
        /*
         * Error reporting style
         *
         * If true, client errors are returned using the
         * application/problem+json content type,
         * otherwise in the format described in the oauth2 specification
         * (default: true)
         */
        'api_problem_error_response' => true,
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'ZF\OAuth2\Controller\Auth' => [
                'ZF\ContentNegotiation\JsonModel' => [
                    'application/json',
                    'application/*+json',
                ],
                'Zend\View\Model\ViewModel' => [
                    'text/html',
                    'application/xhtml+xml',
                ],
            ],
        ],
    ],
];
