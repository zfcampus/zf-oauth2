<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */
namespace ZF\OAuth2\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\OAuth2\Controller\Exception;
use OAuth2\Server as OAuth2Server;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\RefreshToken;
use OAuth2\GrantType\UserCredentials;
use OAuth2\GrantType\JwtBearer;

class OAuth2ServerFactory implements FactoryInterface
{

    /**
     * @param ServiceLocatorInterface $services
     * @return OAuth2\Server
     * @throws Exception\RuntimeException
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config   = $services->get('Config');

        if (!isset($config['zf-oauth2']['storage']) || empty($config['zf-oauth2']['storage'])) {
            throw new Exception\RuntimeException(
                'The storage configuration [\'zf-oauth2\'][\'storage\'] for OAuth2 is missing'
            );
        }

        $storagesServices = array();
        if (is_string($config['zf-oauth2']['storage'])) {
            $storagesServices[] = $config['zf-oauth2']['storage'];
        } elseif (is_array($config['zf-oauth2']['storage'])) {
            $storagesServices = $config['zf-oauth2']['storage'];
        } else {
            throw new Exception\RuntimeException(
                'The storage configuration [\'zf-oauth2\'][\'storage\'] for OAuth2 should be string or array'
            );
        }

        $storage = array();


        foreach ($storagesServices as $storageKey => $storagesService) {
            $storage[$storageKey] = $services->get($storagesService);
        }

        $enforceState   = isset($config['zf-oauth2']['enforce_state'])
            ? $config['zf-oauth2']['enforce_state']
            : true;
        $allowImplicit  = isset($config['zf-oauth2']['allow_implicit'])
            ? $config['zf-oauth2']['allow_implicit']
            : false;
        $accessLifetime = isset($config['zf-oauth2']['access_lifetime'])
            ? $config['zf-oauth2']['access_lifetime']
            : 3600;
        $audience = isset($config['zf-oauth2']['audience'])
            ? $config['zf-oauth2']['audience']
            : '';
        $options        = isset($config['zf-oauth2']['options'])
            ? $config['zf-oauth2']['options']
            : array();
        $options        = array_merge(array(
            'enforce_state'   => $enforceState,
            'allow_implicit'  => $allowImplicit,
            'access_lifetime' => $accessLifetime
        ), $options);

        // Pass a storage object or array of storage objects to the OAuth2 server class
        $server = new OAuth2Server($storage, $options);
        $availableGrantTypes = $config['zf-oauth2']['grant_types'];

        if (isset($availableGrantTypes['client_credentials']) && $availableGrantTypes['client_credentials'] === true) {
            $clientOptions = array();
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
            $refreshOptions = array();
            if (isset($options['always_issue_new_refresh_token'])) {
                $refreshOptions['always_issue_new_refresh_token'] = $options['always_issue_new_refresh_token'];
            }
            if (isset($options['refresh_token_lifetime'])) {
                $refreshOptions['refresh_token_lifetime'] = $options['refresh_token_lifetime'];
            }

            // Add the "Refresh Token" grant type
            $server->addGrantType(new RefreshToken($server->getStorage('refresh_token'), $refreshOptions));
        }

        return $server;
    }
}
