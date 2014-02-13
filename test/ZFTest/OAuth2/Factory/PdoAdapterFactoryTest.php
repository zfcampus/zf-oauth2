<?php
/**
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

namespace ZFTest\OAuth2\Factory;

use Zend\ServiceManager\ServiceManager;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use ZF\OAuth2\Factory\PdoAdapterFactory;

class PdoAdapterFactoryTest extends AbstractHttpControllerTestCase
{
    /**
     * @var PdoAdapterFactory
     */
    protected $factory;

    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @expectedException \ZF\OAuth2\Controller\Exception\RuntimeException
     */
    public function testExceptionThrownWhenMissingDbCredentials()
    {
        $this->services->setService('Configuration', array());
        $adapter = $this->factory->createService($this->services);

        $this->assertInstanceOf('ZF\OAuth2\Adapter\PdoAdapter', $adapter);
    }

    public function testInstanceCreated()
    {
        $this->services->setService('Configuration', array(
            'zf-oauth2' => array(
                'db' => array(
                    'username' => 'foo',
                    'password' => 'bar',
                    'dsn'      => 'sqlite::memory:'
                )
            )
        ));
        $adapter = $this->factory->createService($this->services);
        $this->assertInstanceOf('ZF\OAuth2\Adapter\PdoAdapter', $adapter);
    }

    protected function setUp()
    {
        $this->factory  = new PdoAdapterFactory();
        $this->services = $services = new ServiceManager();
    }
}
