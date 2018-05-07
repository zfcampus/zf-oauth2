# zf-oauth2

[![Build Status](https://secure.travis-ci.org/zfcampus/zf-oauth2.svg?branch=master)](https://secure.travis-ci.org/zfcampus/zf-oauth2)
[![Coverage Status](https://coveralls.io/repos/github/zfcampus/zf-oauth2/badge.svg?branch=master)](https://coveralls.io/github/zfcampus/zf-oauth2?branch=master)

ZF module for [OAuth2](http://oauth.net/2/) authentication.

This module uses the [oauth2-server-php](https://github.com/bshaffer/oauth2-server-php)
library by Brent Shaffer to provide OAuth2 support.

## Requirements
  
Please see the [composer.json](composer.json) file.

## Installation

You can install using:

```bash
$ composer require zfcampus/zf-oauth2
```

If you are using ext/mongodb, you will also need to install a compatibility
package:

```bash
$ composer require alcaeus/mongo-php-adapter
```

Finally, you will need to add the following modules to your application's
configuration:

```php
'modules' => [
    /* ... */
    'ZF\ApiProblem',
    'ZF\ContentNegotiation',
    'ZF\OAuth2',
],
```

> ### zf-component-installer
>
> If you use [zf-component-installer](https://github.com/zendframework/zf-component-installer),
> that plugin will install zf-oauth2 and its other Apigility dependencies as
> modules for you.

## Configuration

This module uses any PDO-suported database to manage the OAuth2 information
(users, client, token, etc).  The database structure is stored in
`data/db_oauth2.sql`.

```sql
CREATE TABLE oauth_clients (
    client_id VARCHAR(80) NOT NULL,
    client_secret VARCHAR(80) NOT NULL,
    redirect_uri VARCHAR(2000) NOT NULL,
    grant_types VARCHAR(80),
    scope VARCHAR(2000),
    user_id VARCHAR(255),
    CONSTRAINT clients_client_id_pk PRIMARY KEY (client_id)
);
CREATE TABLE oauth_access_tokens (
    access_token VARCHAR(40) NOT NULL,
    client_id VARCHAR(80) NOT NULL,
    user_id VARCHAR(255),
    expires TIMESTAMP NOT NULL,
    scope VARCHAR(2000),
    CONSTRAINT access_token_pk PRIMARY KEY (access_token)
);
CREATE TABLE oauth_authorization_codes (
    authorization_code VARCHAR(40) NOT NULL,
    client_id VARCHAR(80) NOT NULL,
    user_id VARCHAR(255),
    redirect_uri VARCHAR(2000),
    expires TIMESTAMP NOT NULL,
    scope VARCHAR(2000),
    id_token VARCHAR(2000),
    CONSTRAINT auth_code_pk PRIMARY KEY (authorization_code)
);
CREATE TABLE oauth_refresh_tokens (
    refresh_token VARCHAR(40) NOT NULL,
    client_id VARCHAR(80) NOT NULL,
    user_id VARCHAR(255),
    expires TIMESTAMP NOT NULL,
    scope VARCHAR(2000),
    CONSTRAINT refresh_token_pk PRIMARY KEY (refresh_token)
);
CREATE TABLE oauth_users (
    username VARCHAR(255) NOT NULL,
    password VARCHAR(2000),
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    CONSTRAINT username_pk PRIMARY KEY (username)
);
CREATE TABLE oauth_scopes (
    type VARCHAR(255) NOT NULL DEFAULT "supported",
    scope VARCHAR(2000),
    client_id VARCHAR (80),
    is_default SMALLINT DEFAULT NULL
);
CREATE TABLE oauth_jwt (
    client_id VARCHAR(80) NOT NULL,
    subject VARCHAR(80),
    public_key VARCHAR(2000),
    CONSTRAINT jwt_client_id_pk PRIMARY KEY (client_id)
);
```

> ### PostgreSQL
>
> We also have a PostgreSQL-specific DDL in `data/db_oauth2_postgresql.sql`.

For security reasons, we encrypt the fields `client_secret` (table
`oauth_clients`) and `password` (table `oauth_users`) using the
[bcrypt](http://en.wikipedia.org/wiki/Bcrypt) algorithm (via the class
[Zend\Crypt\Password\Bcrypt](http://framework.zend.com/manual/2.2/en/modules/zend.crypt.password.html#bcrypt)).

In order to configure the zf-oauth2 module for database access, you need to copy
the file `config/oauth2.local.php.dist` to `config/autoload/oauth2.local.php` in
your ZF2 application, and edit it to provide your DB credentials (DNS, username,
password).

We also include a SQLite database in `data/dbtest.sqlite` that you can use in a
test environment.  In this database, you will find a test client account with
the `client_id` "testclient" and the `client_secret` "testpass".  If you want to
use this database, you can configure your `config/autoload/oauth2.local.php`
file as follow:

```php
return array(
    'zf-oauth2' => array(
        'db' => array(
            'dsn' => 'sqlite:<path to zf-oauth2 module>/data/dbtest.sqlite',
        ),
    ),
);
```

## Mongo Configuration

The Mongo OAuth2 adapter wraps the bshaffer adapter by adding the same password encryption
as the rest of apigility.  The collections needed are the same as above in the PDO
adapter.  To create an OAuth2 client, insert a document like the following into the
oauth_clients collection:

```javascript
{
    "client_id":     "testclient",
    "client_secret": "$2y$14$f3qml4G2hG6sxM26VMq.geDYbsS089IBtVJ7DlD05BoViS9PFykE2",
    "redirect_uri":  "/oauth/receivecode",
    "grant_types":   null
}
```

## User ID Provider

When a user requests an authorization code they may provide their user_id as a request parameter to
the `/oauth/authorize` route.  This will store the `user_id` in the `access_token`, `refresh_token`,
and `authorization_code` tables as the user goes throught the oauth2 process.

A user may be authenticated through `Zend\Authentication\AuthenticationService` or another
authentication means.  When a user must provide authentication before they may access the
`/oauth/authorize` route, the authenticated user ID should be used. This is done with the service
manager alias `ZF\OAuth2\Provider\UserId`.

The default User ID Provider uses the request query parameter `user_id` and is handled via the class
`ZF\OAuth2\Provider\UserId\Request`.

Provided with this repository is an alternative provider,
`ZF\OAuth2\Provider\UserId\AuthorizationService`, which uses
`Zend\Authentication\AuthenticationService` to fetch the identity.  To change the User ID Provider
to use this service, change the `ZF\OAuth2\Provider\UserId` service alias to point at it:

```php
return array(
    'service_manager' => 
        'aliases' => array(
            'ZF\OAuth2\Provider\UserId' => 'ZF\OAuth2\Provider\UserId\AuthenticationService',
        ),
    ),
);
```

## How to test OAuth2

To test the OAuth2 module, you have to add a `client_id` and a `client_secret`
into the oauth2 database. If you are using the SQLite test database, you don't
need to add a `client_id`; just use the default "testclient"/"testpass" account.

Because we encrypt the password using the `bcrypt` algorithm, you need to
encrypt the password using the [Zend\Crypt\Password\Bcrypt](http://framework.zend.com/manual/2.2/en/modules/zend.crypt.password.html#bcrypt)
class from Zend Framework 2. We provided a simple script in `/bin/bcrypt.php` to
generate the hash value of a user's password. You can use this tool from the
command line, with the following syntax:

```bash
php bin/bcrypt.php testpass
```

where "testpass" is the user's password that you want to encrypt. The output of
the previous command will be the hash value of the user's password, a string of
60 bytes like the following:

```
$2y$14$f3qml4G2hG6sxM26VMq.geDYbsS089IBtVJ7DlD05BoViS9PFykE2
```

After the generation of the hash value of the password (`client_secret`), you can
add a new `client_id` in the database using the following SQL statement:

```sql
INSERT INTO oauth_clients (
    client_id,
    client_secret,
    redirect_uri)
VALUES (
    "testclient",
    "$2y$14$f3qml4G2hG6sxM26VMq.geDYbsS089IBtVJ7DlD05BoViS9PFykE2",
    "/oauth/receivecode"
);
```

To test the OAuth2 module, you can use an HTTP client like
[HTTPie](https://github.com/jkbr/httpie) or [CURL](http://curl.haxx.se/).  The
examples below use HTTPie and the test account "testclient"/"testpass".

## REQUEST TOKEN (client\_credentials)

You can request an OAuth2 token using the following HTTPie command:

```bash
http --auth testclient:testpass -f POST http://<URL of your ZF2 app>/oauth grant_type=client_credentials
```

This POST requests a new token to the OAuth2 server using the *client_credentials*
mode. This is typical in machine-to-machine interaction for application access.
If everything works fine, you should receive a response like this:

```json
{
    "access_token":"03807cb390319329bdf6c777d4dfae9c0d3b3c35",
    "expires_in":3600,
    "token_type":"bearer",
    "scope":null
}
```

*Security note:* because this POST uses basic HTTP authentication, the
`client_secret` is exposed in plaintext in the HTTP request. To protect this
call, a [TLS/SSL](http://en.wikipedia.org/wiki/Transport_Layer_Security)
connection is required.


## AUTHORIZE (code)

If you have to integrate an OAuth2 service with a web application, you need to
use the Authorization Code grant type.  This grant requires an approval step to
authorize the web application. This step is implemented using a simple form that
requests the user approve access to the resource (account).  This module
provides a simple form to authorize a specific client. This form can be accessed
by a browser using the following URL:

```bash
http://<URL of your ZF2 app>/oauth/authorize?response_type=code&client_id=testclient&redirect_uri=/oauth/receivecode&state=xyz
```

This page will render the form asking the user to authorize or deny the access
for the client. If they authorize the access, the OAuth2 module will reply with
an Authorization code. This code must be used to request an OAuth2 token; the
following HTTPie command provides an example of how to do that:

```bash
http --auth testclient:testpass -f POST http://<URL of your ZF2 app>/oauth grant_type=authorization_code&code=YOUR_CODE&redirect_uri=/oauth/receivecode
```

In client-side scenarios (i.e mobile) where you cannot store the Client
Credentials in a secure way, you cannot use the previous workflow. In this case
we can use an *implicit grant*. This is similar to the authorization code, but
rather than an authorization code being returned from the authorization request,
a *token* is returned.

To enable the module to accept the implicit grant type, you need to change the
configuration of `allow_implicit` to `true` in the
`config/autoload/oauth2.local.php` file:


```php
return array(
    'zf-oauth2' => array(
        // ...
        'allow_implicit' => true,
        // ...
    ),
);
```

To request a token from the client side, you need to request authorization via
the OAuth2 server:

```
http://<URL of your ZF2 app>/oauth/authorize?response_type=token&client_id=testclient&redirect_uri=/oauth/receivecode&state=xyz
```

This request will render the authorization form as in the previous example. If
you authorize the access, the request will be redirected to `/oauth/receivecode`
(as provided in the `redirect_uri` parameter in the above example), with the
`access_token` specified in the URI fragment, per the following sample:

```
/oauth/receivecode#access_token=559d8f9b6bedd8d94c8e8d708f87475f4838c514&expires_in=3600&token_type=Bearer&state=xyz
```

To get the `access_token`, you can parse the URI. We used the URI fragment to
pass the `access_token` because in this way the token is not transmitted to the
server; it will available only to the client.

In JavaScript, you can easily parse the URI with this snippet of code:

```javascript
// function to parse fragment parameters
var parseQueryString = function( queryString ) {
    var params = {}, queries, temp, i, l;

    // Split into key/value pairs
    queries = queryString.split("&");

    // Convert the array of strings into an object
    for ( i = 0, l = queries.length; i < l; i++ ) {
        temp = queries[i].split('=');
        params[temp[0]] = temp[1];
    }
    return params;
};

// get token params from URL fragment
var tokenParams = parseQueryString(window.location.hash.substr(1));
```

## REVOKE (code)

Starting with version 1.4.0, you can revoke access tokens. By default, revocation
happens via a POST request to the path `/oauth/revoke`, which expects a payload
with:

- `token`, the OAuth2 access token to revoke.
- `token_type_hint => 'access_token'`, indicating that an access token is being
  revoked.

The payload may be delivered as `application/x-www-form-urlencoded` or as JSON.

## Access a test resource

When you obtain a valid token, you can access a restricted API resource. The
OAuth2 module is shipped with a test resource that is accessible with the URL
`/oauth/resource`. This is a simple resource that returns JSON data.

To access the test resource, you can use the following HTTPie command:

```bash
http -f POST http://<URL of your ZF2 app>/oauth/resource access_token=000ab5afab4cbbbda803fb9e50e7943f5e766748
# or
http http://<<URL of your ZF2 app>/oauth/resource "Authorization:Bearer 000ab5afab4cbbbda803fb9e50e7943f5e766748"
```

As you can see, the OAuth2 module supports the data either via POST, using the
`access_token` value, or using the [Bearer](http://tools.ietf.org/html/rfc6750)
authorization header.

## How to protect your API using OAuth2

You can protect your API using the following code (for instance, at the top of a
controller):

```php
if (!$this->server->verifyResourceRequest(OAuth2Request::createFromGlobals())) {
    // Not authorized return 401 error
    $this->getResponse()->setStatusCode(401);
    return;
}
```

where `$this->server` is an instance of `OAuth2\Server` (see the
[AuthController.php](src/Controller/AuthController.php)).
