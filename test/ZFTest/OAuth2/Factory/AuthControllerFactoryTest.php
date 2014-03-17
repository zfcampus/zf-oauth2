<?php
/**
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

namespace ZFTest\OAuth2\Factory;

use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use ZF\OAuth2\Factory\AuthControllerFactory;

class AuthControllerFactoryTest extends AbstractHttpControllerTestCase
{
    /**
     * @var ControllerManager
     */
    protected $controllers;

    /**
     * @var AuthControllerFactory
     */
    protected $factory;

    /**
     * @var ServiceManager
     */
    protected $services;



    public function testControllerCreated()
    {
        $oauthServer = $this->getMockBuilder('OAuth2\Server')->disableOriginalConstructor()->getMock();

        $this->services->setService('ZF\OAuth2\Service\OAuth2Server', $oauthServer);

        $controller = $this->factory->createService($this->controllers);

        $this->assertInstanceOf('ZF\OAuth2\Controller\AuthController', $controller);
        $this->assertEquals(new \ZF\OAuth2\Controller\AuthController($oauthServer), $controller);
    }

    protected function setUp()
    {
        $this->factory = new AuthControllerFactory();

        $this->services = $services = new ServiceManager();

        $this->controllers = $controllers = new ControllerManager();
        $controllers->setServiceLocator(new ServiceManager());
        $controllers->getServiceLocator()->setService('ServiceManager', $services);
    }
}
