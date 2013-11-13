<?php

namespace ZF\OAuth2\Adapter;

use OAuth2\Storage\Pdo as OAuth2Pdo;
use Zend\Crypt\Password\Bcrypt;

/**
 * Extension class of OAuth2\Storage\PDO with Bcrypt client_secret/password encryption
 */
class Pdo extends OAuth2Pdo
{
    protected $bcrypt;

    public function __construct($connection, $config = array())
    {
        parent::__construct($connection, $config);
        $this->bcrypt = new Bcrypt();
    }

    /* OAuth2_Storage_ClientCredentialsInterface */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        $stmt = $this->db->prepare(sprintf('SELECT * from %s where client_id = :client_id', $this->config['client_table']));
        $stmt->execute(compact('client_id'));
        $result = $stmt->fetch();

        // bcrypt verify
        return $this->bcrypt->verify($client_secret, $result['client_secret']);
    }

    public function setClientDetails($client_id, $client_secret = null, $redirect_uri = null, $grant_types = null)
    {
        // if it exists, update it.
        if ($this->getClientDetails($client_id)) {
            if (!empty($client_secret)) {
                $client_secret = $this->brcypt->create($client_secret);
            }
            $stmt = $this->db->prepare($sql = sprintf('UPDATE %s SET client_secret=:client_secret, redirect_uri=:redirect_uri, grant_types=:grant_types where client_id=:client_id', $this->config['client_table']));
        } else {
            $stmt = $this->db->prepare(sprintf('INSERT INTO %s (client_id, client_secret, redirect_uri, grant_types) VALUES (:client_id, :client_secret, :redirect_uri, :grant_types)', $this->config['client_table']));
        }
        return $stmt->execute(compact('client_id', 'client_secret', 'redirect_uri', 'grant_types'));
    }

    // check password using bcrypt
    protected function checkPassword($user, $password)
    {
        return $this->bcrypt($user['password'], $password);
    }

    public function setUser($username, $password, $firstName = null, $lastName = null)
    {
        // do not store in plaintext, use bcrypt
        $password = $this->bcrypt->create($password);

        // if it exists, update it.
        if ($this->getUser($username)) {
            $stmt = $this->db->prepare($sql = sprintf('UPDATE %s SET password=:password, first_name=:firstName, last_name=:lastName where username=:username', $this->config['user_table']));
        } else {
            $stmt = $this->db->prepare(sprintf('INSERT INTO %s (username, password, first_name, last_name) VALUES (:username, :password, :firstName, :lastName)', $this->config['user_table']));
        }
        return $stmt->execute(compact('username', 'password', 'firstName', 'lastName'));
    }

}
