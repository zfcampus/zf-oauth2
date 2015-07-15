<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\OAuth2\Adapter\Pdo;

use ReflectionProperty;

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
    }

    public function provideStorage()
    {
        $this->setUp();

        $serviceManager = $this->getApplication()->getServiceManager();
        $pdo = $serviceManager->get('ZF\OAuth2\Adapter\PdoAdapter');

        $r = new ReflectionProperty($pdo, 'db');
        $r->setAccessible(true);
        $db = $r->getValue($pdo);

        $sql = file_get_contents(__DIR__ . '/../../TestAsset/database/pdo.sql');
        $db->exec($sql);

        return [[$pdo]];
    }
}
