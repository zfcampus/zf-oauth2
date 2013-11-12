<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use OAuth2\Storage\Pdo as OAuth2Storage;
use OAuth2\Server as OAuth2Server;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\AuthorizationCode;

class AuthControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $s) 
    {
        $sm     = $s->getServiceLocator()->get('ServiceManager');
        $config = $sm->get('Configuration');

        if (!isset($config['oauth2']['db']) || empty($config['oauth2']['db'])) {
            throw new Exception\RuntimeException(
                'The database configuration [\'oauth2\'][\'db\'] for OAuth2 is missing'
            );
        }

        $storage = new OAuth2Storage(array(
            'dsn' => $config['oauth2']['db']['dsn'], 
            'username' => $config['oauth2']['db']['username'], 
            'password' => $config['oauth2']['db']['password']
        ));

        // Pass a storage object or array of storage objects to the OAuth2 server class
        $server = new OAuth2Server($storage);
        
        // Add the "Client Credentials" grant type (it is the simplest of the grant types)
        $server->addGrantType(new ClientCredentials($storage));
        
        // Add the "Authorization Code" grant type (this is where the oauth magic happens)
        $server->addGrantType(new AuthorizationCode($storage));

        return new AuthController($server);
    }

}
