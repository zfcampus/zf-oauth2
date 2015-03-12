<?php

namespace ZF\OAuth2\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AuthorizationCode
 */
class AuthorizationCode
{
    /**
     * @var string
     */
    private $authorizationCode;

    /**
     * @var string
     */
    private $redirectUri;

    /**
     * @var \DateTime
     */
    private $expires;

    /**
     * @var string
     */
    private $idToken;

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

    public function getArrayCopy()
    {
        return array(
            'id' => $this->getId(),
            'authorizationCode' => $this->getAuthorizationCode(),
            'redirectUri' => $this->getRedirectUri(),
            'expires' => $this->getExpires(),
            'idToken' => $this->getIdToken(),
            'scope' => $this->getScope(),
            'client' => $this->getClient(),
            'user' => $this->getUser(),
        );
    }

    public function exchangeArray(array $array)
    {
        foreach ($array as $key => $value) {
            switch ($key) {
                case 'authorizationCode':
                    $this->setAuthorizationCode($value);
                    break;
                case 'redirectUri':
                    $this->setRedirectUri($value);
                    break;
                case 'expires':
                    $this->setExpires($value);
                    break;
                case 'idToken':
                    $this->setIdToken($value);
                    break;
                case 'client':
                    $this->setClient($value);
                    break;
                case 'scope':
                    // Clear old collection
                    foreach ($value as $remove) {
                        $this->removeScope($remove);
                        $remove->removeAuthorizationCode($this);
                    }
                    // Add new collection
                    foreach ($value as $scope) {
                        $this->addScope($scope);
                        $scope->addAuthorizationCode($this);
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

    /**
     * Set authorizationCode
     *
     * @param string $authorizationCode
     * @return AuthorizationCode
     */
    public function setAuthorizationCode($authorizationCode)
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    /**
     * Get authorizationCode
     *
     * @return string
     */
    public function getAuthorizationCode()
    {
        return $this->authorizationCode;
    }

    /**
     * Set redirectUri
     *
     * @param string $redirectUri
     * @return AuthorizationCode
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    /**
     * Get redirectUri
     *
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * Set expires
     *
     * @param \DateTime $expires
     * @return AuthorizationCode
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
     * Set idToken
     *
     * @param string $idToken
     * @return AuthorizationCode
     */
    public function setIdToken($idToken)
    {
        $this->idToken = $idToken;

        return $this;
    }

    /**
     * Get idToken
     *
     * @return string
     */
    public function getIdToken()
    {
        return $this->idToken;
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
     * @return AuthorizationCode
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
     * @return AuthorizationCode
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
