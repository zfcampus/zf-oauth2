<?php

namespace ZFTest\OAuth2\Doctrine;

use Doctrine\ORM\Tools\SchemaTool;

abstract class BaseTest extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{
    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../TestAsset/doctrine.application.config.php'
        );

        parent::setUp();

        $serviceManager = $this->getApplication()->getServiceManager();
        $serviceManager->setAllowOverride(true);

        copy(
            __DIR__ . '/../TestAsset/data/doctrine-original.db',
            __DIR__ . '/../TestAsset/data/doctrine.db'
        );
    }

    public function provideStorage()
    {
        $this->setUp();

        $serviceManager = $this->getApplication()->getServiceManager();
        $doctrine = $serviceManager->get('ZF\OAuth2\Adapter\DoctrineAdapter');

        return array(array($doctrine));
    }
}
