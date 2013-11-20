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
            'ZF\OAuth2\Adapter\PdoAdapter' => 'ZF\OAuth2\Factory\PdoAdapterFactory'
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'zf-oauth2' => array(
        'storage' => 'ZF\OAuth2\Adapter\PdoAdapter'
    ),
);
