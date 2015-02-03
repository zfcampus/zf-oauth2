<?php

namespace RollNApi\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Zend\Crypt\Password\Bcrypt;
use RollNApi\Entity\User;
use ZF\OAuth2\Entity;
use DateTime;

class UnitTest implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $bcrypt = new Bcrypt();
        $bcrypt->setCost(14);

        /**
         * These fixtures are for zf-oauth2 unittest
         */

        $scope = new Entity\Scope();
        $scope->setScope('clientscope1');

        $scope2 = new Entity\Scope();
        $scope2->setScope('supportedscope1');

        $scope3 = new Entity\Scope();
        $scope3->setScope('supportedscope2');

        $scope4 = new Entity\Scope();
        $scope4->setScope('supportedscope3');

        $scope5 = new Entity\Scope();
        $scope5->setScope('defaultscope1');
        $scope5->setIsDefault(true);

        $scope6 = new Entity\Scope();
        $scope6->setScope('defaultscope2');
        $scope6->setIsDefault(true);

        $manager->persist($scope);
        $manager->persist($scope2);
        $manager->persist($scope3);
        $manager->persist($scope4);
        $manager->persist($scope5);
        $manager->persist($scope6);

        $user = new User();
        $user->setUsername('oauth_test_user');
        $user->setPassword($bcrypt->create('testpass'));
        $user2 = new User();

        $manager->persist($user);
        $manager->persist($user2);

        $client = new Entity\Client();
        $client->setClientId('oauth_test_client');
        $client->setSecret($bcrypt->create('testpass'));
        $client->setGrantType(array(
            'implicit',
        ));
        $client->setUser($user);
        $client->addScope($scope);
        $scope->addClient($client);

        $client2 = new Entity\Client();
        $client2->setClientId('oauth_test_client2');
        $client2->setSecret($bcrypt->create('testpass'));
        $client2->setGrantType(array(
            'implicit',
        ));
        $client2->setUser($user2);

        $client3 = new Entity\Client();
        $client3->setClientId('oauth_test_client3');
        $client3->setUser($user2);

        $manager->persist($client);
        $manager->persist($client2);
        $manager->persist($client3);

        $accessToken = new Entity\AccessToken();
        $accessToken->setClient($client);
        $accessToken->setExpires(DateTime::createFromFormat('Y-m-d', '2020-01-01'));
        $accessToken->setAccessToken('testtoken');

        $manager->persist($accessToken);


        $authorizationCode = new Entity\AuthorizationCode();
        $authorizationCode->setAuthorizationCode('testtoken');
        $authorizationCode->setClient($client);
        $authorizationCode->setExpires(DateTime::createFromFormat('Y-m-d', '2020-01-01'));

        $manager->persist($authorizationCode);

        $jwt = new Entity\Jwt;
        $jwt->setClient($client);
        $jwt->setSubject('test_subject');
        $jwt->setPublicKey("-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCvfF+Cw8nzsc9Twam37SYpAW3+
lRGUle/hYnd9obfBvDHKBvgb1WfGCblwjwImGL9u0rEIW2sspkwBEsGGFFBmSaqq
fvEER7Yr++VIidOUHkas3cHO1TVoERO3s0THOobw0OzghPnMJL6ayelYOESwfnqR
WfuEMSaWaW0G38QPzwIDAQAB
-----END PUBLIC KEY-----
");

        $manager->persist($jwt);

        $publicKey = new Entity\PublicKey();
        $publicKey->setPublicKey("-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCvfF+Cw8nzsc9Twam37SYpAW3+
lRGUle/hYnd9obfBvDHKBvgb1WfGCblwjwImGL9u0rEIW2sspkwBEsGGFFBmSaqq
fvEER7Yr++VIidOUHkas3cHO1TVoERO3s0THOobw0OzghPnMJL6ayelYOESwfnqR
WfuEMSaWaW0G38QPzwIDAQAB
-----END PUBLIC KEY-----
");
        $publicKey->setPrivateKey("-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQCvfF+Cw8nzsc9Twam37SYpAW3+lRGUle/hYnd9obfBvDHKBvgb
1WfGCblwjwImGL9u0rEIW2sspkwBEsGGFFBmSaqqfvEER7Yr++VIidOUHkas3cHO
1TVoERO3s0THOobw0OzghPnMJL6ayelYOESwfnqRWfuEMSaWaW0G38QPzwIDAQAB
AoGAYHtBB+QdZJ6eHq6bYURBdsoSb6YFxGurN3+rsqb3IM0XkrvCLYtnQrqV+gym
Ycu5dHTiYHXitum3X9+wBseka692RYcYuQbBIeT64H91kiFKLBy1vy/g8cmUyI0X
TmabVBnFgS6JGL26C3zC71k3xmd0OQAEpAKg/vYaz2gTwAECQQDYiaEcS29aFsxm
vT3/IvNV17nGvH5sJAuOkKzf6P6TyE2NmAqSjqngm0wSwRdlARcWM+v6H2R/0qdF
6azDItuBAkEAz3eCWygU7pLOtw4VfrX1ppWBIw6qLNF2lKdKPnFqFk5c3GK9ek2G
tTn6NI3LT5NnKu2/YFTR4tr4hgBbdJfTTwJAWWQfxZ2Cn49P3I39PQmBqQuAnwGL
szsCJl2lcF4wUnPbSDvfCXepu5aAxjE+Zi0YCctvfHdfNsGQ2nTIJFqMgQJBAL5L
D/YsvYZWgeTFtlGS9M7nMpvFR7H0LqALEb5UqMns9p/usX0MvxJbK3Qo2uMSgP6P
M4pYQmuiDXJbwYcf+2ECQCB3s5z9niG6oxVicCfK/l6VJNPifhtr8N48jO0ejWeB
1OYsqgH36dp0vjhmtUZip0ikLOxdOueHeOZEjwlt2l8=
-----END RSA PRIVATE KEY-----
");
        $publicKey->setEncryptionAlgorithm('rsa');
        $publicKey->setClient($client);

        $manager->persist($publicKey);

        $manager->flush();
    }
}
