<?php
/**
 * @copyright Copyright (c) 2016 João Dias <mail@joaodias.eu>
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

namespace ZFTest\OAuth2\Controller;

use OAuth2\Storage\Memory;
use ZF\ApiProblem\Exception\DomainException;

class CustomAdapter extends Memory
{
    public function checkUserCredentials($username, $password)
    {
        // mocking logic to throw an exception if the user is banned
        if ($username === 'banned_user') {
            $loginException = new DomainException('User is banned', 401);
            $loginException->setTitle('banned');
            $loginException->setType('http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html');
            throw $loginException;
        }

        return parent::checkUserCredentials($username, $password);
    }

    public function isPublicClient($client_id)
    {
        return true;
    }
}
