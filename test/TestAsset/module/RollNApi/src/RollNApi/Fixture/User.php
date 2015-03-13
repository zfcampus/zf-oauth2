<?php

namespace RollNApi\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use RollNApi\Entity;
use ZF\OAuth2\Entity\Client as OAuth2Client;
use ZF\OAuth2\Entity\Scope as OAuth2Scope;
use ZF\OAuth2\Entity\Jwt as OAuth2Jwt;
use ZF\OAuth2\Entity\Jti as OAuth2Jti;
use Zend\Crypt\Password\Bcrypt;
use DateTime;

class User implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $userFlop = false;

        $bcrypt = new Bcrypt();
        $bcrypt->setCost(14);

        $scope1 = new OAuth2Scope();
        $scope1->setScope('scope1');
        $scope1->setIsDefault(true);

        $manager->persist($scope1);

        $scope2 = new OAuth2Scope();
        $scope2->setScope('scope2');
        $scope2->setIsDefault(false);

        $manager->persist($scope2);

        $scope3 = new OAuth2Scope();
        $scope3->setScope('scope3');
        $scope3->setIsDefault(false);

        $manager->persist($scope3);

        $user1 = new Entity\User();
        $user1->setUsername('user1');
        $user1->setPassword($bcrypt->create('user1password'));
        $user1->setEmail('tom.h.anderson@gmail.com');
        $user1->setDisplayName('Tom Anderson');

        $manager->persist($user1);

        $client1 = new OAuth2Client();
        $client1->setClientId('client1');
        $client1->setSecret($bcrypt->create('client1password'));
        $client1->setGrantType(array(
            'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'password',
            'authorization_code',
            'client_credentials',
            'refresh_token'
        ));
        $client1->setUser($user1);
        $client1->addScope($scope2);
        $scope2->addClient($client1);

        $manager->persist($client1);

        $jwt1 = new OAuth2Jwt();
        $jwt1->setSubject('user1');
        $jwt1->setPublicKey(file_get_contents(__DIR__ . '/../../../../../media/pubkey.pem'));
        $jwt1->setClient($client1);

        $manager->persist($jwt1);

        $jti1 = new OAuth2Jti();
        $jti1->setSubject('user1');
        $jti1->setAudience('http://localhost:8083');
        $jti1->setExpires(new DateTime(' today +1 day'));
        $jti1->setJti('123456abcdef');
        $jti1->setClient($client1);

        $manager->persist($jti1);

        $user2 = new Entity\User();
        $user2->setUsername('user2');
        $user2->setPassword($bcrypt->create('user2password'));
        $user2->setEmail('tom.h.anderson@gmail.com');
        $user2->setDisplayName('Tom Anderson');

        $manager->persist($user2);

        $client2 = new OAuth2Client();
        $client2->setClientId('client2');
        $client2->setSecret($bcrypt->create('client2password'));
        $client2->setGrantType(array('client_credentials', 'refresh_token'));
        $client2->setUser($user2);

        $manager->persist($client2);

        // Artists
        $artist = new Entity\Artist();
        $artist->setName('Grateful Dead');

        $manager->persist($artist);

        $albums = array(
            'The Grateful Dead',
            'Anthem of the Sun',
            'Aoxomoxoa',
            'Live/Dead',
            'Workingman\'s Dead',
            'American Beauty',
        );

        foreach ($albums as $name) {
            $album = new Entity\Album();
            $album->setArtist($artist);
            $album->setName($name);

            $manager->persist($album);

            $userAlbum = new Entity\UserAlbum();
            $userAlbum->setAlbum($album);
            if ($userFlop = !$userFlop) {
                $userAlbum->setUser($user1);
            } else {
                $userAlbum->setUser($user2);
            }

            $userAlbum->setDescription("Description for $name");

            $manager->persist($userAlbum);
        }

        $manager->flush();
    }
}
