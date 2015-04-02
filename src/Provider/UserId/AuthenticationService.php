<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Provider\UserId;

use Zend\Authentication\AuthenticationService as ZendAuthenticationService;
use Zend\Stdlib\RequestInterface;

class AuthenticationService implements UserIdProviderInterface
{
    /**
     * @var ZendAuthenticationService
     */
    private $authenticationService;

    /**
     *  Set authentication service
     *
     * @param ZendAuthenticationService $service
     */
    public function __construct(ZendAuthenticationService $service)
    {
        $this->authenticationService = $service;
    }

    /**
     * Use Zend\Authentication\AuthenticationService to fetch the identity.
     *
     * @param RequestInterface $request
     * @return mixed
     */
    public function __invoke(RequestInterface $request)
    {
        return $this->authenticationService->getIdentity();
    }
}
