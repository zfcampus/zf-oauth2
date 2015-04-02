<?php

namespace ZFTest\OAuth2\Adapter\Pdo;

use Doctrine\ORM\Tools\SchemaTool;

abstract class BaseTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{
    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../TestAsset/pdo.application.config.php'
        );

        parent::setUp();

        $serviceManager = $this->getApplication()->getServiceManager();
        $serviceManager->setAllowOverride(true);

        copy(
            __DIR__ . '/../../TestAsset/database/pdo.db',
            sys_get_temp_dir() . '/pdo-test.db'
        );
    }

    public function provideStorage()
    {
        $this->setUp();

        $serviceManager = $this->getApplication()->getServiceManager();
        $pdo = $serviceManager->get('ZF\OAuth2\Adapter\PdoAdapter');

        return array(array($pdo));
    }
}
