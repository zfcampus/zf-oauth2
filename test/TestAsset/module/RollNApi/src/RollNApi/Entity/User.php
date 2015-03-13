<?php

namespace RollNApi\Entity;

use Doctrine\ORM\Mapping as ORM;
use ZfcUser\Entity\UserInterface;
use Exception;

/**
 * User
 */
class User implements UserInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $displayName;

    /**
     * @var string
     */
    private $password;

    /**
     * @var integer
     */
    private $state;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $userAlbum;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $client;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userAlbum = new \Doctrine\Common\Collections\ArrayCollection();
        $this->client = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getArrayCopy()
    {
        return array(
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'displayName' => $this->getDisplayName(),
            'password' => $this->getPassword(),
            'state' => $this->getState(),
        );
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set displayName
     *
     * @param string $displayName
     * @return User
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Get displayName
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set state
     *
     * @param integer $state
     * @return User
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set id
     *
     * @return integer
     */
    public function setId($id)
    {
        throw new Exception('Set ID not supported');
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add userAlbum
     *
     * @param \RollNApi\Entity\UserAlbum $userAlbum
     * @return User
     */
    public function addUserAlbum(\RollNApi\Entity\UserAlbum $userAlbum)
    {
        $this->userAlbum[] = $userAlbum;

        return $this;
    }

    /**
     * Remove userAlbum
     *
     * @param \RollNApi\Entity\UserAlbum $userAlbum
     */
    public function removeUserAlbum(\RollNApi\Entity\UserAlbum $userAlbum)
    {
        $this->userAlbum->removeElement($userAlbum);
    }

    /**
     * Get userAlbum
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserAlbum()
    {
        return $this->userAlbum;
    }

    /**
     * Add client
     *
     * @param \ZF\OAuth2\Entity\Client $client
     * @return User
     */
    public function addClient(\ZF\OAuth2\Entity\Client $client)
    {
        $this->client[] = $client;

        return $this;
    }

    /**
     * Remove client
     *
     * @param \ZF\OAuth2\Entity\Client $client
     */
    public function removeClient(\ZF\OAuth2\Entity\Client $client)
    {
        $this->client->removeElement($client);
    }

    /**
     * Get client
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClient()
    {
        return $this->client;
    }
}
