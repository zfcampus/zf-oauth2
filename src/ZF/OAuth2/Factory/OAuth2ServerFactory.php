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

class OAuth2ServerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $services
     * @return OAuth2\Server
     * @throws \ZF\OAuth2\Controller\Exception\RuntimeException
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config   = $services->get('Configuration');

        if (!isset($config['zf-oauth2']['storage']) || empty($config['zf-oauth2']['storage'])) {
            throw new Exception\RuntimeException(
                'The storage configuration [\'zf-oauth2\'][\'storage\'] for OAuth2 is missing'
            );
        }

        $storage = $services->get($config['zf-oauth2']['storage']);

        $enforceState   = isset($config['zf-oauth2']['enforce_state'])  ? $config['zf-oauth2']['enforce_state']  : true;
        $allowImplicit  = isset($config['zf-oauth2']['allow_implicit']) ? $config['zf-oauth2']['allow_implicit'] : false;
        $accessLifetime = isset($config['zf-oauth2']['access_lifetime']) ? $config['zf-oauth2']['access_lifetime'] : 3600;

        // Pass a storage object or array of storage objects to the OAuth2 server class
        $server = new OAuth2Server($storage, array('enforce_state' => $enforceState, 'allow_implicit' => $allowImplicit, 'access_lifetime' => $accessLifetime));

        // Add the "Client Credentials" grant type (it is the simplest of the grant types)
        $server->addGrantType(new ClientCredentials($storage));

        // Add the "Authorization Code" grant type (this is where the oauth magic happens)
        $server->addGrantType(new AuthorizationCode($storage));

        // Add the "User Credentials" grant type
        $server->addGrantType(new UserCredentials($storage));

        // Add the "Refresh Token" grant type
        $server->addGrantType(new RefreshToken($storage));

        return $server;
    }
}
