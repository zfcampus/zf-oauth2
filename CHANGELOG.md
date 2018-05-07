# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.6.0 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.5.1 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.5.0 - 2018-05-07

### Added

- [#167](https://github.com/zfcampus/zf-oauth2/pull/167) adds support for PHP 7.1 and 7.2.

### Changed

- [#160](https://github.com/zfcampus/zf-oauth2/pull/160) alters `AuthController::tokenAction()` such that it uses the exception code from
  a caught `ProblemExceptionInterface` instance as the ApiProblem status if it falls in the 400-600 range.

- [#151](https://github.com/zfcampus/zf-oauth2/pull/151) updates `ZF\OAuth2\Provider\UserId\AuthenticationService` to allow injecting any
  `Zend\Authentication\AuthenticationServiceInterface` implementation, not just `Zend\Authentication\AuthenticationService`.

### Deprecated

- Nothing.

### Removed

- [#167](https://github.com/zfcampus/zf-oauth2/pull/167) removes support for HHVM.

### Fixed

- Nothing.

## 1.4.0 - 2016-07-10

### Added

- [#149](https://github.com/zfcampus/zf-oauth2/pull/149) adds support for usage
  of ext/mongodb with `ZF\OAuth2\Adapter\MongoAdapter`; users will need to also
  install a compatibility package to do so:
  `composer require alcaeus/mongo-php-adapter`
- [#141](https://github.com/zfcampus/zf-oauth2/pull/141) and
  [#148](https://github.com/zfcampus/zf-oauth2/pull/148) update the component to
  allow usage with v3 releases of Zend Framework components on which it depends,
  while maintaining backwards compatibility with v2 components.
- [#141](https://github.com/zfcampus/zf-oauth2/pull/141) and
  [#148](https://github.com/zfcampus/zf-oauth2/pull/148) add support for PHP 7.
- [#122](https://github.com/zfcampus/zf-oauth2/pull/122) adds support for token
  revocation via the `/oauth/revoke` path. The path expects a POST request as
  either urlencoded or JSON values with the parameters:
  - `token`, the access token to revoke
  - `token_type_hint => access_token` to indicate an access token is being
    revoked.
- [#146](https://github.com/zfcampus/zf-oauth2/pull/146) updates the
  `AuthController` to catch `ZF\ApiProblem\Exception\ProblemExceptionInterface`
  instances thrown by the OAuth2 server and return `ApiProblemResponse`s.

### Deprecated

- Nothing.

### Removed

- [#141](https://github.com/zfcampus/zf-oauth2/pull/141) removes support for PHP 5.5.

### Fixed

- Nothing.

## 1.3.3 - 2016-07-07

### Added

- Nothing.

### Changed

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
