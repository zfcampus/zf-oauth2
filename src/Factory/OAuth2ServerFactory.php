<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */
namespace ZF\OAuth2\Factory;

use Interop\Container\ContainerInterface;

class OAuth2ServerFactory
{
    /**
     * @param  ContainerInterface $container
     * @return OAuth2ServerInstanceFactory
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $config = isset($config['zf-oauth2']) ? $config['zf-oauth2'] : [];
        return new OAuth2ServerInstanceFactory($config, $container);
    }

    /**
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $container
     * @return OAuth2ServerInstanceFactory
     */
    public function createService($container)
    {
        return $this($container);
    }
}
