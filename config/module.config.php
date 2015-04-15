<?php
return array(
    'controllers' => array(
        'factories' => array(
            'ZF\OAuth2\Controller\Auth' => 'ZF\OAuth2\Factory\AuthControllerFactory',
        ),
    ),
    'router' => array(
        'routes' => array(
            'oauth' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/oauth',
                    'defaults' => array(
                        'controller' => 'ZF\OAuth2\Controller\Auth',
                        'action'     => 'token',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'authorize' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route' => '/authorize',
                            'defaults' => array(
                                'action' => 'authorize',
                            ),
                        ),
                    ),
                    'resource' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route' => '/resource',
                            'defaults' => array(
                                'action' => 'resource',
                            ),
                        ),
                    ),
                    'code' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route' => '/receivecode',
                            'defaults' => array(
                                'action' => 'receiveCode',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'ZF\OAuth2\Adapter\PdoAdapter'   => 'ZF\OAuth2\Factory\PdoAdapterFactory',
            'ZF\OAuth2\Adapter\IbmDb2Adapter'   => 'ZF\OAuth2\Factory\IbmDb2AdapterFactory',
            'ZF\OAuth2\Adapter\MongoAdapter' => 'ZF\OAuth2\Factory\MongoAdapterFactory',
            'ZF\OAuth2\Service\OAuth2Server' => 'ZF\OAuth2\Factory\OAuth2ServerFactory'
        )
    ),
    'view_manager' => array(
        'template_map' => array(
            'oauth/authorize'    => __DIR__ . '/../view/zf/auth/authorize.phtml',
            'oauth/receive-code' => __DIR__ . '/../view/zf/auth/receive-code.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'zf-oauth2' => array(
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
    ),
    'zf-content-negotiation' => array(
        'ZF\OAuth2\Controller\Auth' => array(
            'ZF\ContentNegotiation\JsonModel' => array(
                'application/json',
                'application/*+json',
            ),
            'Zend\View\Model\ViewModel' => array(
                'text/html',
                'application/xhtml+xml',
            ),
        ),
    ),
);
