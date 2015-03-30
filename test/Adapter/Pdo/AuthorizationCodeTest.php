<?php

namespace ZFTest\OAuth2\Adapter\Pdo;

use OAuth2\Storage\AuthorizationCodeInterface;
use OAuth2\Storage\AccessTokenInterface;

class AuthorizationCodeTest extends BaseTest
{
    /** @dataProvider provideStorage */
    public function testGetAuthorizationCode(AuthorizationCodeInterface $storage)
    {
        if ($storage instanceof NullStorage) {
            $this->markTestSkipped('Skipped Storage: ' . $storage->getMessage());

            return;
        }

        // nonexistant client_id
        $details = $storage->getAuthorizationCode('faketoken');
        $this->assertFalse($details);

        // valid client_id
        $details = $storage->getAuthorizationCode('testtoken');
        $this->assertNotNull($details);
    }

    /** @dataProvider provideStorage */
    public function testSetAuthorizationCode(AuthorizationCodeInterface $storage)
    {
        if ($storage instanceof NullStorage) {
            $this->markTestSkipped('Skipped Storage: ' . $storage->getMessage());

            return;
        }

        // assert code we are about to add does not exist
        $code = $storage->getAuthorizationCode('newcode');
        $this->assertFalse($code);

        // add new code
        $expires = time() + 20;
        $success = $storage->setAuthorizationCode(
            'newcode',
            'oauth_test_client',
            '1',
            'http://example.com',
            $expires
        );
        $this->assertTrue($success);

        $code = $storage->getAuthorizationCode('newcode');
        $this->assertNotNull($code);
        $this->assertArrayHasKey('authorization_code', $code);
        $this->assertArrayHasKey('client_id', $code);
        $this->assertArrayHasKey('user_id', $code);
        $this->assertArrayHasKey('redirect_uri', $code);
        $this->assertArrayHasKey('expires', $code);
        $this->assertEquals($code['authorization_code'], 'newcode');
        $this->assertEquals($code['client_id'], 'oauth_test_client');
        $this->assertEquals($code['user_id'], '1'); # reference from oauth_test_client
        $this->assertEquals($code['redirect_uri'], 'http://example.com');
        $this->assertEquals($code['expires'], $expires);

        // change existing code
        $expires = time() + 42;
        $success = $storage->setAuthorizationCode(
            'newcode',
            'oauth_test_client2',
            '1',
            'http://example.org',
            $expires
        );
        $this->assertTrue($success);

        $code = $storage->getAuthorizationCode('newcode');
        $this->assertNotNull($code);
        $this->assertArrayHasKey('authorization_code', $code);
        $this->assertArrayHasKey('client_id', $code);
        $this->assertArrayHasKey('user_id', $code);
        $this->assertArrayHasKey('redirect_uri', $code);
        $this->assertArrayHasKey('expires', $code);
        $this->assertEquals($code['authorization_code'], 'newcode');
        $this->assertEquals($code['client_id'], 'oauth_test_client2');
        $this->assertEquals($code['user_id'], '1'); # reference from oauth_test_client
        $this->assertEquals($code['redirect_uri'], 'http://example.org');
        $this->assertEquals($code['expires'], $expires);
    }

        /** @dataProvider provideStorage */
    public function testExpireAccessToken(AccessTokenInterface $storage)
    {
        if ($storage instanceof NullStorage) {
            $this->markTestSkipped('Skipped Storage: ' . $storage->getMessage());

            return;
        }

        // create a valid code
        $expires = time() + 20;
        $success = $storage->setAuthorizationCode(
            'code-to-expire',
            'oauth_test_client',
            '1',
            'http://example.com',
            time() + 20
        );

        $this->assertTrue($success);

        // verify the new code exists
        $code = $storage->getAuthorizationCode('code-to-expire');
        $this->assertNotNull($code);

        $this->assertArrayHasKey('authorization_code', $code);
        $this->assertEquals($code['authorization_code'], 'code-to-expire');

        // now expire the code and ensure it's no longer available
        $storage->expireAuthorizationCode('code-to-expire');
        $code = $storage->getAuthorizationCode('code-to-expire');
        $this->assertFalse($code);
    }
}
