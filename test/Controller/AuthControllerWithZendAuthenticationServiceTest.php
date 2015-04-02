<?php
/**
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

namespace ZFTest\OAuth2\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Mockery\Loader;
use Mockery as M;

class AuthControllerWithZendAuthenticationServiceTest extends AbstractHttpControllerTestCase
{
    protected $loader;
    protected $db;

    public function setUp()
    {
        @copy(
            __DIR__ . '/../TestAsset/autoload_zend_authenticationservice/db_oauth2.sqlite',
            sys_get_temp_dir() . '/dbtest.sqlite'
        );

        $this->setApplicationConfig(
            include __DIR__ . '/../TestAsset/zend.authenticationservice.application.config.php'
        );

        $this->loader = new Loader;
        $this->loader->register();

        parent::setUp();
    }

    public function getDb()
    {
        $config = $this->getApplication()->getServiceManager()->get('Config');
        $db = new \PDO($config['zf-oauth2']['db']['dsn']);

        return $db;
    }

    public function tearDown()
    {
        @unlink(sys_get_temp_dir() . '/dbtest.sqlite');
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
        $_GET['response_type'] = 'code';
        $_GET['client_id'] = 'testclient';
        $_GET['state'] = 'xyz';
        $_GET['redirect_uri'] = '/oauth/receivecode';
        $_POST['authorized'] = 'yes';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->getAuthenticationService();

        $this->dispatch('/oauth/authorize');
        $this->assertTrue($this->getResponse()->isRedirect());
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('authorize');

        $location = $this->getResponse()->getHeaders()->get('Location')->getUri();
        if (preg_match('#code=([0-9a-f]+)#', $location, $matches)) {
            $code = $matches[1];
        }

        // test data in database is correct
        $row = $this->getDb()->query("
            SELECT *
            FROM oauth_authorization_codes
            WHERE authorization_code = '" . $code . "'
        ")->fetch();

        $this->assertEquals('123', $row['user_id']);

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
        $this->assertTrue(!empty($response['access_token']));
    }
}
