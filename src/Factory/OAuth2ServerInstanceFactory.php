<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */
namespace ZF\OAuth2\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\OAuth2\Controller\Exception;
use OAuth2\Server as OAuth2Server;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\RefreshToken;
use OAuth2\GrantType\UserCredentials;
use OAuth2\GrantType\JwtBearer;

class OAuth2ServerInstanceFactory
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var ServiceLocatorInterface
     */
    private $services;

    /**
     * @var OAuth2Server
     */
    private $server;

    /**
     * @var array $config Configuration to use when creating the instance.
     * @var ContainerInterface $services ServiceLocator for retrieving storage adapters.
     */
    public function __construct(array $config, ContainerInterface $services)
    {
        $this->config   = $config;
        $this->services = $services;
    }

    /**
     * Create an OAuth2\Server instance.
     *
     * @return OAuth2Server
     * @throws Exception\RuntimeException
     */
    public function __invoke()
    {
        if ($this->server) {
            return $this->server;
        }

        $config = $this->config;

        if (!isset($config['storage']) || empty($config['storage'])) {
            throw new Exception\RuntimeException(
                'The storage configuration for OAuth2 is missing'
            );
        }

        $storagesServices = [];
        if (is_string($config['storage'])) {
            $storagesServices[] = $config['storage'];
        } elseif (is_array($config['storage'])) {
            $storagesServices = $config['storage'];
        } else {
            throw new Exception\RuntimeException(
                'The storage configuration for OAuth2 should be string or array'
            );
        }

        $storage = [];

        foreach ($storagesServices as $storageKey => $storagesService) {
            $storage[$storageKey] = $this->services->get($storagesService);
        }

        $enforceState   = isset($config['enforce_state'])
            ? $config['enforce_state']
            : true;
        $allowImplicit  = isset($config['allow_implicit'])
            ? $config['allow_implicit']
            : false;
        $accessLifetime = isset($config['access_lifetime'])
            ? $config['access_lifetime']
            : 3600;
        $audience = isset($config['audience'])
            ? $config['audience']
            : '';
        $options        = isset($config['options'])
            ? $config['options']
            : [];
        $options        = array_merge([
            'enforce_state'   => $enforceState,
            'allow_implicit'  => $allowImplicit,
            'access_lifetime' => $accessLifetime
        ], $options);

        // Pass a storage object or array of storage objects to the OAuth2 server class
        $server = new OAuth2Server($storage, $options);
        $availableGrantTypes = $config['grant_types'];

        if (isset($availableGrantTypes['client_credentials']) && $availableGrantTypes['client_credentials'] === true) {
            $clientOptions = [];
            if (isset($options['allow_credentials_in_request_body'])) {
                $clientOptions['allow_credentials_in_request_body'] = $options['allow_credentials_in_request_body'];
            }

            // Add the "Client Credentials" grant type (it is the simplest of the grant types)
            $server->addGrantType(new ClientCredentials($server->getStorage('client_credentials'), $clientOptions));
        }

        if (isset($availableGrantTypes['authorization_code']) && $availableGrantTypes['authorization_code'] === true) {
            // Add the "Authorization Code" grant type (this is where the oauth magic happens)
            $server->addGrantType(new AuthorizationCode($server->getStorage('authorization_code')));
        }

        if (isset($availableGrantTypes['password']) && $availableGrantTypes['password'] === true) {
            // Add the "User Credentials" grant type
            $server->addGrantType(new UserCredentials($server->getStorage('user_credentials')));
        }

        if (isset($availableGrantTypes['jwt']) && $availableGrantTypes['jwt'] === true) {
            // Add the "JWT Bearer" grant type
            $server->addGrantType(new JwtBearer($server->getStorage('jwt_bearer'), $audience));
        }

        if (isset($availableGrantTypes['refresh_token']) && $availableGrantTypes['refresh_token'] === true) {
            $refreshOptions = [];
            if (isset($options['always_issue_new_refresh_token'])) {
                $refreshOptions['always_issue_new_refresh_token'] = $options['always_issue_new_refresh_token'];
            }
            if (isset($options['unset_refresh_token_after_use'])) {
                $refreshOptions['unset_refresh_token_after_use'] = $options['unset_refresh_token_after_use'];
            }

            // Add the "Refresh Token" grant type
            $server->addGrantType(new RefreshToken($server->getStorage('refresh_token'), $refreshOptions));
        }

        return $this->server = $server;
    }
}
