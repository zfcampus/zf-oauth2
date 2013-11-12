Zf-OAuth2
=========

ZF2 module for [OAuth2](http://oauth.net/2/) authentication.

This module uses the [oauth2-server-php](https://github.com/bshaffer/oauth2-server-php) library of Brent Shaffer to provide OAuth2 support.

Installation
------------

You can install using:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
```

Configuration
-------------

This module uses a MySQL database to manage the OAuth2 information (users, client, token, etc).
In order to use this module you need to create a MySQL database using the file `data/db_oauth2.sql`.

```sql
CREATE TABLE oauth_clients (client_id VARCHAR(80) NOT NULL, client_secret VARCHAR(80) NOT NULL, redirect_uri VARCHAR(2000) NOT NULL, grant_tpyes VARCHAR(80), CONSTRAINT client_id_pk PRIMARY KEY (client_id));
CREATE TABLE oauth_access_tokens (access_token VARCHAR(40) NOT NULL, client_id VARCHAR(80) NOT NULL, user_id VARCHAR(255), expires TIMESTAMP NOT NULL, scope VARCHAR(2000), CONSTRAINT access_token_pk PRIMARY KEY (access_token));
CREATE TABLE oauth_authorization_codes (authorization_code VARCHAR(40) NOT NULL, client_id VARCHAR(80) NOT NULL, user_id VARCHAR(255), redirect_uri VARCHAR(2000), expires TIMESTAMP NOT NULL, scope VARCHAR(2000), CONSTRAINT auth_code_pk PRIMARY KEY (authorization_code));
CREATE TABLE oauth_refresh_tokens (refresh_token VARCHAR(40) NOT NULL, client_id VARCHAR(80) NOT NULL, user_id VARCHAR(255), expires TIMESTAMP NOT NULL, scope VARCHAR(2000), CONSTRAINT refresh_token_pk PRIMARY KEY (refresh_token));
CREATE TABLE oauth_users (username VARCHAR(255) NOT NULL, password VARCHAR(2000), first_name VARCHAR(255), last_name VARCHAR(255), CONSTRAINT username_pk PRIMARY KEY (username));
CREATE TABLE oauth_scopes (type VARCHAR(255) NOT NULL DEFAULT "supported", scope VARCHAR(2000), client_id VARCHAR (80));
CREATE TABLE oauth_jwt (client_id VARCHAR(80) NOT NULL, subject VARCHAR(80), public_key VARCHAR(2000), CONSTRAINT client_id_pk PRIMARY KEY (client_id));
```

After that you need to configure the database access, copy the file `config/oauth2.local.php.dist` in your `/config/autoload/oauth2.local.php` of your ZF2 application and edit with your DB credentials (DNS, username, password).


How to test OAuth2
------------------

To test the OAuth2 module you have to add a client_id and a client_secret into the MySQL database.
You can use the following SQL statement:

```sql
INSERT INTO oauth_clients (client_id, client_secret, redirect_uri) VALUES ("testclient", "testpass", "http://fake/");
```

To test the OAuth2 module you can use a HTTP client like [HTTPie](https://github.com/jkbr/httpie) or [CURL](http://curl.haxx.se/).
Below are reported some examples using HTTPie.

REQUEST TOKEN (client_credentials)
----------------------------------

You can request a OAuth2 token using the following command:

```bash
http --auth testclient:testpass -f POST http://<URL of your ZF2 app>/oauth grant_type=client_credentials
```

This POST requests a new token to the OAuth2 server using the *client_credentials* mode. This is typical in machine-to-machine interaction for Application access.
If everything works fine you should receive a response like this:

```json
{"access_token":"03807cb390319329bdf6c777d4dfae9c0d3b3c35","expires_in":3600,"token_type":"bearer","scope":null}
```

*Security note:* because this POST uses a basic HTTP authentication, the client_secret is exposed in plaintext in the HTTP request. To protect this call a [TLS/SSL](http://en.wikipedia.org/wiki/Transport_Layer_Security) connection is required.


AUTHORIZE (code)
----------------

If you have to integrate an OAuth2 service for a web application you need to use the Authorization Code grant type.
This grant require an approval step to authorize the web application. This step is implemented using a simple FORM that request to approve the access to the resource (account).
The OAuth2 provides a simple FORM to authorize a specific client. This FORM can be accessed by a browser using the following URL: 

```bash
http://<URL of your ZF2 app>/oauth/authorize?response_type=code&client_id=testclient&state=xyz
```

This page will render the FORM asking to authorize or deny the access for the client. If you authorize the access the OAuth2 will reply with an Authorization code. This code must be used to request an OAuth2 token using the following command:

```bash
http --auth testclient:testpass -f POST http://<URL of your ZF2 app>/oauth grant_type=authorization_code&code=YOUR_CODE
```


Access a test resource
----------------------

When you obtain a valid token you can access a restricted API resource. The OAuth2 module is shipped with a test resource that is accessable with the URL /oauth/resource. This is a simple resource that return a JSON data.

To access the test resource you can use the following command:
```bash
http -f POST http://<URL of your ZF2 app>/oauth/resource access_token=000ab5afab4cbbbda803fb9e50e7943f5e766748
# or
http http://<<URL of your ZF2 app>/oauth/resource "Authorization:Bearer 000ab5afab4cbbbda803fb9e50e7943f5e766748"
```
As you can see, the OAuth2 module supports the data in POST, using the `access_token` value, or using the [Bearear](http://tools.ietf.org/html/rfc6750) authorization header.


How to protect your API using OAuth2
------------------------------------

You can protect your API using the following code (for instance, at the top of a controller):

```php 
if (!$this->server->verifyResourceRequest(OAuth2Request::createFromGlobals())) {
    // Not authorized return 401 error
    $this->getResponse()->setStatusCode(401);
    return;
}
``` 

where `$this->server` is an instance of `OAuth2\Server` (see the [AuthController.php](https://github.com/zfcampus/zf-oauth2/blob/master/src/ZF/OAuth2/Controller/AuthController.php)).


