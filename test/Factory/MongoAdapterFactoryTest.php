<?php
/**
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

namespace ZFTest\OAuth2\Factory;

use ReflectionObject;
use Zend\ServiceManager\ServiceManager;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use ZF\OAuth2\Factory\MongoAdapterFactory;

class MongoAdapterFactoryTest extends AbstractHttpControllerTestCase
{
    /**
     * @var MongoAdapterFactory
     */
    protected $factory;

    /**
     * @var ServiceManager
     */
    protected $services;

    protected function setUp()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('The Mongo extension is not available.');
        }

        $this->factory  = new MongoAdapterFactory();
        $this->services = $services = new ServiceManager();

        $this->setApplicationConfig([
            'modules' => [
                'ZF\OAuth2',
            ],
            'module_listener_options' => [
                'module_paths' => [__DIR__ . '/../../'],
                'config_glob_paths' => [],
            ],
            'service_listener_options' => [],
            'service_manager' => [],
        ]);
        parent::setUp();
    }

    /**
     * @expectedException \ZF\OAuth2\Controller\Exception\RuntimeException
     */
    public function testExceptionThrownWhenMissingMongoCredentials()
    {
        $this->services->setService('Config', []);
        $adapter = $this->factory->createService($this->services);

        $this->assertInstanceOf('ZF\OAuth2\Adapter\PdoAdapter', $adapter);
    }

    public function testInstanceCreated()
    {
        $this->services->setService('Config', [
            'zf-oauth2' => [
                'mongo' => [
                    'database' => 'test',
                    'dsn'      => 'mongodb://127.0.0.1:27017'
                ]
            ]
        ]);

        $adapter = $this->factory->createService($this->services);
        $this->assertInstanceOf('ZF\OAuth2\Adapter\MongoAdapter', $adapter);
    }

    public function testInstanceCreatedWithMongoDbInServiceLocator()
    {
        $this->services->setService('Config', [
            'zf-oauth2' => [
                'mongo' => [
                    'locator_name' => 'testdb',
                ],
            ],
        ]);
        $mock = $this->getMock('\MongoDB', [], [], '', false);
        $this->services->setService('testdb', $mock);

        $adapter = $this->factory->createService($this->services);
        $this->assertInstanceOf('ZF\OAuth2\Adapter\MongoAdapter', $adapter);
    }

    public function testCanPassAdapterConfigurationWhenCreatingInstance()
    {
        $this->services->setService('Config', [
            'zf-oauth2' => [
                'mongo' => [
                    'locator_name' => 'testdb',
                ],
                'storage_settings' => [
                    'user_table' => 'my_users',
                ],
            ],
        ]);
        $mock = $this->getMock('\MongoDB', [], [], '', false);
        $this->services->setService('testdb', $mock);

        $adapter = $this->factory->createService($this->services);
        $this->assertInstanceOf('ZF\OAuth2\Adapter\MongoAdapter', $adapter);

        $r = new ReflectionObject($adapter);
        $c = $r->getProperty('config');
        $c->setAccessible(true);
        $config = $c->getValue($adapter);
        $this->assertEquals('my_users', $config['user_table']);
    }
}
