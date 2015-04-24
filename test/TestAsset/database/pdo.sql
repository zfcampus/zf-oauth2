PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
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
    scope VARCHAR(2000), id_token VARCHAR(2000),
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
INSERT INTO "oauth_users" VALUES('oauth_test_user','$2y$14$f3qml4G2hG6sxM26VMq.geDYbsS089IBtVJ7DlD05BoViS9PFykE2','test','user');
CREATE TABLE oauth_jwt (
    client_id VARCHAR(80) NOT NULL,
    subject VARCHAR(80),
    public_key VARCHAR(2000),
    CONSTRAINT client_id_pk PRIMARY KEY (client_id)
);
CREATE TABLE oauth_scopes (is_default TINYINT(1) DEFAULT NULL, type VARCHAR(255), scope VARCHAR(2000), client_id VARCHAR (80));
INSERT INTO "oauth_scopes" VALUES(0,NULL,'supportedscope1',NULL);
INSERT INTO "oauth_scopes" VALUES(0,NULL,'supportedscope2',NULL);
INSERT INTO "oauth_scopes" VALUES(0,NULL,'supportedscope3',NULL);
INSERT INTO "oauth_scopes" VALUES(1,NULL,'defaultscope2',NULL);
INSERT INTO "oauth_scopes" VALUES(1,NULL,'defaultscope1',NULL);
CREATE TABLE oauth_clients (
client_id VARCHAR(80) NOT NULL,
client_secret VARCHAR(80) NOT NULL,
redirect_uri VARCHAR(2000) NOT NULL,
grant_types VARCHAR(80), scope varchar(255), user_id varchar(255),
CONSTRAINT client_id_pk PRIMARY KEY (client_id)
);
INSERT INTO "oauth_clients" VALUES('testclient','$2y$14$f3qml4G2hG6sxM26VMq.geDYbsS089IBtVJ7DlD05BoViS9PFykE2','/oauth/receivecode',NULL,'clientscope1',NULL);
COMMIT;
