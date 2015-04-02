<?php

namespace ZF\OAuth2\Provider\UserId;

use Zend\Stdlib\RequestInterface;

class Request implements UserIdProviderInterface
{
    /**
     * Use the Request to fetch the identity
     *
     * @return integer
     */
    public function __invoke(RequestInterface $request)
    {
        return $request->getQuery('user_id', null);
    }
}
