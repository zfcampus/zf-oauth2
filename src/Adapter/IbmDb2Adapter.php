<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Adapter;

use OAuth2\Storage\IbmDb2 as OAuth2Db2;
use RuntimeException;
use Zend\Crypt\Password\Bcrypt;

/**
 * Extension of OAuth2\Storage\IbmDb2 that provides Bcrypt client_secret/password
 * encryption
 *
 * @author Alan Seiden <alan at alanseiden dot com> (of IbmDb2 changes)
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
     * @param string $string
     * @return string
     */
    protected function createBcryptHash($string)
    {
        return $this->getBcrypt()->create($string);
    }

    /**
     * Check hash using bcrypt
     *
     * @param string $hash
     * @param string $check
     * @return bool
     */
    protected function verifyHash($check, $hash)
    {
        return $this->getBcrypt()->verify($check, $hash);
    }

    /**
     * Check client credentials
     *
     * @param string $clientId
     * @param null|string $clientSecret
     * @return bool
     */
    public function checkClientCredentials($clientId, $clientSecret = null)
    {
        $stmt = db2_prepare($this->db, sprintf(
            'SELECT * from %s where client_id = ?',
            $this->config['client_table']
        ));
        if (false == $stmt) {
            throw new \Exception(db2_stmt_errormsg());
        }

        $successfulExecute = db2_execute($stmt, compact('clientId'));
        $result = db2_fetch_assoc($stmt);

        // bcrypt verify
        return $this->verifyHash($clientSecret, $result['client_secret']);
    }

    /**
     * Set client details
     *
     * @param string $clientId
     * @param null|string $clientSecret
     * @param null|string $redirectUri
     * @param null|string $grantTypes
     * @param null|string $scopeOrUserId If 5 arguments, userId; if 6, scope.
     * @param null|string $userId
     * @return bool
     */
    public function setClientDetails(
        $clientId,
        $clientSecret = null,
        $redirectUri = null,
        $grantTypes = null,
        $scopeOrUserId = null,
        $userId = null
    ) {
        if (func_num_args() > 5) {
            $scope = $scopeOrUserId;
        } else {
            $userId = $scopeOrUserId;
            $scope  = null;
        }

        if (! empty($clientSecret)) {
            $clientSecret = $this->createBcryptHash($clientSecret);
        }
        // if it exists, update it.
        if ($this->getClientDetails($clientId)) {
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
            $params = compact('clientSecret', 'redirectUri', 'grantTypes', 'scope', 'userId', 'clientId');

        } else {
            $stmt = db2_prepare($this->db, sprintf(
                'INSERT INTO %s (client_id, client_secret, redirect_uri, grant_types, scope, user_id) '
                . 'VALUES (?, ?, ?, ?, ?, ?)',
                $this->config['client_table']
            ));
            $params = compact('clientId', 'clientSecret', 'redirectUri', 'grantTypes', 'scope', 'userId');

        }
        if (false === $stmt) {
            throw new RuntimeException(db2_stmt_errormsg());
        }
        return db2_execute($stmt, $params);
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
        $password = $this->createBcryptHash($password);

        // if it exists, update it.
        if ($this->getUser($username)) {
            $stmt = db2_prepare($this->db, sprintf(
                'UPDATE %s SET password=?, first_name=?, last_name=? where username=?',
                $this->config['user_table']
            ));
            $params = compact('password', 'firstName', 'lastName', 'username');
        } else {
            $stmt = db2_prepare($this->db, sprintf(
                'INSERT INTO %s (username, password, first_name, last_name) '
                . 'VALUES (?, ?, ?, ?)',
                $this->config['user_table']
            ));
            $params = compact('username', 'password', 'firstName', 'lastName');
        }
        if (false === $stmt) {
            throw new RuntimeException(db2_stmt_errormsg());
        }

        return db2_execute($stmt, $params);
    }
}
