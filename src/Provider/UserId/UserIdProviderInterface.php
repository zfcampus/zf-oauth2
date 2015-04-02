<?php

namespace ZF\OAuth2\Provider\UserId;

use Zend\Stdlib\RequestInterface;

interface UserIdProviderInterface
{
    /**
     * Return the current authenticated user
     * identifier as a string or integer
     *
     * @param Request $request
     * @return mixed
     */
    public function __invoke(RequestInterface $request);
}
