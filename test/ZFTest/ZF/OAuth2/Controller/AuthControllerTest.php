<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

namespace ZFTest\ZF\OAuth2\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AuthControllerTest extends AbstractHttpControllerTestCase
{
    protected $db;

    public function setUp()
    {
        $configFile = realpath(__DIR__ . '/../TestAsset/autoload/oauth2.local.php'); 
        if (!file_exists($configFile)) {
            $this->markTestSkipped(
                "To execute the test you need to create and edit the file TestAsset/autoload/oauth2.local.php"
            );
        }

        // Insert or update the test values for the client_id
        $config   = include $configFile;
        $paramDb  = $config['oauth2']['db'];
        $this->db = new \PDO($paramDb['dsn'], $paramDb['username'], $paramDb['password']);
        $stmt     = $this->db->prepare(
            'REPLACE INTO oauth_clients (client_id, client_secret, redirect_uri) VALUES ("testclient", "testpass", "http://fake")'
        );
        if ($stmt->execute() === false) {
            $this->markTestSkipped(
                "I cannot use the OAuth2 database, please check the table structures with the data/db_oauth2.sql file"
            );
        }

        $this->setApplicationConfig(
            include __DIR__ . '/../TestAsset/application.config.php'
        );
        parent::setUp();
    }

    public function tearDown()
    {
        if ($this->db) {
            $stmt = $this->db->prepare(
                'DELETE FROM oauth_clients WHERE client_id="testclient"'
            );
            $stmt->execute();
        }    
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
        $_POST['authorized'] = 'yes';

        $this->dispatch('/oauth/authorize');
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('authorize');
        $this->assertResponseStatusCode(200);
        $this->assertQueryContentRegex('h2', '#SUCCESS! Authorization Code: [0-9a-f]+#');

        if (preg_match('#Code: ([0-9a-f]+)#', $this->getResponse()->getContent(), $matches)) {
            $code = $matches[1];
        }
        // test get token from authorized code
        $_POST['grant_type'] = 'authorization_code';
        $_POST['code'] = $code;
        $_SERVER['PHP_AUTH_USER'] = 'testclient';
        $_SERVER['PHP_AUTH_PW'] = 'testpass';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        $this->dispatch('/oauth');
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertTrue(!empty($response['access_token']));
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
