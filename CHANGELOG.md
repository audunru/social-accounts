## [11.1.2](https://github.com/audunru/social-accounts/compare/v11.1.1...v11.1.2) (2026-11-13)


### Bug Fixes

* reject a redirect that starts with a slash and a backslash or has control characters ([#144](https://github.com/audunru/social-accounts/issues/144)) ([aa55f02](https://github.com/audunru/social-accounts/commit/aa55f02d6ec7470b14d1fc42cfc3dd99d00ec676))

## [11.1.1](https://github.com/audunru/social-accounts/compare/v11.1.0...v11.1.1) (2026-11-12)


### Bug Fixes

* remove the intended URL when the provider callback aborts ([#143](https://github.com/audunru/social-accounts/issues/143)) ([6c45fdb](https://github.com/audunru/social-accounts/commit/6c45fdb4ae485c27cd42df15b364b7c702769828)), closes [#141](https://github.com/audunru/social-accounts/issues/141)

# [11.1.0](https://github.com/audunru/social-accounts/compare/v11.0.0...v11.1.0) (2026-11-11)


### Features

* dispatch SocialAccountRemoved when a social account is removed ([#142](https://github.com/audunru/social-accounts/issues/142)) ([bedad21](https://github.com/audunru/social-accounts/commit/bedad21786f6f2237ba4601df25b938ebfafd53f))

# [11.0.0](https://github.com/audunru/social-accounts/compare/v10.2.0...v11.0.0) (2026-11-11)


### Bug Fixes

* do not dispatch SocialUserLoggedIn when a social account is added ([ec83fbc](https://github.com/audunru/social-accounts/commit/ec83fbc2ed8f00287ef36f14390323bffe9616a8))
* do not log in again when a social account is added ([e06fb27](https://github.com/audunru/social-accounts/commit/e06fb27d8952d378c6fe6c16e7414d96b112f9f8))


### BREAKING CHANGES

* when a logged-in user adds a social account, the callback no longer calls
Auth::login(), so Laravel's Login event is not dispatched and no remember cookie is set.
Listen for SocialAccountAdded for that case.
* SocialUserLoggedIn is no longer dispatched when a logged-in user adds a
social account. Listen for SocialAccountAdded for that case.

# [10.2.0](https://github.com/audunru/social-accounts/compare/v10.1.0...v10.2.0) (2026-11-07)


### Features

* throw UserCreationDenied when the create-user gate denies ([#136](https://github.com/audunru/social-accounts/issues/136)) ([b6c40bf](https://github.com/audunru/social-accounts/commit/b6c40bfd1694b6e9214d319ed25fce518a4e34d2))

# [10.1.0](https://github.com/audunru/social-accounts/compare/v10.0.0...v10.1.0) (2026-11-07)


### Features

* check the provider email before automatic user creation ([#134](https://github.com/audunru/social-accounts/issues/134)) ([970caa8](https://github.com/audunru/social-accounts/commit/970caa88226db785f56331f339842ba9e8818b4f))

# [10.0.0](https://github.com/audunru/social-accounts/compare/v9.2.1...v10.0.0) (2026-11-07)


### Documentation

* mark the SocialAccountBelongsToAnotherUser change as breaking ([bc711a4](https://github.com/audunru/social-accounts/commit/bc711a4620b01485df8727417dc95c997cf97846))


### BREAKING CHANGES

* When a logged-in user authenticates with a provider account that belongs to
another user, the callback now throws SocialAccountBelongsToAnotherUser (409) instead of
logging in the other user.

## [9.2.1](https://github.com/audunru/social-accounts/compare/v9.2.0...v9.2.1) (2026-11-07)


### Bug Fixes

* stop the provider callback from logging in another user ([#131](https://github.com/audunru/social-accounts/issues/131)) ([1aa4447](https://github.com/audunru/social-accounts/commit/1aa4447cb06e209134324667fb98d6e0b211b922))

# [9.2.0](https://github.com/audunru/social-accounts/compare/v9.1.4...v9.2.0) (2026-11-07)


### Features

* dispatch SocialUserLoggedIn event after a successful provider login ([#129](https://github.com/audunru/social-accounts/issues/129)) ([b3f2453](https://github.com/audunru/social-accounts/commit/b3f24539b633ce2bde5596ca2314b6ace1c2467c))

## [9.1.4](https://github.com/audunru/social-accounts/compare/v9.1.3...v9.1.4) (2026-11-06)


### Bug Fixes

* use column_names config in hasProvider() ([#126](https://github.com/audunru/social-accounts/issues/126)) ([6b46e55](https://github.com/audunru/social-accounts/commit/6b46e555fe99b0057dafb8116b9b26c6f3ed1cae))

## [9.1.3](https://github.com/audunru/social-accounts/compare/v9.1.2...v9.1.3) (2026-10-11)


### Bug Fixes

* add type information to the SocialAccount resource and model ([#118](https://github.com/audunru/social-accounts/issues/118)) ([30cf16e](https://github.com/audunru/social-accounts/commit/30cf16e288f2e0d1c73680333e2e2218c5dc310a)), closes [#117](https://github.com/audunru/social-accounts/issues/117)

## [9.1.2](https://github.com/audunru/social-accounts/compare/v9.1.1...v9.1.2) (2026-10-11)


### Bug Fixes

* only redirect to same-origin urls after provider callback ([#116](https://github.com/audunru/social-accounts/issues/116)) ([254cdc3](https://github.com/audunru/social-accounts/commit/254cdc341a1ba9c8a972ffccc569cc35a566e7bb)), closes [#115](https://github.com/audunru/social-accounts/issues/115)

## [9.1.1](https://github.com/audunru/social-accounts/compare/v9.1.0...v9.1.1) (2026-10-08)


### Bug Fixes

* redirect to intended url when provider callback returns an error ([#114](https://github.com/audunru/social-accounts/issues/114)) ([18245a0](https://github.com/audunru/social-accounts/commit/18245a0306ebaf9be4a6fc31767c305d7af07748))

# [9.1.0](https://github.com/audunru/social-accounts/compare/v9.0.0...v9.1.0) (2026-05-25)


### Features

* add Trivy security scan workflow ([#95](https://github.com/audunru/social-accounts/issues/95)) ([6f3345c](https://github.com/audunru/social-accounts/commit/6f3345c33920c285f3b68dc4080fbbf366ccd6f6))

# [9.0.0](https://github.com/audunru/social-accounts/compare/v8.1.2...v9.0.0) (2026-03-24)


### Features

* laravel 13 support ([#89](https://github.com/audunru/social-accounts/issues/89)) ([cfeb0b6](https://github.com/audunru/social-accounts/commit/cfeb0b64be827fc57cc612ec8d7692cfa5cf398c))


### BREAKING CHANGES

* Laravel 13 is now the minimum required version, which also means PHP 8.3 is required

## [8.1.2](https://github.com/audunru/social-accounts/compare/v8.1.1...v8.1.2) (2025-11-15)


### Bug Fixes

* force semantic release ([521a33a](https://github.com/audunru/social-accounts/commit/521a33aecc1bed170058171d32a23b67d2d39f55))

# Changelog

## [8.1.0](https://github.com/audunru/social-accounts/compare/v8.0.0...v8.1.0) (2025-09-22)


### Features

* redirect to intended url after login ([#65](https://github.com/audunru/social-accounts/issues/65)) ([260de0e](https://github.com/audunru/social-accounts/commit/260de0e10cb1c6cf95e0421df5c9afaec9b00351))

## [8.0.0](https://github.com/audunru/social-accounts/compare/v7.0.0...v8.0.0) (2025-04-05)


### ⚠ BREAKING CHANGES

* laravel sanctum support ([#46](https://github.com/audunru/social-accounts/issues/46))

### Features

* laravel sanctum support ([#46](https://github.com/audunru/social-accounts/issues/46)) ([412166d](https://github.com/audunru/social-accounts/commit/412166d117f5cad817d96cc3c2e578a63b20f905))

## [7.0.0](https://github.com/audunru/social-accounts/compare/v6.1.2...v7.0.0) (2025-03-16)


### ⚠ BREAKING CHANGES

* laravel 12 support ([#44](https://github.com/audunru/social-accounts/issues/44))

### Features

* laravel 12 support ([#44](https://github.com/audunru/social-accounts/issues/44)) ([e127b25](https://github.com/audunru/social-accounts/commit/e127b25dbcad49aa0385e8f9effb67eb6f1703f5))


### Miscellaneous Chores

* rename master branch to main ([1ab9b38](https://github.com/audunru/social-accounts/commit/1ab9b38fd959311177ec273191e44692ef2394e1))

## [6.1.2](https://github.com/audunru/social-accounts/compare/v6.1.1...v6.1.2) (2025-02-01)


### Miscellaneous Chores

* dependabot config ([2961efb](https://github.com/audunru/social-accounts/commit/2961efbe1201cdad61a64263205a42e7362e424a))

## [6.1.1](https://github.com/audunru/social-accounts/compare/v6.1.0...v6.1.1) (2024-08-25)


### Bug Fixes

* dispatch registered event after socialusercreated ([821b191](https://github.com/audunru/social-accounts/commit/821b191619ff442cd6d4c7df404471020e9f699b))

## [6.1.0](https://github.com/audunru/social-accounts/compare/v6.0.1...v6.1.0) (2024-08-25)


### Features

* dispatch registered event when user is created ([#22](https://github.com/audunru/social-accounts/issues/22)) ([4bb57e0](https://github.com/audunru/social-accounts/commit/4bb57e0f310015029af8966ed2c47d39d7849883))


### Miscellaneous Chores

* update dependencies ([08314dc](https://github.com/audunru/social-accounts/commit/08314dce7a2e1565b4ecbaf20e7036aa982d2009))

## [6.0.1](https://github.com/audunru/social-accounts/compare/v6.0.0...v6.0.1) (2024-05-20)


### Miscellaneous Chores

* parallel php-cs-fixer ([814f628](https://github.com/audunru/social-accounts/commit/814f6281f5148e559bcd01336dea3f600e37bc3d))
* update dependencies ([3c27388](https://github.com/audunru/social-accounts/commit/3c27388d8ee9afc3e88687a7dd15a8f7832bf4a6))

## [6.0.0](https://github.com/audunru/social-accounts/compare/v5.0.0...v6.0.0) (2024-04-07)


### Features

* laravel 11 support ([#17](https://github.com/audunru/social-accounts/issues/17)) ([a77cf05](https://github.com/audunru/social-accounts/commit/a77cf05e89c145d37ed3e68e36848c7d7e17be62))


### Miscellaneous Chores

* delete prettier config ([07265cd](https://github.com/audunru/social-accounts/commit/07265cdf26a9f789cb04fb571b82917ed8150e8e))
* release 6.0.0 ([91dad42](https://github.com/audunru/social-accounts/commit/91dad42ffeb3868f3a019db07f7179e298b40a7f))
* update dependencies ([511f5c4](https://github.com/audunru/social-accounts/commit/511f5c4b14dc93d7a2da7f4aacdd7ad20ccdcb2b))
* update phpunit ([501fda4](https://github.com/audunru/social-accounts/commit/501fda4db6616452260cbb11d65d40f6ea33fa17))
* update styleci config ([f297463](https://github.com/audunru/social-accounts/commit/f297463ca0be48b736a50a4a418532ee084fb2ac))
