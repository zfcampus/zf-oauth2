<?php

namespace ZFTest\OAuth2\Adapter\Pdo;

use OAuth2\Storage\AccessTokenInterface;

class AccessTokenTest extends BaseTest
{
    /** @dataProvider provideStorage */
    public function testSetAccessToken(AccessTokenInterface $storage)
    {
        if ($storage instanceof NullStorage) {
            $this->markTestSkipped('Skipped Storage: ' . $storage->getMessage());

            return;
        }

        // assert token we are about to add does not exist
        $token = $storage->getAccessToken('newtoken');
        $this->assertFalse($token);

        // add new token
        $expires = time() + 20;
        $success = $storage->setAccessToken('newtoken', 'oauth_test_client', '1', $expires);
        $this->assertTrue($success);

        $token = $storage->getAccessToken('newtoken');
        $this->assertNotNull($token);
        $this->assertArrayHasKey('access_token', $token);
        $this->assertArrayHasKey('client_id', $token);
        $this->assertArrayHasKey('user_id', $token);
        $this->assertArrayHasKey('expires', $token);
        $this->assertEquals($token['access_token'], 'newtoken');
        $this->assertEquals($token['client_id'], 'oauth_test_client');
        $this->assertEquals($token['user_id'], '1'); # reference from client
        $this->assertEquals($token['expires'], $expires);

        // change existing token
        $expires = time() + 42;
        $success = $storage->setAccessToken('newtoken', 'oauth_test_client2', '1', $expires);
        $this->assertTrue($success);

        $token = $storage->getAccessToken('newtoken');
        $this->assertNotNull($token);
        $this->assertArrayHasKey('access_token', $token);
        $this->assertArrayHasKey('client_id', $token);
        $this->assertArrayHasKey('user_id', $token);
        $this->assertArrayHasKey('expires', $token);
        $this->assertEquals($token['access_token'], 'newtoken');
        $this->assertEquals($token['client_id'], 'oauth_test_client2');
        $this->assertEquals($token['user_id'], '1'); # reference from client
        $this->assertEquals($token['expires'], $expires);
    }
}
