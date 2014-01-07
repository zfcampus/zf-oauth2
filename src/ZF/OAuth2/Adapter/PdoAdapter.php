<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Adapter;

use OAuth2\Storage\Pdo as OAuth2Pdo;
use Zend\Crypt\Password\Bcrypt;

/**
 * Extension of OAuth2\Storage\PDO that provides Bcrypt client_secret/password
 * encryption
 */
class PdoAdapter extends OAuth2Pdo
{
    const BCRYPT_DEFAULT_COST = '10';
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
        $this->bcrypt = new Bcrypt();
        if (isset($config['bcrypt_cost'])) {
            $this->bcrypt->setCost($config['bcrypt_cost']);
        } else {
            $this->bcrypt->setCost(self::BCRYPT_DEFAULT_COST);
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
        $stmt = $this->db->prepare(sprintf('SELECT * from %s where client_id = :client_id', $this->config['client_table']));
        $stmt->execute(compact('client_id'));
        $result = $stmt->fetch();

        // bcrypt verify
        return $this->bcrypt->verify($client_secret, $result['client_secret']);
    }

    /**
     * Set client details
     *
     * @param string $client_id
     * @param string $client_secret
     * @param string $redirect_uri
     * @param string $grant_types
     * @return bool
     */
    public function setClientDetails($client_id, $client_secret = null, $redirect_uri = null, $grant_types = null, $user_id = null)
    {
        if (!empty($client_secret)) {
            $client_secret = $this->bcrypt->create($client_secret);
        }
        // if it exists, update it.
        if ($this->getClientDetails($client_id)) {
            $stmt = $this->db->prepare($sql = sprintf('UPDATE %s SET client_secret=:client_secret, redirect_uri=:redirect_uri, grant_types=:grant_types where client_id=:client_id', $this->config['client_table']));
        } else {
            $stmt = $this->db->prepare(sprintf('INSERT INTO %s (client_id, client_secret, redirect_uri, grant_types) VALUES (:client_id, :client_secret, :redirect_uri, :grant_types)', $this->config['client_table']));
        }
        return $stmt->execute(compact('client_id', 'client_secret', 'redirect_uri', 'grant_types'));
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
        return $this->bcrypt->verify($password, $user['password']);
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
        $password = $this->bcrypt->create($password);

        // if it exists, update it.
        if ($this->getUser($username)) {
            $stmt = $this->db->prepare(sprintf(
                'UPDATE %s SET password=:password, first_name=:firstName, last_name=:lastName where username=:username',
                $this->config['user_table']
            ));
        } else {
            $stmt = $this->db->prepare(sprintf(
                'INSERT INTO %s (username, password, first_name, last_name) VALUES (:username, :password, :firstName, :lastName)',
                $this->config['user_table']
            ));
        }

        return $stmt->execute(compact('username', 'password', 'firstName', 'lastName'));
    }
}
