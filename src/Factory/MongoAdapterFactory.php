<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Factory;

use Interop\Container\ContainerInterface;
use MongoClient;
use ZF\OAuth2\Adapter\MongoAdapter;
use ZF\OAuth2\Controller\Exception;

/**
 * @author Chuck "MANCHUCK" Reeves <chuck@manchuck.com>
 */
class MongoAdapterFactory
{
    /**
     * @param  ContainerInterface $container
     * @return MongoAdapter
     */
    public function __invoke(ContainerInterface $container)
    {
        $config  = $container->get('config');
        return new MongoAdapter(
            $this->getMongoDb($container, $config),
            $this->getOauth2ServerConfig($config)
        );
    }

    /**
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $container
     * @return MongoAdapter
     */
    public function createService($container)
    {
        return $this($container);
    }

    /**
     * Get the mongo database
     *
     * @param ContainerInterface $container
     * @param array|\ArrayAccess $config
     * @return \MongoDB
     */
    protected function getMongoDb(ContainerInterface $container, $config)
    {
        $dbLocatorName = isset($config['zf-oauth2']['mongo']['locator_name'])
            ? $config['zf-oauth2']['mongo']['locator_name']
            : 'MongoDB';

        if ($container->has($dbLocatorName)) {
            return $container->get($dbLocatorName);
        }

        if (! isset($config['zf-oauth2']['mongo'])
            || empty($config['zf-oauth2']['mongo']['database'])
        ) {
            throw new Exception\RuntimeException(
                'The database configuration [\'zf-oauth2\'][\'mongo\'] for OAuth2 is missing'
            );
        }

        $options = isset($config['zf-oauth2']['mongo']['options'])
            ? $config['zf-oauth2']['mongo']['options']
            : [];
        $options['connect'] = false;
        $server = isset($config['zf-oauth2']['mongo']['dsn'])
            ? $config['zf-oauth2']['mongo']['dsn']
            : null;
        $mongo = new MongoClient($server, $options);

        return $mongo->{$config['zf-oauth2']['mongo']['database']};
    }

    /**
     * Retrieve oauth2-server-php configuration
     *
     * @param array|\ArrayAccess $config
     * @return array
     */
    protected function getOauth2ServerConfig($config)
    {
        if (isset($config['zf-oauth2']['storage_settings'])
            && is_array($config['zf-oauth2']['storage_settings'])
        ) {
            return $config['zf-oauth2']['storage_settings'];
        }

        return [];
    }
}
