<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Adapter;

use OAuth2\Storage\Mongo as OAuth2Mongo;


/**
 * Extension of OAuth2\Storage\PDO that provides Bcrypt client_secret/password
 * encryption
 */
class MongoAdapter extends OAuth2Mongo
{
    use BcryptTrait;

    /**
     * @param $connection
     * @param array $config
     * @throws Exception\RuntimeException
     */
    public function __construct($connection, $config = [])
    {
        // @codeCoverageIgnoreStart
        if (!extension_loaded('mongo') || version_compare('1.4.1', \MongoClient::VERSION, '<')) {
            throw new Exception\RuntimeException(
                'The Mongo Driver v1.4.1 required for this adapter to work'
            );
        }
        // @codeCoverageIgnoreEnd

        parent::__construct($connection, $config);

    }

    /**
     * Check client credentials
     *
     * @param string $client_id
     * @param string $client_secret
     * @return bool
     */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        if ($result = $this->collection('client_table')->findOne(['client_id' => $client_id])) {
            return $this->getBcrypt()->verify($client_secret, $result['client_secret']);
        }

        return false;
    }

    /**
     * Set client details
     *
     * @param string $client_id
     * @param string $client_secret
     * @param string $redirect_uri
     * @param string $grant_types
     * @param string $scope_or_user_id If 5 arguments, user_id; if 6, scope.
     * @param string $user_id
     * @return bool
     */
    public function setClientDetails($client_id, $client_secret = null, $redirect_uri = null, $grant_types = null, $scope_or_user_id = null, $user_id = null)
    {
        if (func_num_args() > 5) {
            $scope = $scope_or_user_id;
        } else {
            $user_id = $scope_or_user_id;
            $scope   = null;
        }

        if (!empty($client_secret)) {
            $client_secret = $$this->getBcrypt()->create($client_secret);
        }

        if ($this->getClientDetails($client_id)) {
            $this->collection('client_table')->update(
                ['client_id' => $client_id],
                ['$set' => [
                    'client_secret' => $client_secret,
                    'redirect_uri'  => $redirect_uri,
                    'grant_types'   => $grant_types,
                    'scope'         => $scope,
                    'user_id'       => $user_id,
                ]]
            );
        } else {
            $this->collection('client_table')->insert(
                [
                    'client_id'     => $client_id,
                    'client_secret' => $client_secret,
                    'redirect_uri'  => $redirect_uri,
                    'grant_types'   => $grant_types,
                    'scope'         => $scope,
                    'user_id'       => $user_id,
                ]
            );
        }

        return true;
    }


    /**
     * Check password using bcrypt
     *
     * @param string $user
     * @param string $password
     * @return bool
     */
    protected function checkPassword($user, $password)
    {
        return $this->getBcrypt()->verify($password, $user['password']);
    }

    /**
     * Set the user
     *
     * @param string $username
     * @param string $password
     * @param string $firstName
     * @param string $lastName
     * @return bool
     */
    public function setUser($username, $password, $firstName = null, $lastName = null)
    {
        $password = $this->getBcrypt()->create($password);

        if ($this->getUser($username)) {
            $this->collection('user_table')->update(
                ['username' => $username],
                ['$set' => [
                    'password'   => $password,
                    'first_name' => $firstName,
                    'last_name'  => $lastName
                ]]
            );
        } else {
            $this->collection('user_table')->insert([
                'username'   => $username,
                'password'   => $password,
                'first_name' => $firstName,
                'last_name'  => $lastName
            ]);
        }

        return true;
    }
}
