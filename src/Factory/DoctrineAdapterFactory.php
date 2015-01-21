<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\OAuth2\Controller\Exception;
use ZF\OAuth2\Adapter\DoctrineAdapter;

class DoctrineAdapterFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $services
     * @throws \ZF\OAuth2\Controller\Exception\RuntimeException
     * @return \ZF\OAuth2\Adapter\PdoAdapter
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config = $services->get('Config');

        $oauth2ServerConfig = array();
        if (isset($config['zf-oauth2']['storage_settings']) && is_array($config['zf-oauth2']['storage_settings'])) {
            $oauth2ServerConfig = $config['zf-oauth2']['storage_settings'];
        }

        $adapter = new DoctrineAdapter;
        $adapter->setConfig($oauth2ServerConfig);

        if (isset($config['zf-oauth2']['storage_settings']['bcrypt_cost'])) {
            $adapter->setBcryptCost($config['zf-oauth2']['storage_settings']['bcrypt_cost']);
        }

        $adapter->setObjectManager($this->loadObjectManager($services, $config['zf-oauth2']['storage_settings']));
        $adapter->setServiceLocator($services);

        return $adapter;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param                         $config
     *
     * @return ObjectManager
     * @throws \Zend\ServiceManager\Exception\ServiceNotCreatedException
     */
    protected function loadObjectManager(ServiceLocatorInterface $serviceLocator, $config)
    {
        if ($serviceLocator->has($config['object_manager'])) {
            $objectManager = $serviceLocator->get($config['object_manager']);
        } else {
            // @codeCoverageIgnoreStart
            throw new ServiceNotCreatedException('The object_manager could not be found.');
        }
        // @codeCoverageIgnoreEnd
        return $objectManager;
    }
}
