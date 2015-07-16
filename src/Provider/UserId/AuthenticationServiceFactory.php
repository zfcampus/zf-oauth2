<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Provider\UserId;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $services
     * @return AuthenticationService
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config = $services->get('Config');

        if ($services->has('Zend\Authentication\AuthenticationService')) {
            return new AuthenticationService(
                $services->get('Zend\Authentication\AuthenticationService'),
                $config
            );
        }

        return new AuthenticationService(null, $config);
    }
}
