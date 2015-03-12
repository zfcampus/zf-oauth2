<?php

namespace ZF\OAuth2\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefreshToken
 */
class RefreshToken
{
    /**
     * @var string
     */
    private $refreshToken;

    /**
     * @var \DateTime
     */
    private $expires;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \ZF\OAuth2\Entity\Client
     */
    private $client;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $scope;

    /**
     * @var object
     */
    private $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->scope = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function exchangeArray(array $array)
    {
        foreach ($array as $key => $value) {
            switch ($key) {
                case 'refreshToken':
                    $this->setRefreshToken($value);
                    break;
                case 'expires':
                    $this->setExpires($value);
                    break;
                case 'client':
                    $this->setClient($value);
                    break;
                case 'scope':
                    // Clear old collection
                    foreach ($value as $remove) {
                        $this->removeScope($remove);
                        $remove->removeRefreshToken($this);
                    }

                    // Add new collection
                    foreach ($value as $scope) {
                        $this->addScope($scope);
                        $scope->removeRefreshToken($this);
                    }
                    break;
                case 'user':
                    $this->setUser($value);
                    break;
                default:
                    break;
            }
        }

        return $this;
    }

    public function getArrayCopy()
    {
        return array(
            'id' => $this->getId(),
            'refreshToken' => $this->getRefreshToken(),
            'expires' => $this->getExpires(),
            'client' => $this->getClient(),
            'scope' => $this->getScope(),
            'user' => $this->getUser(),
        );
    }

    /**
     * Set refreshToken
     *
     * @param string $refreshToken
     * @return RefreshToken
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Get refreshToken
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Set expires
     *
     * @param \DateTime $expires
     * @return RefreshToken
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * Get expires
     *
     * @return \DateTime
     */
    public function getExpires()
    {
        return $this->expires;
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
     * Set client
     *
     * @param \ZF\OAuth2\Entity\Client $client
     * @return RefreshToken
     */
    public function setClient(\ZF\OAuth2\Entity\Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \ZF\OAuth2\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Add scope
     *
     * @param \ZF\OAuth2\Entity\Scope $scope
     * @return RefreshToken
     */
    public function addScope(\ZF\OAuth2\Entity\Scope $scope)
    {
        $this->scope[] = $scope;

        return $this;
    }

    /**
     * Remove scope
     *
     * @param \ZF\OAuth2\Entity\Scope $scope
     */
    public function removeScope(\ZF\OAuth2\Entity\Scope $scope)
    {
        $this->scope->removeElement($scope);
    }

    /**
     * Get scope
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getScope()
    {
        return $this->scope;
    }


    /**
     * Set user
     *
     * @param $user
     * @return AuthorizationCode
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return user
     */
    public function getUser()
    {
        return $this->user;
    }
}
