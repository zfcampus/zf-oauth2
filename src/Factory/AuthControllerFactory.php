<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Factory;

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
        $services = $controllers->getServiceLocator()->get('ServiceManager');
        $authController = new AuthController($services->get('ZF\OAuth2\Service\OAuth2Server'));

        $config  = $services->get('Config');
        $apiProblemErrorResponse = isset($config['zf-oauth2']['api_problem_error_response'])
            && $config['zf-oauth2']['api_problem_error_response'] === true;

        $authController->setApiProblemErrorResponse($apiProblemErrorResponse);

        return $authController;
    }
}
