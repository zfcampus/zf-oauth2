<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Provider\UserId;

use Zend\Stdlib\RequestInterface;

class Request implements UserIdProviderInterface
{
    /**
     * Use the composed request to fetch the identity from the query string
     * argument "user_id".
     *
     * @param RequestInterface $requst
     * @return mixed
     */
    public function __invoke(RequestInterface $request)
    {
        return $request->getQuery('user_id', null);
    }
}
