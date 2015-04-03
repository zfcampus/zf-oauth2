<?php

namespace ZF\OAuth2\Adapter;

use OAuth2\Storage\IbmDb2 as OAuth2Db2;
use Zend\Crypt\Password\Bcrypt;

/**
 * Extension of OAuth2\Storage\IbmDb2 that provides Bcrypt client_secret/password
 * encryption
 */
class IbmDb2Adapter extends OAuth2Db2
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
     * @param string $connection
     * @param array $config
     */
    public function __construct($connection, $config = array())
    {
        parent::__construct($connection, $config);
        if (isset($config['bcrypt_cost'])) {
            $this->setBcryptCost($config['bcrypt_cost']);
        }
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
        $stmt = $this->db->prepare(sprintf(
            'SELECT * from %s where client_id = ?',
            $this->config['client_table']
        ));
        $successfulExecute = db2_execute($stmt, compact('client_id'));
        $result = $stmt->fetch();

        // bcrypt verify
        return $this->verifyHash($client_secret, $result['client_secret']);
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
        // if it exists, update it.
        if ($this->getClientDetails($client_id)) {
            $stmt = db2_prepare($this->db, sprintf(
                'UPDATE %s '
                . 'SET '
                . 'client_secret=?, '
                . 'redirect_uri=?, '
                . 'grant_types=?, '
                . 'scope=?, '
                . 'user_id=? '
                . 'WHERE client_id=?',
                $this->config['client_table']
            ));
        } else {
            $stmt = db2_prepare($this->db, sprintf(
                'INSERT INTO %s (client_id, client_secret, redirect_uri, grant_types, scope, user_id) '
                . 'VALUES (?, ?, ?, ?, ?, ?)',
                $this->config['client_table']
            ));
        }
        return db2_execute($stmt, compact('client_id', 'client_secret', 'redirect_uri', 'grant_types', 'scope', 'user_id'));
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
        // do not store in plaintext, use bcrypt
        $this->createBcryptHash($password);

        // if it exists, update it.
        if ($this->getUser($username)) {
            $stmt = $this->db->prepare(sprintf(
                'UPDATE %s SET password=?, first_name=?, last_name=? where username=?',
                $this->config['user_table']
            ));
        } else {
            $stmt = db2_prepare($this->db,sprintf(
                'INSERT INTO %s (username, password, first_name, last_name) '
                . 'VALUES (?, ?, ?, ?)',
                $this->config['user_table']
            ));
        }

        return db2_execute($stmt, compact('username', 'password', 'firstName', 'lastName'));
    }
}
