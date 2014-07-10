<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Adapter;

use OAuth2\Storage\Pdo as OAuth2Pdo;
use Zend\Crypt\Password\PasswordInterface;
use Zend\Crypt\Password\PasswordAwareInterface;

/**
 * Extension of OAuth2\Storage\PDO that provides Bcrypt client_secret/password
 * encryption
 */
class PdoAdapter extends OAuth2Pdo implements PasswordAwareInterface
{

    /**
     * Password instance
     *
     * @var PasswordInterface
     * @access protected
     */
    private $password = null;

    /**
     * Set password
     *
     * @param  PasswordInterface $password
     * @return self
     * @access public
     */
    public function setPassword(PasswordInterface $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Retrieve password
     *
     * @param void
     * @return null|PasswordInterface
     * @access public
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Check password
     *
     * @param string $user
     * @param string $password
     * @return bool
     */
    protected function checkPassword($user, $password)
    {
        return $this->getPassword()->verify($password, $user['password']);
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
        return $this->getPassword()->verify($client_secret, $result['client_secret']);
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
            $this->getPassword()->create($client_secret);
        }
        // if it exists, update it.
        if ($this->getClientDetails($client_id)) {
            $stmt = $this->db->prepare($sql = sprintf('UPDATE %s SET client_secret=:client_secret, redirect_uri=:redirect_uri, grant_types=:grant_types, scope=:scope, user_id:=user_id where client_id=:client_id', $this->config['client_table']));
        } else {
            $stmt = $this->db->prepare(sprintf('INSERT INTO %s (client_id, client_secret, redirect_uri, grant_types, scope, user_id) VALUES (:client_id, :client_secret, :redirect_uri, :grant_types, :scope, :user_id)', $this->config['client_table']));
        }
        return $stmt->execute(compact('client_id', 'client_secret', 'redirect_uri', 'grant_types', 'scope', 'user_id'));
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
        $this->getPassword()->create($password);

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
