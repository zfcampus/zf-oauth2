<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use MongoClient;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\OAuth2\Adapter\MongoAdapter;
use ZF\OAuth2\Controller\Exception;

/**
 * Class MongoAdapterFactory
 *
 * @package ZF\OAuth2\Factory
 * @author Chuck "MANCHUCK" Reeves <chuck@manchuck.com>
 */
class MongoAdapterFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     *
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = NULL)
    {
        $config  = $container->get('Config');
        return new MongoAdapter($this->getMongoDb($container), $this->getOauth2ServerConfig($config));
    }


    /**
     * Get the mongo database
     *
     * @param ServiceLocatorInterface $services
     * @return \MongoDB
     */
    protected function getMongoDb($services)
    {
        $config  = $services->get('Config');
        $dbLocatorName = isset($config['zf-oauth2']['mongo']['locator_name'])
            ? $config['zf-oauth2']['mongo']['locator_name']
            : 'MongoDB';

        if ($services->has($dbLocatorName)) {
            return $services->get($dbLocatorName);
        }

        if (!isset($config['zf-oauth2']['mongo']) || empty($config['zf-oauth2']['mongo']['database'])) {
            throw new Exception\RuntimeException(
                'The database configuration [\'zf-oauth2\'][\'mongo\'] for OAuth2 is missing'
            );
        }

        $options = isset($config['zf-oauth2']['mongo']['options']) ? $config['zf-oauth2']['mongo']['options'] : [];
        $options['connect'] = false;
        $server  = isset($config['zf-oauth2']['mongo']['dsn']) ? $config['zf-oauth2']['mongo']['dsn'] : null;
        $mongo   = new MongoClient($server, $options);
        return $mongo->{$config['zf-oauth2']['mongo']['database']};
    }

    /**
     * Retrieve oauth2-server-php configuration
     *
     * @return array
     */
    protected function getOauth2ServerConfig($config)
    {
        $oauth2ServerConfig = [];
        if (isset($config['zf-oauth2']['storage_settings']) && is_array($config['zf-oauth2']['storage_settings'])) {
            $oauth2ServerConfig = $config['zf-oauth2']['storage_settings'];
        }

        return $oauth2ServerConfig;
    }
}
