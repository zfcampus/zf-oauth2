<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZF\OAuth2\Adapter\IbmDb2Adapter;
use ZF\OAuth2\Controller\Exception;

class IbmDb2AdapterFactory implements FactoryInterface
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
        $config = $container->get('Config');

        if (! isset($config['zf-oauth2']['db']) || empty($config['zf-oauth2']['db'])) {
            throw new Exception\RuntimeException(
                'The database configuration [\'zf-oauth2\'][\'db\'] for OAuth2 is missing'
            );
        }

        $username = isset($config['zf-oauth2']['db']['username'])
            ? $config['zf-oauth2']['db']['username']
            : null;
        $password = isset($config['zf-oauth2']['db']['password'])
            ? $config['zf-oauth2']['db']['password']
            : null;
        $driver_options  = isset($config['zf-oauth2']['db']['driver_options'])
            ? $config['zf-oauth2']['db']['driver_options']
            : [];

        $oauth2ServerConfig = [];
        if (isset($config['zf-oauth2']['storage_settings'])
            && is_array($config['zf-oauth2']['storage_settings'])
        ) {
            $oauth2ServerConfig = $config['zf-oauth2']['storage_settings'];
        }

        return new IbmDb2Adapter([
            'database'       => $config['zf-oauth2']['db']['database'],
            'username'       => $username,
            'password'       => $password,
            'driver_options' => $driver_options,
        ], $oauth2ServerConfig);
    }

}
