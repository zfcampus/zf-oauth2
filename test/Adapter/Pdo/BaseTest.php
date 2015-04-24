<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\OAuth2\Adapter\Pdo;

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

        $this->testDbPath= getenv('TRAVIS')
            ? __DIR__ . '/../../TestAsset/database'
            : sys_get_temp_dir();

        copy(
            __DIR__ . '/../../TestAsset/database/pdo.db',
            $this->testDbPath . '/pdo-test.db'
        );
    }

    protected function tearDown()
    {
        $db = $this->testDbPath . '/pdo-test.db';
        if (file_exists($db)) {
            unlink($db);
        }
    }

    public function provideStorage()
    {
        $this->setUp();

        $serviceManager = $this->getApplication()->getServiceManager();
        $pdo = $serviceManager->get('ZF\OAuth2\Adapter\PdoAdapter');

        return array(array($pdo));
    }
}
