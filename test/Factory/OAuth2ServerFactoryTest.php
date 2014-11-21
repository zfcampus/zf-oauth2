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
        $this->factory->createService($this->services);
    }

    public function testServiceCreatedWithDefaults()
    {
        $adapter = $this->getMockBuilder('OAuth2\Storage\Pdo')->disableOriginalConstructor()->getMock();

        $this->services->setService('TestAdapter', $adapter);
        $this->services->setService('Config', array(
            'zf-oauth2' => array(
                'storage' => 'TestAdapter'
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
        $expectedService->addGrantType(new ClientCredentials($adapter));
        $expectedService->addGrantType(new AuthorizationCode($adapter));
        $expectedService->addGrantType(new UserCredentials($adapter));
        $expectedService->addGrantType(new RefreshToken($adapter));

        $service = $this->factory->createService($this->services);
        $this->assertInstanceOf('OAuth2\Server', $service);
        $this->assertEquals($expectedService, $service);
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

        $service = $this->factory->createService($this->services);
        $this->assertInstanceOf('OAuth2\Server', $service);
        $this->assertEquals($expectedService, $service);
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

        $service = $this->factory->createService($this->services);
        $this->assertInstanceOf('OAuth2\Server', $service);
        $this->assertEquals($expectedService, $service);
    }

    protected function setUp()
    {
        $this->factory = new OAuth2ServerFactory();

        $this->services = $services = new ServiceManager();
    }
}
