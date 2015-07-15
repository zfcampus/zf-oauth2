<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */
namespace ZF\OAuth2\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OAuth2ServerFactory implements FactoryInterface
{

    /**
     * @param ServiceLocatorInterface $services
     * @return OAuth2\Server
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config = $services->get('Config');
        $config = isset($config['zf-oauth2']) ? $config['zf-oauth2'] : [];
        return new OAuth2ServerInstanceFactory($config, $services);
    }
}
