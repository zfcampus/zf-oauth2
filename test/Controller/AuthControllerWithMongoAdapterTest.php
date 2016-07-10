<?php
/**
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

namespace ZFTest\OAuth2\Controller;

use MongoClient;
use MongoConnectionException;
use MongoDB;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AuthControllerWithMongoAdapterTest extends AbstractHttpControllerTestCase
{
    /**
     * @var MongoDB
     */
    protected $db;

    /**
     * Default data to insert in database.
     *
     * Defined here to prevent "cannot pass by reference" issues when using
     * alcaeus/mongo-php-adapter (as that library accepts a reference for the
     * first argument, and unassigned arrays fail that).
     *
     * @var array
     */
    protected $defaultData = [
        'client_id'     => 'testclient',
        'client_secret' => '$2y$14$f3qml4G2hG6sxM26VMq.geDYbsS089IBtVJ7DlD05BoViS9PFykE2',
        'redirect_uri'  => '/oauth/receivecode',
        'grant_types'   => null,
    ];

    public function setUp()
    {
        if (! (extension_loaded('mongodb') || extension_loaded('mongo'))
            || ! class_exists(MongoClient::class)
            || version_compare(MongoClient::VERSION, '1.4.1', '<')
        ) {
            $this->markTestSkipped('ext/mongo ^1.4.1 or ext/mongodb + alcaeus/mongo-php-adapter is not available.');
        }

        $this->setApplicationConfig(include __DIR__ . '/../TestAsset/mongo.application.config.php');

        parent::setUp();

        try {
            $client = new MongoClient("mongodb://127.0.0.1:27017");
        } catch (MongoConnectionException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $this->db = $client->selectDB('zf_oauth2_test');
        $this->db->oauth_clients->insert($this->defaultData);

        $this->getApplicationServiceLocator()->setService('MongoDB', $this->db);
    }

    public function tearDown()
    {
        if ($this->db instanceof MongoDB) {
            $this->db->drop();
        }

        parent::tearDown();
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

    public function testAuthorizeErrorParam()
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

    public function testAuthorizeCode()
    {
        $queryData = [
            'response_type' => 'code',
            'client_id'     => 'testclient',
            'state'         => 'xyz',
            'redirect_uri'  => '/oauth/receivecode'
        ];

        $this->dispatch('/oauth/authorize?' . http_build_query($queryData), 'POST', ['authorized' => 'yes']);
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
        $request->getHeaders()->addHeaderLine('Authorization', sprintf('Bearer %s', $token));
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
}
