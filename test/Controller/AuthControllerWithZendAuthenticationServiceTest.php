<?php
/**
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

namespace ZFTest\OAuth2\Controller;

use Mockery as M;
use Mockery\Loader;
use PDO;
use ReflectionProperty;
use Zend\Stdlib\Parameters;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AuthControllerWithZendAuthenticationServiceTest extends AbstractHttpControllerTestCase
{
    protected $loader;
    protected $db;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../TestAsset/zend.authenticationservice.application.config.php'
        );

        $this->loader = new Loader;
        $this->loader->register();

        parent::setUp();
        $this->setupDb();
    }

    public function setupDb()
    {
        $pdo = $this->getApplication()->getServiceManager()->get('ZF\OAuth2\Adapter\PdoAdapter');
        $r = new ReflectionProperty($pdo, 'db');
        $r->setAccessible(true);
        $db = $r->getValue($pdo);

        $sql = file_get_contents(__DIR__ . '/../TestAsset/database/db_oauth2.sql');
        $db->exec($sql);
        $this->db = $db;
    }

    public function getDb()
    {
        return $this->db;
    }

    public function getAuthenticationService()
    {
        $storage = M::mock('Zend\Authentication\Storage\StorageInterface');
        $storage->shouldReceive('isEmpty')->once()->andReturn(false);
        $storage->shouldReceive('read')->once()->andReturn(123);

        $authentication = $this->getApplication()->
            getServiceManager()->get('Zend\Authentication\AuthenticationService');

        $authentication->setStorage($storage);

        return $authentication;
    }

    public function testAuthorizeCode()
    {
        $request = $this->getRequest();
        $request->setQuery(new Parameters([
            'response_type' => 'code',
            'client_id'     => 'testclient',
            'state'         => 'xyz',
            'redirect_uri'  => '/oauth/receivecode',
        ]));
        $request->setPost(new Parameters([
            'authorized' => 'yes',
        ]));
        $request->setMethod('POST');

        $this->getAuthenticationService();

        $this->dispatch('/oauth/authorize');
        $this->assertTrue($this->getResponse()->isRedirect(), var_export($this->getResponse(), 1));
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('authorize');

        $location = $this->getResponse()->getHeaders()->get('Location')->getUri();
        if (preg_match('#code=([0-9a-f]+)#', $location, $matches)) {
            $code = $matches[1];
        }

        // test data in database is correct
        $query = sprintf(
            'SELECT * FROM oauth_authorization_codes WHERE authorization_code = \'%s\'',
            $code
        );
        $row = $this->getDb()
            ->query($query)
            ->fetch();

        $this->assertEquals(null, $row['user_id']);

        // test get token from authorized code
        $request = $this->getRequest();
        $request->getPost()->set('grant_type', 'authorization_code');
        $request->getPost()->set('code', $code);
        $request->getPost()->set('redirect_uri', '/oauth/receivecode');
        $request->getServer()->set('PHP_AUTH_USER', 'testclient');
        $request->getServer()->set('PHP_AUTH_PW', 'testpass');

        $this->dispatch('/oauth');
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertNotEmpty($response['access_token']);
    }
}
