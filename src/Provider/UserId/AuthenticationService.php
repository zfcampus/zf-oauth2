<?php

namespace ZF\OAuth2\Provider\UserId;

use Zend\Stdlib\RequestInterface;
use Zend\Authentication\AuthenticationService as ZendAuthenticationService;

class AuthenticationService implements UserIdProviderInterface
{
    protected $authenticationService;

    /**
     *  Set authentication service
     *
     * @param AuthenticationService $service
     * @return $this
     */
    public function setAuthenticationService(ZendAuthenticationService $service)
    {
        $this->authenticationService = $service;

        return $this;
    }

    /**
     *  Get authentication service
     *
     * @param AuthenticationService $service
     * @return $this
     */
    public function getAuthenticationService()
    {
        return $this->authenticationService;
    }

    /**
     * Use the Zend authentication service to fetch the identity
     *
     * @return integer
     */
    public function __invoke(RequestInterface $request)
    {
        return $this->getAuthenticationService()->getIdentity();
    }
}
