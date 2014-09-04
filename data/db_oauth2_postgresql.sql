CREATE TABLE oauth_access_tokens
(
  access_token character varying(40) NOT NULL,
  client_id character varying(80) NOT NULL,
  user_id character varying(255),
  expires timestamp(0) without time zone NOT NULL,
  scope character varying(2000),
  CONSTRAINT access_token_pk PRIMARY KEY (access_token)
);

CREATE TABLE oauth_authorization_codes
(
  authorization_code character varying(40) NOT NULL,
  client_id character varying(80) NOT NULL,
  user_id character varying(255),
  redirect_uri character varying(2000),
  expires timestamp(0) without time zone NOT NULL,
  scope character varying(2000),
  id_token character varying(2000),
  CONSTRAINT auth_code_pk PRIMARY KEY (authorization_code)
);

CREATE TABLE oauth_clients
(
  client_id character varying(80) NOT NULL,
  client_secret character varying(80) NOT NULL,
  redirect_uri character varying(2000) NOT NULL,
  grant_types character varying(80),
  scope character varying(2000),
  user_id character varying(255),
  CONSTRAINT clients_client_id_pk PRIMARY KEY (client_id)
);

CREATE TABLE oauth_jwt
(
  client_id character varying(80) NOT NULL,
  subject character varying(80),
  public_key character varying(2000),
  CONSTRAINT jwt_client_id_pk PRIMARY KEY (client_id)
);


CREATE TABLE oauth_refresh_tokens
(
  refresh_token character varying(40) NOT NULL,
  client_id character varying(80) NOT NULL,
  user_id character varying(255),
  expires timestamp(0) without time zone NOT NULL,
  scope character varying(2000),
  CONSTRAINT refresh_token_pk PRIMARY KEY (refresh_token)
);

CREATE TABLE oauth_scopes
(
  type character varying(255) NOT NULL DEFAULT 'supported'::character varying,
  scope character varying(2000),
  client_id character varying(80),
  is_default smallint
);

CREATE TABLE oauth_users
(
  username character varying(255) NOT NULL,
  password character varying(2000),
  first_name character varying(255),
  last_name character varying(255),
  CONSTRAINT username_pk PRIMARY KEY (username)
);

