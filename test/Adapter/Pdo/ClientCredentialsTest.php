<?php

namespace ZFTest\OAuth2\Adapter\Pdo;

use OAuth2\Storage\ClientCredentialsInterface;

class ClientCredentialsTest extends BaseTest
{
    /** @dataProvider provideStorage */
    public function testCheckClientCredentials(ClientCredentialsInterface $storage)
    {
        if ($storage instanceof NullStorage) {
            $this->markTestSkipped('Skipped Storage: ' . $storage->getMessage());

            return;
        }

        // nonexistant client_id
        $pass = $storage->checkClientCredentials('fakeclient', 'testpass');
        $this->assertFalse($pass);

        // invalid password
        $pass = $storage->checkClientCredentials('testclient', 'invalidcredentials');
        $this->assertFalse($pass);

        // valid credentials
        $pass = $storage->checkClientCredentials('testclient', 'testpass');
        $this->assertTrue($pass);
    }
}
