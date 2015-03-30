<?php
/**
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
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

        $this->setApplicationConfig(include __DIR__ . '/../TestAsset/application.config.php');
        parent::setUp();
    }

    public function tearDown()
    {
        @unlink(__DIR__ . '/../TestAsset/autoload/dbtest.sqlite');
    }

    public function testToken()
    {
        $request = $this->getRequest();
        $request->getPost()->set('grant_type', 'client_credentials');
        $request->getServer()->set('PHP_AUTH_USER', 'testclient');
        $request->getServer()->set('PHP_AUTH_PW', 'testpass');
        $request->setMethod('POST');

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

    public function testTokenErrorIsApiProblem()
    {
        $request = $this->getRequest();
        $request->getPost()->set('grant_type', 'fake_grant_type');
        $request->getServer()->set('PHP_AUTH_USER', 'testclient');
        $request->getServer()->set('PHP_AUTH_PW', 'testpass');
        $request->setMethod('POST');

        $this->dispatch('/oauth');
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(400);

        $headers = $this->getResponse()->getHeaders();
        $this->assertEquals('application/problem+json', $headers->get('content-type')->getFieldValue());

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals('unsupported_grant_type', $response['title']);
        $this->assertEquals('Grant type "fake_grant_type" not supported', $response['detail']);
        $this->assertEquals('400', $response['status']);
    }

    public function testTokenErrorIsOAuth2Format()
    {
        $request = $this->getRequest();
        $request->getPost()->set('grant_type', 'fake_grant_type');
        $request->getServer()->set('PHP_AUTH_USER', 'testclient');
        $request->getServer()->set('PHP_AUTH_PW', 'testpass');
        $request->setMethod('POST');

        $this->setIsOAuth2FormatResponse();

        $this->dispatch('/oauth');
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(400);

        $headers = $this->getResponse()->getHeaders();
        $this->assertEquals('application/json', $headers->get('content-type')->getFieldValue());

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals('unsupported_grant_type', $response['error']);
        $this->assertEquals('Grant type "fake_grant_type" not supported', $response['error_description']);
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

    public function testAuthorizeParamErrorIsApiProblem()
    {
        $this->dispatch('/oauth/authorize');

        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('authorize');
        $this->assertResponseStatusCode(400);

        $headers = $this->getResponse()->getHeaders();
        $this->assertEquals('application/problem+json', $headers->get('content-type')->getFieldValue());

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals('invalid_client', $response['title']);
        $this->assertEquals('No client id supplied', $response['detail']);
        $this->assertEquals('400', $response['status']);
    }

    public function testAuthorizeParamErrorIsOAuth2Format()
    {
        $this->setIsOAuth2FormatResponse();

        $this->dispatch('/oauth/authorize');

        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('authorize');
        $this->assertResponseStatusCode(400);

        $headers = $this->getResponse()->getHeaders();
        $this->assertEquals('application/json', $headers->get('content-type')->getFieldValue());

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals('invalid_client', $response['error']);
        $this->assertEquals('No client id supplied', $response['error_description']);
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

    public function testImplicitClientAuth()
    {
        $config = $this->getApplication()->getConfig();
        $allowImplicit = isset($config['zf-oauth2']['allow_implicit']) ? $config['zf-oauth2']['allow_implicit'] : false;

        if (!$allowImplicit) {
            $this->markTestSkipped('The allow implicit client mode is disabled');
        }

        $request = $this->getRequest();
        $request->getQuery()->set('response_type', 'token');
        $request->getQuery()->set('client_id', 'testclient');
        $request->getQuery()->set('state', 'xyz');
        $request->getQuery()->set('redirect_uri', '/oauth/receivecode');
        $request->getPost()->set('authorized', 'yes');
        $request->setMethod('POST');

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
        $request = $this->getRequest();
        $request->getPost()->set('grant_type', 'client_credentials');
        $request->getServer()->set('PHP_AUTH_USER', 'testclient');
        $request->getServer()->set('PHP_AUTH_PW', 'testpass');
        $request->setMethod('POST');

        $this->dispatch('/oauth');
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertTrue(!empty($response['access_token']));

        $token = $response['access_token'];

        // test resource through token by POST
        $post = $request->getPost();
        unset($post['grant_type']);
        $post->set('access_token', $token);
        $server = $request->getServer();
        unset($server['PHP_AUTH_USER']);
        unset($server['PHP_AUTH_PW']);

        $this->dispatch('/oauth/resource');
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('resource');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);

        $this->assertTrue($response['success']);
        $this->assertEquals('You accessed my APIs!', $response['message']);

        // test resource through token by Bearer header
        $request->getHeaders()
            ->addHeaderLine('Authorization', 'Bearer ' . $token);
        unset($post['access_token']);
        $request->setMethod('GET');

        $this->dispatch('/oauth/resource');
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('resource');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertEquals('You accessed my APIs!', $response['message']);
    }

    protected function setIsOAuth2FormatResponse()
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        $config = $serviceManager->get('Config');
        $config['zf-oauth2']['api_problem_error_response'] = false;

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Config', $config);
    }
}
