<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2015-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Provider\UserId;

use Interop\Container\ContainerInterface;

class AuthenticationServiceFactory
{
    /**
     * @param  ContainerInterface $container
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if ($container->has('Zend\Authentication\AuthenticationService')) {
            return new AuthenticationService(
                $container->get('Zend\Authentication\AuthenticationService'),
                $config
            );
        }

        return new AuthenticationService(null, $config);
    }

    /**
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $container
     * @return AuthenticationService
     */
    public function createService($container)
    {
        return $this($container);
    }
}
