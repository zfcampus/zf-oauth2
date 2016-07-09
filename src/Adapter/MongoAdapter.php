<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Adapter;

use MongoClient;
use OAuth2\Storage\Mongo as OAuth2Mongo;
use Zend\Crypt\Password\Bcrypt;

/**
 * Extension of OAuth2\Storage\PDO that provides Bcrypt client_secret/password
 * encryption
 */
class MongoAdapter extends OAuth2Mongo
{
    /**
     * @var int
     */
    protected $bcryptCost = 10;

    /**
     * @var Bcrypt
     */
    protected $bcrypt;

    /**
     * @return Bcrypt
     */
    public function getBcrypt()
    {
        if (null === $this->bcrypt) {
            $this->bcrypt = new Bcrypt();
            $this->bcrypt->setCost($this->bcryptCost);
        }

        return $this->bcrypt;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setBcryptCost($value)
    {
        $this->bcryptCost = (int) $value;
        return $this;
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
        return $this->verifyHash($password, $user['password']);
    }

    /**
     * @param $string
     */
    protected function createBcryptHash(&$string)
    {
        $string = $this->getBcrypt()->create($string);
    }

    /**
     * Check hash using bcrypt
     *
     * @param $hash
     * @param $check
     * @return bool
     */
    protected function verifyHash($check, $hash)
    {
        return $this->getBcrypt()->verify($check, $hash);
    }

    /**
     * @param $connection
     * @param array $config
     * @throws Exception\RuntimeException
     */
    public function __construct($connection, $config = [])
    {
        // @codeCoverageIgnoreStart
        $useMongoDb = defined('HHVM_VERSION') || version_compare(PHP_VERSION, '7.0', '>=');
        if (! extension_loaded($useMongoDb ? 'mongodb' : 'mongo')
            || ! class_exists(MongoClient::class)
            || version_compare(MongoClient::VERSION, '1.4.1', '<')
        ) {
            throw new Exception\RuntimeException(
                'The MongoAdapter requires either the Mongo Driver v1.4.1 or '
                . 'ext/mongodb + the alcaeus/mongo-php-adapter package (which provides '
                . 'backwards compatibility for ext/mongo classes)'
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
            return $this->verifyHash($client_secret, $result['client_secret']);
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
    public function setClientDetails(
        $client_id,
        $client_secret = null,
        $redirect_uri = null,
        $grant_types = null,
        $scope_or_user_id = null,
        $user_id = null
    ) {
        if (func_num_args() > 5) {
            $scope = $scope_or_user_id;
        } else {
            $user_id = $scope_or_user_id;
            $scope   = null;
        }

        if (!empty($client_secret)) {
            $this->createBcryptHash($client_secret);
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
        $this->createBcryptHash($password);

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
