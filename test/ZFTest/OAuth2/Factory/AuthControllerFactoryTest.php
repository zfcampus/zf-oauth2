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

    /**
     * @expectedException \ZF\OAuth2\Controller\Exception\RuntimeException
     */
    public function testExceptionThrownOnMissingStorageClass()
    {
        $this->services->setService('Configuration', array());
        $this->factory->createService($this->controllers);
    }

    public function testControllerCreated()
    {
        $adapter = $this->getMockBuilder('OAuth2\Storage\Pdo')->disableOriginalConstructor()->getMock();

        $this->services->setService('TestAdapter', $adapter);
        $this->services->setService('Configuration', array(
            'zf-oauth2' => array(
                'storage' => 'TestAdapter'
            )
        ));
        $controller = $this->factory->createService($this->controllers);
        $this->assertInstanceOf('ZF\OAuth2\Controller\AuthController', $controller);
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
