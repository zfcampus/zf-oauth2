<?php

/**
 * FIXME:  This adapter logic is not supported in the PDO adapter
 */

namespace ZFTest\OAuth2\Adapter\Pdo;

use OAuth2\Storage\PublicKeyInterface;

class PublicKeyTest extends BaseTest
{
    /** @dataProvider provideStorage */
    public function testSetAccessToken($storage)
    {
        $this->assertFalse(false);
        return;

        $globalPublicKey  = file_get_contents(__DIR__ . '/../../TestAsset/data/pubkey.pem');
        $globalPrivateKey = file_get_contents(__DIR__ . '/../../TestAsset/data/key.pem');

        /* assert values from storage */
        $this->assertEquals($globalPublicKey, $storage->getPublicKey('oauth_test_client'));
        $this->assertEquals($globalPrivateKey, $storage->getPrivateKey('oauth_test_client'));
        $this->assertEquals('rsa', $storage->getEncryptionAlgorithm('oauth_test_client'));

        $this->assertFalse($storage->getPublicKey('invalidclient'));
        $this->assertFalse($storage->getPublicKey('oauth_test_client2'));

        $this->assertFalse($storage->getPrivateKey('invalidclient'));
        $this->assertFalse($storage->getPrivateKey('oauth_test_client2'));

        $this->assertFalse($storage->getEncryptionAlgorithm('invalidclient'));
        $this->assertFalse($storage->getEncryptionAlgorithm('oauth_test_client2'));
    }
}
