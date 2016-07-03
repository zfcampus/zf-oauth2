<?php
/**
 * @copyright Copyright (c) 2016 João Dias <mail@joaodias.eu>
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

namespace ZFTest\OAuth2\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AuthControllerWithCustomAdapterTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../TestAsset/custom.application.config.php');

        parent::setUp();
    }

    public function testToken()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $request->getPost()->set('grant_type', 'password');
        $request->getPost()->set('client_id', 'public');
        $request->getPost()->set('username', 'banned_user');
        $request->getPost()->set('password', 'testpass');
        $request->setMethod('POST');

        $this->dispatch('/oauth');
        $this->assertControllerName('ZF\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(401);

        /** @var \Zend\Http\Response $response */
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $this->assertEquals('application/problem+json', $headers->get('content-type')->getFieldValue());

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals('banned', $response['title']);
        $this->assertEquals('User is banned', $response['detail']);
        $this->assertEquals('401', $response['status']);
    }
}
