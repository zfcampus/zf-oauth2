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
     * @var string
     */
    private $userId = 'id';

    /**
     *  Set authentication service
     *
     * @param ZendAuthenticationService $service
     * @param array $config
     */
    public function __construct(ZendAuthenticationService $service = null, $config = [])
    {
        $this->authenticationService = $service;

        if (isset($config['zf-oauth2']['user_id'])) {
            $this->userId = $config['zf-oauth2']['user_id'];
        }
    }

    /**
     * Use Zend\Authentication\AuthenticationService to fetch the identity.
     *
     * @param  RequestInterface $request
     * @return mixed
     */
    public function __invoke(RequestInterface $request)
    {
        if (empty($this->authenticationService)) {
            return null;
        }

        $identity = $this->authenticationService->getIdentity();

        if (is_object($identity)) {
            $method = "get" . ucfirst($this->userId);
            $methodVariable = array($identity, $method);
            if (method_exists($identity, $method) && is_callable($methodVariable, false, $callable_name)) {
                return $identity->$method();
            }

            if (property_exists($identity, $this->userId)) {
                return $identity->{$this->userId};
            }

            return null;
        }

        if (is_array($identity) && isset($identity[$this->userId])) {
            return $identity[$this->userId];
        }

        return null;
    }
}
