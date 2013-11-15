<?php
/**
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

namespace ZFTest\OAuth2\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AuthControllerTest extends AbstractHttpControllerTestCase
{
    protected $db;

    public function setUp()
    {
        @copy(__DIR__ . '/../TestAsset/autoload/db_oauth2.sqlite', __DIR__ . '/../TestAsset/autoload/dbtest.sqlite');

        $this->setApplicationConfig(
            include __DIR__ . '/../TestAsset/application.config.php'
        );
        parent::setUp();
    }

    public function tearDown()
    {
        @unlink(__DIR__ . '/../TestAsset/autoload/dbtest.sqlite');
    }

    public function testToken()
    {
        $_POST['grant_type'] = 'client_credentials';
        $_SERVER['PHP_AUTH_USER'] = 'testclient';
        $_SERVER['PHP_AUTH_PW'] = 'testpass';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->dispatch('/oauth');
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(200);
        
        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertTrue(!empty($response['access_token']));
        $this->assertTrue(!empty($response['expires_in']));
        $this->assertTrue(array_key_exists('scope', $response));
        $this->assertTrue(!empty($response['token_type']));
    }

    public function testAuthorizeForm()
    {
        $_GET['response_type'] = 'code';
        $_GET['client_id'] = 'testclient';
        $_GET['state'] = 'xyz';
        
        $this->dispatch('/oauth/authorize');
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('authorize');
        $this->assertResponseStatusCode(200);
        $this->assertXpathQuery('//form/input[@name="authorized" and @value="yes"]');
        $this->assertXpathQuery('//form/input[@name="authorized" and @value="no"]');
    }

    public function testAuthorizeErrorParam()
    {
        $this->dispatch('/oauth/authorize');

        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('authorize');
        $this->assertResponseStatusCode(400);

        $headers = $this->getResponse()->getHeaders();
        $this->assertEquals('application/api-problem+json', $headers->get('content-type')->getFieldValue());

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals('invalid_client', $response['title']);
        $this->assertEquals('No client id supplied', $response['detail']);
        $this->assertEquals('400', $response['httpStatus']);
    }

    public function testAuthorizeCode()
    {
        $_GET['response_type'] = 'code';
        $_GET['client_id'] = 'testclient';
        $_GET['state'] = 'xyz';
        $_GET['redirect_uri'] = '/oauth/receivecode';
        $_POST['authorized'] = 'yes';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->dispatch('/oauth/authorize');
        $this->assertTrue($this->getResponse()->isRedirect());
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('authorize');

        $location = $this->getResponse()->getHeaders()->get('Location')->getUri();
        if (preg_match('#code=([0-9a-f]+)#', $location, $matches)) {
            $code = $matches[1];
        }
        // test get token from authorized code
        $_POST['grant_type'] = 'authorization_code';
        $_POST['code'] = $code;
        $_POST['redirect_uri'] = '/oauth/receivecode';
        $_SERVER['PHP_AUTH_USER'] = 'testclient';
        $_SERVER['PHP_AUTH_PW'] = 'testpass';

        $this->dispatch('/oauth');
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertTrue(!empty($response['access_token']));
    }

    public function testImplicitClientAuth()
    {
        $config = $this->getApplication()->getConfig();
        $allowImplicit = isset($config['zf-oauth2']['allow_implicit']) ? $config['zf-oauth2']['allow_implicit'] : false;

        if (!$allowImplicit) {
            $this->markTestSkipped('The allow implicit client mode is disabled');
        }

        $_GET['response_type'] = 'token';
        $_GET['client_id'] = 'testclient';
        $_GET['state'] = 'xyz';
        $_GET['redirect_uri'] = '/oauth/receivecode';
        $_POST['authorized'] = 'yes';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->dispatch('/oauth/authorize');
        $this->assertTrue($this->getResponse()->isRedirect());
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('authorize');

        $token    = '';
        $location = $this->getResponse()->getHeaders()->get('Location')->getUri();

        if (preg_match('#access_token=([0-9a-f]+)#', $location, $matches)) {
            $token = $matches[1];
        }
        $this->assertTrue(!empty($token));
    }

    public function testResource()
    {
        $_POST['grant_type'] = 'client_credentials';
        $_SERVER['PHP_AUTH_USER'] = 'testclient';
        $_SERVER['PHP_AUTH_PW'] = 'testpass';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->dispatch('/oauth');
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertTrue(!empty($response['access_token']));

        $token = $response['access_token'];

        // test resource through token by POST
        $_POST['access_token'] = $token;
        unset($_POST['grant_type']);
        unset($_SERVER['PHP_AUTH_USER']);
        unset($_SERVER['PHP_AUTH_PW']);

        $this->dispatch('/oauth/resource');
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('resource');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertEquals('You accessed my APIs!', $response['message']);

        // test resource through token by Bearer header
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $token";
        unset($_POST['access_token']);
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->dispatch('/oauth/resource');
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('resource');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertEquals('You accessed my APIs!', $response['message']);
    }
}
