<?php

namespace ZF\OAuth2\Adapter;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use OAuth2\OpenID\Storage\UserClaimsInterface as OpenIDUserClaimsInterface;
use OAuth2\OpenID\Storage\AuthorizationCodeInterface as OpenIDAuthorizationCodeInterface;
use OAuth2\Storage\AuthorizationCodeInterface;
use OAuth2\Storage\AccessTokenInterface;
use OAuth2\Storage\ClientCredentialsInterface;
use OAuth2\Storage\UserCredentialsInterface;
use OAuth2\Storage\RefreshTokenInterface;
use OAuth2\Storage\JwtBearerInterface;
use OAuth2\Storage\ScopeInterface;
use OAuth2\Storage\PublicKeyInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Crypt\Password\Bcrypt;
use Exception;
use DateTime;

/**
 * Doctrine storage for OAuth2
 *
 * @author Tom Anderson <tom.h.anderson@gmail.com>
 */
class DoctrineAdapter implements
    AuthorizationCodeInterface,
    AccessTokenInterface,
    ClientCredentialsInterface,
    UserCredentialsInterface,
    RefreshTokenInterface,
    JwtBearerInterface,
    ScopeInterface,
    PublicKeyInterface,
    OpenIDUserClaimsInterface,
    OpenIDAuthorizationCodeInterface,
    ObjectManagerAwareInterface,
    ServiceLocatorAwareInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

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
     * Set the object manager
     *
     * @param ObjectManager $objectManager
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get the object manager
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @var array
     */
    protected $config = array();

    /**
     * Set the config for the entities implementing the interfaces
     *
     * @param config array
     */
    public function setConfig($config)
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }


    /* OAuth2\Storage\ClientCredentialsInterface */
    /**
     * Make sure that the client credentials is valid.
     *
     * @param $client_id
     * Client identifier to be check with.
     * @param $client_secret
     * (optional) If a secret is required, check that they've given the right one.
     *
     * @return
     * TRUE if the client credentials are valid, and MUST return FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-3.1
     *
     * @ingroup oauth2_section_3
     */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        $config = $this->getConfig();
        $doctrineClientIdField = $config['mapping']['ZF\OAuth2\Mapper\Client']['mapping']['client_id']['name'];
        $doctrineClientSecretField = $config['mapping']['ZF\OAuth2\Mapper\Client']['mapping']['client_secret']['name'];

        $client = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\Client']['entity'])
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            return false;
        }

        $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\Client')->reset();
        $mapper->exchangeDoctrineArray($client->getArrayCopy());
        $data = $mapper->getOAuth2ArrayCopy();

        return $this->verifyHash($client_secret, $data['client_secret']);
    }

    /* OAuth2\Storage\ClientCredentialsInterface */
    /**
     * Determine if the client is a "public" client, and therefore
     * does not require passing credentials for certain grant types
     *
     * @param $client_id
     * Client identifier to be check with.
     *
     * @return
     * TRUE if the client is public, and FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-2.3
     * @see https://github.com/bshaffer/oauth2-server-php/issues/257
     *
     * @ingroup oauth2_section_2
     */
    public function isPublicClient($client_id)
    {
        $config = $this->getConfig();
        $doctrineClientIdField = $config['mapping']['ZF\OAuth2\Mapper\Client']['mapping']['client_id']['name'];

        $client = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\Client']['entity'])
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            return false;
        }

        return ($client->getSecret()) ? false: true;
    }


    /* OAuth2\Storage\ClientInterface */
    /**
     * Get client details corresponding client_id.
     *
     * OAuth says we should store request URIs for each registered client.
     * Implement this function to grab the stored URI for a given client id.
     *
     * @param $client_id
     * Client identifier to be check with.
     *
     * @return array
     *               Client details. The only mandatory key in the array is "redirect_uri".
     *               This function MUST return FALSE if the given client does not exist or is
     *               invalid. "redirect_uri" can be space-delimited to allow for multiple valid uris.
     *               <code>
     *               return array(
     *               "redirect_uri" => REDIRECT_URI,      // REQUIRED redirect_uri registered for the client
     *               "client_id"    => CLIENT_ID,         // OPTIONAL the client id
     *               "grant_types"  => GRANT_TYPES,       // OPTIONAL an array of restricted grant types
     *               "user_id"      => USER_ID,           // OPTIONAL the user identifier associated with this client
     *               "scope"        => SCOPE,             // OPTIONAL the scopes allowed for this client
     *               );
     *               </code>
     *
     * @ingroup oauth2_section_4
     */
    public function getClientDetails($client_id)
    {
        $config = $this->getConfig();
        $doctrineClientIdField = $config['mapping']['ZF\OAuth2\Mapper\Client']['mapping']['client_id']['name'];

        $client = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\Client']['entity'])
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            return false;
        }

        $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\Client')->reset();
        $mapper->exchangeDoctrineArray($client->getArrayCopy());
        $data = $mapper->getOAuth2ArrayCopy();

        return $data;
    }

    /* !!!!! OAuth2\Storage\ClientInterface */
    /**
     * This function isn't in the interface but called often
     */
    public function setClientDetails(
        $client_id,
        $client_secret = null,
        $redirect_uri = null,
        $grant_types = null,
        $scope = null,
        $user_id = null
    ) {
        $config = $this->getConfig();
        $doctrineClientIdField = $config['mapping']['ZF\OAuth2\Mapper\Client']['mapping']['client_id']['name'];

        $client = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\Client']['entity'])
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            $client = new $config['mapping']['ZF\OAuth2\Mapper\Client']['entity'];
            $client->setClientId($client_id);
            $this->getObjectManager()->persist($client);
        }

        $scopes = new ArrayCollection;
        foreach ((array) $scope as $scopeString) {
            $scopes->add($this->getObjectManager()
                ->getRepository($config['mapping']['ZF\OAuth2\Mapper\Scope']['entity'])
                ->findOneBy(array(
                    $config['mapping']['ZF\OAuth2\Mapper\Scope']['mapping']['scope']['name'] => $scopeString,
                )));
        }

        $client->exchangeArray(array(
            $config['mapping']['ZF\OAuth2\Mapper\Client']['mapping']['client_secret']['name'] => $client_secret,
            $config['mapping']['ZF\OAuth2\Mapper\Client']['mapping']['redirect_uri']['name'] => $redirect_uri,
            $config['mapping']['ZF\OAuth2\Mapper\Client']['mapping']['grant_types']['name'] => $grant_types,
            $config['mapping']['ZF\OAuth2\Mapper\Client']['mapping']['scope']['name'] => $scopes,
        ));

        $this->getObjectManager()->flush();

        return true;
    }

    /* OAuth2\Storage\ClientInterface */
    /**
     * Check restricted grant types of corresponding client identifier.
     *
     * If you want to restrict clients to certain grant types, override this
     * function.
     *
     * @param $client_id
     * Client identifier to be check with.
     * @param $grant_type
     * Grant type to be check with
     *
     * @return
     * TRUE if the grant type is supported by this client identifier, and
     * FALSE if it isn't.
     *
     * @ingroup oauth2_section_4
     */
    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        $config = $this->getConfig();
        $doctrineClientIdField = $config['mapping']['ZF\OAuth2\Mapper\Client']['mapping']['client_id']['name'];

        $client = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\Client']['entity'])
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            return false;
        }

        if ($client->getGrantType()) {
            return in_array($grant_type, $client->getGrantType());
        }

        // @codeCoverageIgnoreStart
        // if grant_types are not defined, then none are restricted
        return true;
        // @codeCoverageIgnoreEnd
    }

    /* OAuth2\Storage\ClientInterface */
    /**
     * Get the scope associated with this client
     *
     * @return
     * STRING the space-delineated scope list for the specified client_id
     */
    public function getClientScope($client_id)
    {
        $config = $this->getConfig();
        $doctrineClientIdField = $config['mapping']['ZF\OAuth2\Mapper\Client']['mapping']['client_id']['name'];

        $client = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\Client']['entity'])
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            return false;
        }

        $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\Client')->reset();
        $mapper->exchangeDoctrineArray($client->getArrayCopy());
        $data = $mapper->getOAuth2ArrayCopy();

        return $data['scope'];
    }

    /* OAuth2\Storage\AccessTokenInterface */
    /**
     * Look up the supplied oauth_token from storage.
     *
     * We need to retrieve access token data as we create and verify tokens.
     *
     * @param $oauth_token
     * oauth_token to be check with.
     *
     * @return
     * An associative array as below, and return NULL if the supplied oauth_token
     * is invalid:
     * - expires: Stored expiration in unix timestamp.
     * - client_id: (optional) Stored client identifier.
     * - user_id: (optional) Stored user identifier.
     * - scope: (optional) Stored scope values in space-separated string.
     * - id_token: (optional) Stored id_token (if "use_openid_connect" is true).
     *
     * @ingroup oauth2_section_7
     */
    public function getAccessToken($access_token)
    {
        $config = $this->getConfig();
        $doctrineAccessTokenField =
            $config['mapping']['ZF\OAuth2\Mapper\AccessToken']['mapping']['access_token']['name'];

        $accessToken = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\AccessToken']['entity'])
            ->findOneBy(
                array(
                    $doctrineAccessTokenField => $access_token,
                )
            );

        if (!$accessToken) {
            return false;
        }

        $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\AccessToken')->reset();
        $mapper->exchangeDoctrineArray($accessToken->getArrayCopy());
        $data = $mapper->getOAuth2ArrayCopy();

        return $data;
    }

    /* OAuth2\Storage\AccessTokenInterface */
    /**
     * Store the supplied access token values to storage.
     *
     * We need to store access token data as we create and verify tokens.
     *
     * @param $oauth_token    oauth_token to be stored.
     * @param $client_id      client identifier to be stored.
     * @param $user_id        user identifier to be stored.
     * @param int    $expires expiration to be stored as a Unix timestamp.
     * @param string $scope   OPTIONAL Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_4
     */
    public function setAccessToken(
        $access_token,
        $client_id,
        $user_id,
        $expires,
        $scope = null
    ) {

        $config = $this->getConfig();
        $doctrineAccessTokenField =
            $config['mapping']['ZF\OAuth2\Mapper\AccessToken']['mapping']['access_token']['name'];

        $accessToken = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\AccessToken']['entity'])
            ->findOneBy(
                array(
                    $doctrineAccessTokenField => $access_token,
                )
            );

        if (!$accessToken) {
            $entityClass = $config['mapping']['ZF\OAuth2\Mapper\AccessToken']['entity'];

            $accessToken = new $entityClass;
            $this->getObjectManager()->persist($accessToken);
        }

        $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\AccessToken')->reset();
        $mapper->exchangeOAuth2Array(array(
            'access_token' => $access_token,
            'client_id' => $client_id,
            'user_id' => $user_id,
            'expires' => $expires,
            'scope' => $scope,
        ));

        $accessToken->exchangeArray($mapper->getDoctrineArrayCopy());

        $this->getObjectManager()->flush();

        return true;
    }

    /* OAuth2\Storage\AuthorizationCodeInterface */
    /**
     * Fetch authorization code data (probably the most common grant type).
     *
     * Retrieve the stored data for the given authorization code.
     *
     * Required for OAuth2::GRANT_TYPE_AUTH_CODE.
     *
     * @param $code
     * Authorization code to be check with.
     *
     * @return
     * An associative array as below, and NULL if the code is invalid
     * @code
     * return array(
     *     "client_id"    => CLIENT_ID,      // REQUIRED Stored client identifier
     *     "user_id"      => USER_ID,        // REQUIRED Stored user identifier
     *     "expires"      => EXPIRES,        // REQUIRED Stored expiration in unix timestamp
     *     "redirect_uri" => REDIRECT_URI,   // REQUIRED Stored redirect URI
     *     "scope"        => SCOPE,          // OPTIONAL Stored scope values in space-separated string
     * );
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.1
     *
     * @ingroup oauth2_section_4
     */
    public function getAuthorizationCode($code)
    {
        $config = $this->getConfig();

        $doctrineAuthorizationCode =
            $config['mapping']['ZF\OAuth2\Mapper\AuthorizationCode']['mapping']['authorization_code']['name'];
        $doctrineExpiresField =
            $config['mapping']['ZF\OAuth2\Mapper\AuthorizationCode']['mapping']['expires']['name'];

        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder->select('authorizationCode')
            ->from($config['mapping']['ZF\OAuth2\Mapper\AuthorizationCode']['entity'], 'authorizationCode')
            ->andwhere("authorizationCode.$doctrineAuthorizationCode = :code")
            ->andwhere("authorizationCode.$doctrineExpiresField > :now")
            ->setParameter('code', $code)
            ->setParameter('now', new DateTime())
            ;

        try {
            $authorizationCode = $queryBuilder->getQuery()->getSingleResult();
        } catch (Exception $e) {
            $authorizationCode = false;
        }

        if ($authorizationCode) {
            $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\AuthorizationCode')->reset();
            $mapper->exchangeDoctrineArray($authorizationCode->getArrayCopy());

            $authorizationCodeClientAssertion = new \ZF\OAuth2\ClientAssertionType\AuthorizationCode();
            ;
            $authorizationCodeClientAssertion->exchangeArray($mapper->getOAuth2ArrayCopy());

            return $authorizationCodeClientAssertion;
        } else {
            return false;
        }
    }

    /* OAuth2\Storage\AuthorizationCodeInterface */
    /**
     * Take the provided authorization code values and store them somewhere.
     *
     * This function should be the storage counterpart to getAuthCode().
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * Required for OAuth2::GRANT_TYPE_AUTH_CODE.
     *
     * @param string $code         Authorization code to be stored.
     * @param mixed  $client_id    Client identifier to be stored.
     * @param mixed  $user_id      User identifier to be stored.
     * @param string $redirect_uri Redirect URI(s) to be stored in a space-separated string.
     * @param int    $expires      Expiration to be stored as a Unix timestamp.
     * @param string $scope        OPTIONAL Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_4
     */
    public function setAuthorizationCode(
        $code,
        $client_id,
        $user_id,
        $redirect_uri,
        $expires,
        $scope = null,
        $id_token = null
    ) {

        $config = $this->getConfig();
        $doctrineAuthorizationCodeField =
            $config['mapping']['ZF\OAuth2\Mapper\AuthorizationCode']['mapping']['authorization_code']['name'];

        $authorizationCode = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\AuthorizationCode']['entity'])
            ->findOneBy(
                array(
                    $doctrineAuthorizationCodeField => $code,
                )
            );

        if (!$authorizationCode) {
            $entityClass = $config['mapping']['ZF\OAuth2\Mapper\AuthorizationCode']['entity'];

            $authorizationCode= new $entityClass;
            $this->getObjectManager()->persist($authorizationCode);
        }

        $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\AuthorizationCode')->reset();
        $mapper->exchangeOAuth2Array(array(
            'authorization_code' => $code,
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'expires' => $expires,
            'scope' => $scope,
            'id_token' => $id_token,
            'user_id' => $user_id,
        ));

        $authorizationCode->exchangeArray($mapper->getDoctrineArrayCopy());

        $this->getObjectManager()->flush();

        return true;
    }

    /* OAuth2\Storage\AuthorizationCodeInterface */
    /**
     * once an Authorization Code is used, it must be exipired
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.1.2
     *
     *    The client MUST NOT use the authorization code
     *    more than once.  If an authorization code is used more than
     *    once, the authorization server MUST deny the request and SHOULD
     *    revoke (when possible) all tokens previously issued based on
     *    that authorization code
     *
     */
    public function expireAuthorizationCode($code)
    {
        $config = $this->getConfig();
        $doctrineAuthorizationCodeField =
            $config['mapping']['ZF\OAuth2\Mapper\AuthorizationCode']['mapping']['authorization_code']['name'];

        $authorizationCode = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\AuthorizationCode']['entity'])
            ->findOneBy(
                array(
                    $doctrineAuthorizationCodeField => $code,
                )
            );

        if ($authorizationCode) {
            $doctrineExpiresField =
                $config['mapping']['ZF\OAuth2\Mapper\AuthorizationCode']['mapping']['expires']['name'];
            $authorizationCode->exchangeArray(array(
                $doctrineExpiresField => new DateTime(), # maybe subtract 1 second?
            ));

            $this->getObjectManager()->flush();
        }

        return true;
    }

    /* OAuth2\Storage\UserCredentialsInterface */
    /**
     * Grant access tokens for basic user credentials.
     *
     * Check the supplied username and password for validity.
     *
     * You can also use the $client_id param to do any checks required based
     * on a client, if you need that.
     *
     * Required for OAuth2::GRANT_TYPE_USER_CREDENTIALS.
     *
     * @param $username
     * Username to be check with.
     * @param $password
     * Password to be check with.
     *
     * @return
     * TRUE if the username and password are valid, and FALSE if it isn't.
     * Moreover, if the username and password are valid, and you want to
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.3
     *
     * @ingroup oauth2_section_4
     */
    public function checkUserCredentials($username, $password)
    {
        $config = $this->getConfig();
        $doctrineUsernameField = $config['mapping']['ZF\OAuth2\Mapper\User']['mapping']['username']['name'];

        $user = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\User']['entity'])
            ->findOneBy(
                array(
                    $doctrineUsernameField => $username,
                )
            );

        if ($user) {
            $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\User')->reset();
            $mapper->exchangeDoctrineArray($user->getArrayCopy());

            return $this->checkPassword($mapper->getOAuth2ArrayCopy(), $password);
        }

        return false;
    }

    /* OAuth2\Storage\UserCredentialsInterface */
    /**
     * @return
     * ARRAY the associated "user_id" and optional "scope" values
     * This function MUST return FALSE if the requested user does not exist or is
     * invalid. "scope" is a space-separated list of restricted scopes.
     * @code
     * return array(
     *     "user_id"  => USER_ID,    // REQUIRED user_id to be stored with the authorization code or access token
     *     "scope"    => SCOPE       // OPTIONAL space-separated list of restricted scopes
     * );
     * @endcode
     */
    public function getUserDetails($username)
    {
        $config = $this->getConfig();
        $doctrineUsernameField = $config['mapping']['ZF\OAuth2\Mapper\User']['mapping']['username']['name'];

        $user = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\User']['entity'])
            ->findOneBy(
                array(
                    $doctrineUsernameField => $username,
                )
            );

        if ($user) {
            $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\User')->reset();
            $mapper->exchangeDoctrineArray($user->getArrayCopy());

            return $mapper->getOAuth2ArrayCopy();
        }

        return false;
    }

    /* OAuth2\OpenID\Storage\UserClaimsInterface */
    /**
     * Return claims about the provided user id.
     *
     * Groups of claims are returned based on the requested scopes. No group
     * is required, and no claim is required.
     *
     * @param $user_id
     * The id of the user for which claims should be returned.
     * ## Although the spec says id the rest of the class uses username so I changed this to use username
     * @param $scope
     * The requested scope.
     * Scopes with matching claims: profile, email, address, phone.
     *
     * @return
     * An array in the claim => value format.
     *
     * @see http://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
     */
    public function getUserClaims($username, $scope)
    {
        $config = $this->getConfig();
        $doctrineUsernameField = $config['mapping']['ZF\OAuth2\Mapper\User']['mapping']['username']['name'];

        $user = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\User']['entity'])
            ->findOneBy(
                array(
                    $doctrineUsernameField => $username,
                )
            );

        if (!$user) {
            return false;
        }

        switch ($scope) {
            case 'profile':
                return array_intersect_key(
                    $user->getArrayCopy(),
                    array_flip(explode(' ', self::PROFILE_CLAIM_VALUES))
                );
                break;
            case 'email':
                return array_intersect_key(
                    $user->getArrayCopy(),
                    array_flip(explode(' ', self::EMAIL_CLAIM_VALUES))
                );
                break;
            case 'address':
                return array_intersect_key(
                    $user->getArrayCopy(),
                    array_flip(explode(' ', self::ADDRESS_CLAIM_VALUES))
                );
                break;
            case 'phone':
                return array_intersect_key(
                    $user->getArrayCopy(),
                    array_flip(explode(' ', self::PHONE_CLAIM_VALUES))
                );
                break;
            default:
                break;
        }

        return false;
    }

    /* OAuth2\Storage\RefreshTokenInterface */
    /**
     * Grant refresh access tokens.
     *
     * Retrieve the stored data for the given refresh token.
     *
     * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
     *
     * @param $refresh_token
     * Refresh token to be check with.
     *
     * @return
     * An associative array as below, and NULL if the refresh_token is
     * invalid:
     * - refresh_token: Refresh token identifier.
     * - client_id: Client identifier.
     * - user_id: User identifier.
     * - expires: Expiration unix timestamp, or 0 if the token doesn't expire.
     * - scope: (optional) Scope values in space-separated string.
     *
     * @see http://tools.ietf.org/html/rfc6749#section-6
     *
     * @ingroup oauth2_section_6
     */
    # If expired return null
    public function getRefreshToken($refresh_token)
    {
        $config = $this->getConfig();
        $doctrineRefreshTokenField =
            $config['mapping']['ZF\OAuth2\Mapper\RefreshToken']['mapping']['refresh_token']['name'];
        $doctrineExpiresField =
            $config['mapping']['ZF\OAuth2\Mapper\RefreshToken']['mapping']['expires']['name'];

        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder->select('refreshToken')
            ->from($config['mapping']['ZF\OAuth2\Mapper\RefreshToken']['entity'], 'refreshToken')
            ->andwhere("refreshToken.$doctrineRefreshTokenField = :token")
            ->andwhere("refreshToken.$doctrineExpiresField > :now")
            ->setParameter('token', $refresh_token)
            ->setParameter('now', new DateTime())
            ;

        try {
            $refreshToken = $queryBuilder->getQuery()->getSingleResult();

            $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\RefreshToken')->reset();
            $mapper->exchangeDoctrineArray($refreshToken->getArrayCopy());

            return $mapper->getOAuth2ArrayCopy();
        } catch (Exception $e) {
            // no result ok
        }

        return false;
    }

    /* OAuth2\Storage\RefreshTokenInterface */
    /**
     * Take the provided refresh token values and store them somewhere.
     *
     * This function should be the storage counterpart to getRefreshToken().
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
     *
     * @param $refresh_token
     * Refresh token to be stored.
     * @param $client_id
     * Client identifier to be stored.
     * @param $user_id
     * User identifier to be stored.
     * @param $expires
     * Expiration timestamp to be stored. 0 if the token doesn't expire.
     * @param $scope
     * (optional) Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_6
     */
    public function setRefreshToken(
        $refresh_token,
        $client_id,
        $user_id,
        $expires,
        $scope = null
    ) {
        $config = $this->getConfig();
        $doctrineRefreshTokenField =
            $config['mapping']['ZF\OAuth2\Mapper\RefreshToken']['mapping']['refresh_token']['name'];

        $refreshToken= $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\RefreshToken']['entity'])
            ->findOneBy(
                array(
                    $doctrineRefreshTokenField => $refresh_token,
                )
            );

        if (!$refreshToken) {
            $entityClass = $config['mapping']['ZF\OAuth2\Mapper\RefreshToken']['entity'];

            $refreshToken= new $entityClass;
            $this->getObjectManager()->persist($refreshToken);
        }

        $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\RefreshToken')->reset();
        $mapper->exchangeOAuth2Array(array(
            'refresh_token' => $refresh_token,
            'client_id' => $client_id,
            'user_id' => $user_id,
            'expires' => $expires,
            'scope' => $scope,
            'user_id' => $user_id,
        ));

        $scopes = new ArrayCollection;
        foreach ((array) $scope as $scopeString) {
            $scopes->add($this->getObjectManager()
                ->getRepository($config['mapping']['ZF\OAuth2\Mapper\Scope']['entity'])
                ->findOneBy(array(
                    $config['mapping']['ZF\OAuth2\Mapper\Scope']['mapping']['scope']['name'] => $scopeString,
                )));
        }

        $refreshToken->exchangeArray($mapper->getDoctrineArrayCopy());

        $this->getObjectManager()->flush();

        return true;
    }

    /* OAuth2\Storage\RefreshTokenInterface */
    /**
     * Expire a used refresh token.
     *
     * This is not explicitly required in the spec, but is almost implied.
     * After granting a new refresh token, the old one is no longer useful and
     * so should be forcibly expired in the data store so it can't be used again.
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * @param $refresh_token
     * Refresh token to be expirse.
     *
     * @ingroup oauth2_section_6
     */
    public function unsetRefreshToken($refresh_token)
    {
        $config = $this->getConfig();
        $doctrineRefreshTokenCodeField =
            $config['mapping']['ZF\OAuth2\Mapper\RefreshToken']['mapping']['refresh_token']['name'];

        $refreshToken = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\RefreshToken']['entity'])
            ->findOneBy(
                array(
                    $doctrineRefreshTokenCodeField => $refresh_token,
                )
            );

        if ($refreshToken) {
            $doctrineExpiresField = $config['mapping']['ZF\OAuth2\Mapper\RefreshToken']['mapping']['expires']['name'];
            $refreshToken ->exchangeArray(array(
                $doctrineExpiresField => new DateTime(), # maybe subtract 1 second?
            ));

            $this->getObjectManager()->flush();
        }

        return true;
    }

    /* OAuth2\Storage\ScopeInterface */
    /**
     * Check if the provided scope exists.
     *
     * @param $scope
     * A space-separated string of scopes.
     *
     * @return
     * TRUE if it exists, FALSE otherwise.
     */
    public function scopeExists($scope)
    {
        $config = $this->getConfig();
        $scopeArray = explode(' ', $scope);

        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder
            ->select('scope')
            ->from($config['mapping']['ZF\OAuth2\Mapper\Scope']['entity'], 'scope')
            ->andwhere(
                $queryBuilder->expr()->in('scope.scope', $scopeArray)
            )
            ;

        $result = $queryBuilder->getQuery()->getResult();

        return sizeof($result) == sizeof($scopeArray);
    }

    /* OAuth2\Storage\ScopeInterface */
    /**
     * The default scope to use in the event the client
     * does not request one. By returning "false", a
     * request_error is returned by the server to force a
     * scope request by the client. By returning "null",
     * opt out of requiring scopes
     *
     * @param $client_id
     * An optional client id that can be used to return customized default scopes.
     *
     * @return
     * string representation of default scope, null if
     * scopes are not defined, or false to force scope
     * request by the client
     *
     * ex:
     *     'default'
     * ex:
     *     null
     */
    public function getDefaultScope($client_id = null)
    {
        $config = $this->getConfig();
        $doctrineScopeIsDefaultField = $config['mapping']['ZF\OAuth2\Mapper\Scope']['mapping']['is_default']['name'];

        $scope = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\Scope']['entity'])
            ->findBy(
                array(
                    $doctrineScopeIsDefaultField => true,
                )
            );

        $return = array();
        foreach ($scope as $s) {
            $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\Scope')->reset();
            $mapper->exchangeDoctrineArray($s->getArrayCopy());
            $data = $mapper->getOAuth2ArrayCopy();

            $return[] = $data['scope'];
        }

        return implode(' ', $return);
    }

    /* OAuth2\Storage\JWTBearerInterface */
    /**
     * Get the public key associated with a client_id
     *
     * @param $client_id
     * Client identifier to be checked with.
     *
     * @return
     * STRING Return the public key for the client_id if it exists, and MUST return FALSE if it doesn't.
     */
    public function getClientKey($client_id, $subject)
    {
        $config = $this->getConfig();
        $doctrineClientIdField = $config['mapping']['ZF\OAuth2\Mapper\Client']['mapping']['client_id']['name'];

        $client = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\Client']['entity'])
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            return false;
        }

        $doctrineClientIdField = $config['mapping']['ZF\OAuth2\Mapper\Jwt']['mapping']['client_id']['name'];
        $doctrineSubjectField = $config['mapping']['ZF\OAuth2\Mapper\Jwt']['mapping']['subject']['name'];

        try {
            $jwt = $this->getObjectManager()
                ->getRepository($config['mapping']['ZF\OAuth2\Mapper\Jwt']['entity'])
                ->findOneBy(
                    array(
                        $doctrineClientIdField => $client,
                        $doctrineSubjectField => $subject,
                    )
                );
        } catch (Exception $e) {
            // No result ok
        }

        if (!$jwt) {
            return false;
        }

        $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\Jwt')->reset();
        $mapper->exchangeDoctrineArray($jwt->getArrayCopy());
        $data = $mapper->getOAuth2ArrayCopy();

        return ($data['public_key']) ?: false;
    }

    /* OAuth2\Storage\JwtBearerInterface */
    /**
     * Get a jti (JSON token identifier) by matching against the client_id, subject, audience and expiration.
     *
     * @param $client_id
     * Client identifier to match.
     *
     * @param $subject
     * The subject to match.
     *
     * @param $audience
     * The audience to match.
     *
     * @param $expiration
     * The expiration of the jti.
     *
     * @param $jti
     * The jti to match.
     *
     * @return
     * An associative array as below, and return NULL if the jti does not exist.
     * - issuer: Stored client identifier.
     * - subject: Stored subject.
     * - audience: Stored audience.
     * - expires: Stored expiration in unix timestamp.
     * - jti: The stored jti.
     */
    public function getJti(
        $client_id,
        $subject,
        $audience,
        $expiration,
        $jti
    ) {
        $config = $this->getConfig();
        $doctrineClientIdField = $config['mapping']['ZF\OAuth2\Mapper\Jti']['mapping']['client_id']['name'];
        $doctrineSubjectField = $config['mapping']['ZF\OAuth2\Mapper\Jti']['mapping']['subject']['name'];
        $doctrineAudienceField = $config['mapping']['ZF\OAuth2\Mapper\Jti']['mapping']['audience']['name'];
        $doctrineExpirationField = $config['mapping']['ZF\OAuth2\Mapper\Jti']['mapping']['expiration']['name'];
        $doctrineJtiField = $config['mapping']['ZF\OAuth2\Mapper\Jti']['mapping']['jti']['name'];

        $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\Jti')->reset();
        $mapper->exchangeOAuth2Array(array(
            'client_id' => $client_id,
            'subject' => $subject,
            'audience' => $audience,
            'expiration' => $expiration,
            'jti' => $jti,
        ));

        // Fetch doctrine array and filter for parameter values
        $query = $mapper->getDoctrineArrayCopy();

        $jti= $this->getObjectManager()->getRepository($config['mapping']['ZF\OAuth2\Mapper\Jti']['entity'])
            ->findOneBy($query);

        if (!$jti) {
            return false;
        }

        $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\Jti')->reset();
        $mapper->exchangeDoctrineArray($jti->getArrayCopy());

        return $mapper->getOAuth2ArrayCopy();
    }

    /* OAuth2\Storage\JwtBearerInterface */
    /**
     * Store a used jti so that we can check against it to prevent replay attacks.
     * @param $client_id
     * Client identifier to insert.
     *
     * @param $subject
     * The subject to insert.
     *
     * @param $audience
     * The audience to insert.
     *
     * @param $expiration
     * The expiration of the jti.
     *
     * @param $jti
     * The jti to insert.
     */
    public function setJti($client_id, $subject, $audience, $expiration, $jti)
    {
        $config = $this->getConfig();
        $jtiEntity = new $config['mapping']['ZF\OAuth2\Mapper\Jti']['entity'];

        $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\Jti')->reset();
        $mapper->exchangeOAuth2Array(array(
            'client_id'  => $client_id,
            'subject'    => $subject,
            'audience'   => $audience,
            'expiration' => $expiration,
            'jti'        => $jti,
        ));

        $jtiEntity->exchangeArray($mapper->getDoctrineArrayCopy());

        $this->getObjectManager()->persist($jtiEntity);
        $this->getObjectManager()->flush();

        return true;
    }

    /* OAuth2\Storate\PublicKeyInterface */
    public function getPublicKey($client_id = null)
    {
        $config = $this->getConfig();
        $doctrineClientIdField = $config['mapping']['ZF\OAuth2\Mapper\Client']['mapping']['client_id']['name'];

        $client = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\Client']['entity'])
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            return false;
        }

        $publicKey = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\PublicKey']['entity'])
            ->findOneBy(
                array(
                    $config['mapping']['ZF\OAuth2\Mapper\PublicKey']['mapping']['client_id']['name'] => $client,
                )
            );

        if (!$publicKey) {
            return false;
        }

        $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\PublicKey')->reset();
        $mapper->exchangeDoctrineArray($publicKey->getArrayCopy());

        $publicKeyOAuth2 = $mapper->getOAuth2ArrayCopy();

        return $publicKeyOAuth2['public_key'];
    }

    /* OAuth2\Storate\PublicKeyInterface */
    public function getPrivateKey($client_id = null)
    {
        $config = $this->getConfig();
        $doctrineClientIdField = $config['mapping']['ZF\OAuth2\Mapper\Client']['mapping']['client_id']['name'];

        $client = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\Client']['entity'])
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            return false;
        }

        $publicKey = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\PublicKey']['entity'])
            ->findOneBy(
                array(
                    $config['mapping']['ZF\OAuth2\Mapper\PublicKey']['mapping']['client_id']['name'] => $client,
                )
            );

        if (!$publicKey) {
            return false;
        }

        $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\PublicKey')->reset();
        $mapper->exchangeDoctrineArray($publicKey->getArrayCopy());

        $publicKeyOAuth2 = $mapper->getOAuth2ArrayCopy();

        return $publicKeyOAuth2['private_key'];
    }

    /* OAuth2\Storate\PublicKeyInterface */
    public function getEncryptionAlgorithm($client_id = null)
    {
        $config = $this->getConfig();
        $doctrineClientIdField = $config['mapping']['ZF\OAuth2\Mapper\Client']['mapping']['client_id']['name'];

        $client = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\Client']['entity'])
            ->findOneBy(
                array(
                    $doctrineClientIdField => $client_id,
                )
            );

        if (!$client) {
            return false;
        }

        $publicKey = $this->getObjectManager()
            ->getRepository($config['mapping']['ZF\OAuth2\Mapper\PublicKey']['entity'])
            ->findOneBy(
                array(
                    $config['mapping']['ZF\OAuth2\Mapper\PublicKey']['mapping']['client_id']['name'] => $client,
                )
            );

        if (!$publicKey) {
            return false;
        }

        $mapper = $this->getServiceLocator()->get('ZF\OAuth2\Mapper\PublicKey')->reset();
        $mapper->exchangeDoctrineArray($publicKey->getArrayCopy());

        $publicKeyOAuth2 = $mapper->getOAuth2ArrayCopy();

        return $publicKeyOAuth2['encryption_algorithm'];
    }
}
