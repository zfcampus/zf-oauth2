<?php
/**
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */
namespace ZFTest\OAuth2\Factory;

use Zend\ServiceManager\ServiceManager;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use ZF\OAuth2\Factory\OAuth2ServerFactory;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\RefreshToken;
use OAuth2\GrantType\UserCredentials;
use OAuth2\GrantType\JwtBearer;

class OAuth2ServerFactoryTest extends AbstractHttpControllerTestCase
{
    /**
     * @var OAuth2ServerFactory
     */
    protected $factory;

    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @expectedException \ZF\OAuth2\Controller\Exception\RuntimeException
     */
    public function testExceptionThrownOnMissingStorageClass()
    {
        $this->services->setService('Config', array());
        $factory = $this->factory->createService($this->services);
        $factory();
    }

    public function testServiceCreatedWithDefaults()
    {
        $adapter = $this->getMockBuilder('OAuth2\Storage\Pdo')->disableOriginalConstructor()->getMock();
        $this->services->setService('TestAdapter', $adapter);
        $this->services->setService('Config', array(
            'zf-oauth2' => array(
                'storage' => 'TestAdapter',
                'grant_types' => array(
                    'client_credentials' => true,
                    'authorization_code' => true,
                    'password'           => true,
                    'refresh_token'      => true,
                    'jwt'                => true,
                ),
            ),
        ));

        $expectedService = new \OAuth2\Server(
            $adapter,
            array(
                'enforce_state'   => true,
                'allow_implicit'  => false,
                'access_lifetime' => 3600
            )
        );

        $expectedService->addGrantType(new ClientCredentials($adapter));
        $expectedService->addGrantType(new AuthorizationCode($adapter));
        $expectedService->addGrantType(new UserCredentials($adapter));
        $expectedService->addGrantType(new RefreshToken($adapter));
        $expectedService->addGrantType(new JwtBearer($adapter, ''));

        $service = $this->factory->createService($this->services);
        $this->assertInstanceOf('ZF\OAuth2\Factory\OAuth2ServerInstanceFactory', $service);
        $server = $service();
        $this->assertInstanceOf('OAuth2\Server', $server);
        $this->assertEquals($expectedService, $server);
    }

    public function testServiceCreatedWithOverriddenValues()
    {
        $adapter = $this->getMockBuilder('OAuth2\Storage\Pdo')->disableOriginalConstructor()->getMock();
        $this->services->setService('TestAdapter', $adapter);
        $this->services->setService('Config', array(
            'zf-oauth2' => array(
                'storage'        => 'TestAdapter',
                'enforce_state'  => false,
                'allow_implicit' => true,
                'access_lifetime' => 12000,
                'grant_types' => array(
                    'client_credentials' => true,
                    'authorization_code' => true,
                    'password'           => true,
                    'refresh_token'      => true,
                    'jwt'                => true,
                ),
            ),
        ));

        $expectedService = new \OAuth2\Server(
            $adapter,
            array(
                'enforce_state'   => false,
                'allow_implicit'  => true,
                'access_lifetime' => 12000
            )
        );

        $expectedService->addGrantType(new ClientCredentials($adapter));
        $expectedService->addGrantType(new AuthorizationCode($adapter));
        $expectedService->addGrantType(new UserCredentials($adapter));
        $expectedService->addGrantType(new RefreshToken($adapter));
        $expectedService->addGrantType(new JwtBearer($adapter, ''));

        $service = $this->factory->createService($this->services);
        $this->assertInstanceOf('ZF\OAuth2\Factory\OAuth2ServerInstanceFactory', $service);
        $server = $service();
        $this->assertInstanceOf('OAuth2\Server', $server);
        $this->assertEquals($expectedService, $server);
    }

    public function testServiceCreatedWithOverriddenValuesInOptionsSubArray()
    {
        $adapter = $this->getMockBuilder('OAuth2\Storage\Pdo')->disableOriginalConstructor()->getMock();

        $this->services->setService('TestAdapter', $adapter);
        $this->services->setService('Config', array(
            'zf-oauth2' => array(
                'storage' => 'TestAdapter',
                'options' => array(
                    'enforce_state'   => false,
                    'allow_implicit'  => true,
                    'access_lifetime' => 12000,
                ),
                'grant_types' => array(
                    'client_credentials' => true,
                    'authorization_code' => true,
                    'password'           => true,
                    'refresh_token'      => true,
                    'jwt'                => true,
                ),
            )
        ));

        $expectedService = new \OAuth2\Server(
            $adapter,
            array(
                'enforce_state'   => false,
                'allow_implicit'  => true,
                'access_lifetime' => 12000
            )
        );

        $expectedService->addGrantType(new ClientCredentials($adapter));
        $expectedService->addGrantType(new AuthorizationCode($adapter));
        $expectedService->addGrantType(new UserCredentials($adapter));
        $expectedService->addGrantType(new RefreshToken($adapter));
        $expectedService->addGrantType(new JwtBearer($adapter, ''));

        $service = $this->factory->createService($this->services);
        $this->assertInstanceOf('ZF\OAuth2\Factory\OAuth2ServerInstanceFactory', $service);
        $server = $service();
        $this->assertInstanceOf('OAuth2\Server', $server);
        $this->assertEquals($expectedService, $server);
    }

    public function testServiceCreatedWithStoragesAsArray()
    {
        $storage = array(
            'access_token'       => $this->getMockForAbstractClass('OAuth2\Storage\AccessTokenInterface'),
            'authorization_code' => $this->getMockForAbstractClass('OAuth2\Storage\AuthorizationCodeInterface'),
            'client_credentials' => $this->getMockForAbstractClass('OAuth2\Storage\ClientCredentialsInterface'),
            'client'             => $this->getMockForAbstractClass('OAuth2\Storage\ClientInterface'),
            'refresh_token'      => $this->getMockForAbstractClass('OAuth2\Storage\RefreshTokenInterface'),
            'user_credentials'   => $this->getMockForAbstractClass('OAuth2\Storage\UserCredentialsInterface'),
            'public_key'         => $this->getMockForAbstractClass('OAuth2\Storage\PublicKeyInterface'),
            'jwt_bearer'         => $this->getMockForAbstractClass('OAuth2\Storage\JWTBearerInterface'),
            'scope'              => $this->getMockForAbstractClass('OAuth2\Storage\ScopeInterface'),
        );

        $this->services->setService('OAuth2\Storage\AccessToken', $storage['access_token']);
        $this->services->setService('OAuth2\Storage\AuthorizationCode', $storage['authorization_code']);
        $this->services->setService('OAuth2\Storage\ClientCredentials', $storage['client_credentials']);
        $this->services->setService('OAuth2\Storage\Client', $storage['client']);
        $this->services->setService('OAuth2\Storage\RefreshToken', $storage['refresh_token']);
        $this->services->setService('OAuth2\Storage\UserCredentials', $storage['user_credentials']);
        $this->services->setService('OAuth2\Storage\PublicKey', $storage['public_key']);
        $this->services->setService('OAuth2\Storage\JWTBearer', $storage['jwt_bearer']);
        $this->services->setService('OAuth2\Storage\Scope', $storage['scope']);

        $this->services->setService('Config', array(
            'zf-oauth2' => array(
                'storage'        => array(
                    'access_token'       => 'OAuth2\Storage\AccessToken',
                    'authorization_code' => 'OAuth2\Storage\AuthorizationCode',
                    'client_credentials' => 'OAuth2\Storage\ClientCredentials',
                    'client'             => 'OAuth2\Storage\Client',
                    'refresh_token'      => 'OAuth2\Storage\RefreshToken',
                    'user_credentials'   => 'OAuth2\Storage\UserCredentials',
                    'public_key'         => 'OAuth2\Storage\PublicKey',
                    'jwt_bearer'         => 'OAuth2\Storage\JWTBearer',
                    'scope'              => 'OAuth2\Storage\Scope',
                ),
                'grant_types' => array(
                    'client_credentials' => true,
                    'authorization_code' => true,
                    'password'           => true,
                    'refresh_token'      => true,
                    'jwt'                => true,
                ),
            )
        ));

        $expectedService = new \OAuth2\Server(
            $storage,
            array(
                'enforce_state'   => true,
                'allow_implicit'  => false,
                'access_lifetime' => 3600
            )
        );

        $expectedService->addGrantType(new ClientCredentials($storage['client_credentials']));
        $expectedService->addGrantType(new AuthorizationCode($storage['authorization_code']));
        $expectedService->addGrantType(new UserCredentials($storage['user_credentials']));
        $expectedService->addGrantType(new RefreshToken($storage['refresh_token']));
        $expectedService->addGrantType(new JwtBearer($storage['jwt_bearer'], ''));

        $service = $this->factory->createService($this->services);
        $this->assertInstanceOf('ZF\OAuth2\Factory\OAuth2ServerInstanceFactory', $service);
        $server = $service();
        $this->assertInstanceOf('OAuth2\Server', $server);
        $this->assertEquals($expectedService, $server);
    }

    public function testServiceCreatedWithSelectedGrandTypes()
    {
        $adapter = $this->getMockBuilder('OAuth2\Storage\Pdo')->disableOriginalConstructor()->getMock();
        $this->services->setService('TestAdapter', $adapter);
        $this->services->setService('Config', array(
            'zf-oauth2' => array(
                'storage' => 'TestAdapter',
                'grant_types' => array(
                    'client_credentials' => false,
                    'password'           => true,
                    'refresh_token'      => true,
                ),
            )
        ));

        $expectedService = new \OAuth2\Server(
            $adapter,
            array(
                'enforce_state'   => true,
                'allow_implicit'  => false,
                'access_lifetime' => 3600
            )
        );

        $expectedService->addGrantType(new UserCredentials($adapter));
        $expectedService->addGrantType(new RefreshToken($adapter));
        $service = $this->factory->createService($this->services);
        $this->assertInstanceOf('ZF\OAuth2\Factory\OAuth2ServerInstanceFactory', $service);
        $server = $service();
        $this->assertInstanceOf('OAuth2\Server', $server);
        $this->assertEquals($expectedService, $server);
    }

    protected function setUp()
    {
        $this->factory = new OAuth2ServerFactory();
        $this->services = $services = new ServiceManager();
    }
}
