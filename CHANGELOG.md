# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.4.0 - TBD

### Added

- [#122](https://github.com/zfcampus/zf-oauth2/pull/120) adds support for token
  revocation via the `/oauth/revoke` path. The path expects a POST request as
  either urlencoded or JSON values with the parameters:
  - `token`, the access token to revoke
  - `token_type_hint => access_token` to indicate an access token is being
    revoked.
- [#146](https://github.com/zfcampus/zf-oauth2/pull/120) updates the
  `AuthController` to catch `ZF\ApiProblem\Exception\ProblemExceptionInterface`
  instances thrown by the OAuth2 server and return `ApiProblemResponse`s.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.3.3 - 2016-07-07

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#147](https://github.com/zfcampus/zf-oauth2/pull/147) fixes an issue in the
  `AuthControllerFactory` introduced originally by a change in zend-mvc (and
  since corrected in that component). The patch to `AuthControllerFactory` makes
  it forwards compatible with zend-servicemanager v3, and prevents the original
  issue from recurring in the future.
- [#144](https://github.com/zfcampus/zf-oauth2/pull/144) removes an unused
  variable from the `receive-code` template.

## 1.3.2 - 2016-06-24

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#120](https://github.com/zfcampus/zf-oauth2/pull/120) fixes a typo in the
  `ZF\OAuth2\Provider\UserId\AuthenticationService` which prevented returning of
  the user identifier.
