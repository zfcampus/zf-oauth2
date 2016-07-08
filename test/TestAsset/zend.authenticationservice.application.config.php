<?php
$modules = [
    'ZF\ContentNegotiation',
    'ZF\OAuth2',
];

if (class_exists('Zend\Router\Module')) {
    $modules[] = 'Zend\Router';
}

return [
    // This should be an array of module namespaces used in the application.
    'modules' => $modules,

    // These are various options for the listeners attached to the ModuleManager
    'module_listener_options' => [
        // This should be an array of paths in which modules reside.
        // If a string key is provided, the listener will consider that a module
        // namespace, the value of that key the specific path to that module's
        // Module class.
        'module_paths' => [
            __DIR__ . '/../../../../..',
            __DIR__ . '/../../../../../vendor',
        ],

        // An array of paths from which to glob configuration files after
        // modules are loaded. These effectively override configuration
        // provided by modules themselves. Paths may use GLOB_BRACE notation.
        'config_glob_paths' => [
            __DIR__ . '/autoload_zend_authenticationservice/{,*.}{global,local}.php',
        ],
    ],
];
