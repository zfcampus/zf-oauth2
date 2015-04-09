<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\OAuth2\Controller\AuthController;

class AuthControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllers
     * @return AuthController
     */
    public function createService(ServiceLocatorInterface $controllers)
    {
        $services = $controllers instanceof ServiceLocatorAwareInterface
            ? $controllers->getServiceLocator()
            : $controllers;
        return new AuthController($services->get('ZF\OAuth2\Service\OAuth2Server'));
    }
}
