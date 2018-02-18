# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- Options for Courier authlib authentication: courier_md5, courier_md5raw, courier_sha1, courier_sha256
- crypt_type 'drupal' for Drupal 7 authentication

## [3.1.0] - 2018-02-06
### Added
- Column autocomplete for PostgreSQL
- Currently supported parameters in README.md
- SALT support for password algorithms "system" and "password_hash"
### Changed
- Updated README.me file
- Nextcloud 12 & 13 support
- Moved files to be more on the standard places
- Renamed some files to be more standard like
- Source code changes to be more standard like (max 80 characters)
### Fixed
- Column autocomplete in "Groups Settings"
- Security fix for password length sniffing attacks
- Small bug fixes
## Removed
- Code for supervisor mode

## [2.4.0] - 2017-12-26
### Added
- This CHANGELOG.md file
- Support for PHP 7
- SHA1 hash algorithm support
- Groups option
- Supervisor option

### Changed
- Supported version of ownCloud, Nextcloud: ownCloud 10, Nextcloud 12
