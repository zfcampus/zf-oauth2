<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Factory;

use OAuth2\Server as OAuth2Server;
use Zend\ServiceManager\FactoryInterface;
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
        $services = $controllers->getServiceLocator();

        // For BC, if the ZF\OAuth2\Service\OAuth2Server service returns an
        // OAuth2\Server instance, wrap it in a closure.
        $oauth2ServerFactory = $services->get('ZF\OAuth2\Service\OAuth2Server');
        if ($oauth2ServerFactory instanceof OAuth2Server) {
            $oauth2Server = $oauth2ServerFactory;
            $oauth2ServerFactory = function () use ($oauth2Server) {
                return $oauth2Server;
            };
        }

        $authController = new AuthController(
            $oauth2ServerFactory,
            $services->get('ZF\OAuth2\Provider\UserId')
        );

        $config = $services->get('Config');
        $authController->setApiProblemErrorResponse((isset($config['zf-oauth2']['api_problem_error_response'])
            && $config['zf-oauth2']['api_problem_error_response'] === true));

        return $authController;
    }
}
