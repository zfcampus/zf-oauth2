<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Provider\UserId;

use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Stdlib\RequestInterface;

class AuthenticationService implements UserIdProviderInterface
{
    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;

    /**
     * @var string
     */
    private $userId = 'id';

    /**
     *  Set authentication service
     *
     * @param AuthenticationServiceInterface $service
     * @param array $config
     */
    public function __construct(AuthenticationServiceInterface $service = null, $config = [])
    {
        $this->authenticationService = $service;

        if (isset($config['zf-oauth2']['user_id'])) {
            $this->userId = $config['zf-oauth2']['user_id'];
        }
    }

    /**
     * Use implementation of Zend\Authentication\AuthenticationServiceInterface to fetch the identity.
     *
     * @param  RequestInterface $request
     * @return mixed
     */
    public function __invoke(RequestInterface $request)
    {
        if (null === $this->authenticationService) {
            return null;
        }

        $identity = $this->authenticationService->getIdentity();

        if (is_object($identity)) {
            if (property_exists($identity, $this->userId)) {
                return $identity->{$this->userId};
            }

            $method = "get" . ucfirst($this->userId);
            if (method_exists($identity, $method)) {
                return $identity->$method();
            }

            return null;
        }

        if (is_array($identity) && isset($identity[$this->userId])) {
            return $identity[$this->userId];
        }

        return null;
    }
}
