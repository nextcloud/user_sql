# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [4.7.1]
- Support for Nextcloud 22, 23

## [4.7.0]
### Changed
- Support for Doctrine 3
- Support for Nextcloud 21 only

## [4.6.0] - 2021-01-16
### Fixed
- [issue#123](https://github.com/nextcloud/user_sql/issues/123) - sync exception - Duplicate entry
### Added
- Support for Nextcloud 20

## [4.5.0] - 2020-04-13
### Added
- Support for Nextcloud 19
- Argon2id support
- System wide values option
- Allow email login option
- UID user table column
- GID user table column
- HMAC hash implementation
- Default group option

## [4.4.1] - 2020-02-02
### Fixed
- Issue generating 'Object of class Closure could not be converted to string' log message

## [4.4.0] - 2019-10-09
### Added
- Users can confirm passwords
- Support Nextcloud password_policy
- Support for Nextcloud 18
- Name sync option

### Fixed
- Getting user display names backend
- Do not include users which are disabled

### Changed
- Extend user/group search

## [4.3.0] - 2018-12-30
### Added
- Reverse active column option
- Support for Nextcloud 16
- Set default value for "provide avatar" option
- Set hash algorithm parameters

## [4.2.1] - 2018-12-22
### Fixed
- SQL error when same column names given in several tables

## [4.2.0] - 2018-12-16
### Added
- Support for Nextcloud 15
- Redmine, SHA-256, SHA-512 hash algorithms

### Fixed
- Loading user list when display name is null
- Hide "password change form" when "Allow password change" not set

### Changed
- Append salt only when checked. Not by default

## [4.1.0] - 2018-10-28
### Added
- Whirlpool hash algorithm
- 'Prepend salt' toggle
- Drupal 7 hash algorithm
- 'Case-insensitive username' option

### Fixed
- Error when 'Display name' not set
- Encoding of iteration for 'Extended DES (Crypt)'
- 'Trying to get property of non-object' warning

## [4.0.1] - 2018-08-16
### Fixed
- Leftover lines break the admin page

## [4.0.0] - 2018-08-11
### Added
- SHA512 Whirlpool hash algorithm
- WoltLab Community Framework 2.x hash algorithm
- phpass hash implementation
- Support for salt column
- User quota synchronization

### Changed
- Example SQL script in README file
- Fixed misspelling
- Support for Nextcloud 14 only
- Group backend implementation
- User backend implementation

### Fixed
- Table and column autocomplete in settings panel

## [4.0.0-rc2] - 2018-06-14
### Added
- User active column

### Changed
- Fixed "Use of undefined constant" error for Argon2 Crypt with PHP below 7.2.

## [4.0.0-rc1] - 2018-06-13
### Added
- New hash algorithms: Argon2 Crypt (PHP 7.2 and above), Blowfish Crypt, Courier base64-encoded MD5, Courier base64-encoded SHA1, Courier base64-encoded SHA256, Courier hexadecimal MD5, Extended DES Crypt, SHA256 Crypt, SHA512 Crypt, SSHA512, Standard DES Crypt
- Option to allow users to change their display names
- Option to allow user to change its avatar 
- Database query results cache
- Option for group display name
- Option for group is admin flag

### Changed
- The whole core implementation, which is NOT COMPATIBLE with the previous versions.
- Minimum supported PHP version - 7.0

### Removed
- MySQL ENCRYPT() hash implementation - Function is deprecated as of MySQL 5.7.6 and will be removed in a future MySQL release.
- MySQL PASSWORD() hash implementation - Function is deprecated as of MySQL 5.7.6 and will be removed in a future MySQL release.
- Redmine hash implementation - Cannot implement in new core system.
- User active column - Use database view instead
- Domain support

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

## 2.4.0 - 2017-12-26
### Added
- This CHANGELOG.md file
- Support for PHP 7
- SHA1 hash algorithm support
- Groups option
- Supervisor option

### Changed
- Supported version of ownCloud, Nextcloud: ownCloud 10, Nextcloud 12

[4.7.1]: https://github.com/nextcloud/user_sql/compare/v4.7.0...v4.7.1
[4.7.0]: https://github.com/nextcloud/user_sql/compare/v4.6.0...v4.7.0
[4.6.0]: https://github.com/nextcloud/user_sql/compare/v4.5.0...v4.6.0
[4.5.0]: https://github.com/nextcloud/user_sql/compare/v4.4.1...v4.5.0
[4.4.1]: https://github.com/nextcloud/user_sql/compare/v4.4.0...v4.4.1
[4.4.0]: https://github.com/nextcloud/user_sql/compare/v4.3.0...v4.4.0
[4.3.0]: https://github.com/nextcloud/user_sql/compare/v4.2.1...v4.3.0
[4.2.1]: https://github.com/nextcloud/user_sql/compare/v4.2.0...v4.2.1
[4.2.0]: https://github.com/nextcloud/user_sql/compare/v4.1.0...v4.2.0
[4.1.0]: https://github.com/nextcloud/user_sql/compare/v4.0.1...v4.1.0
[4.0.1]: https://github.com/nextcloud/user_sql/compare/v4.0.0...v4.0.1
[4.0.0]: https://github.com/nextcloud/user_sql/compare/v4.0.0-rc2...v4.0.0
[4.0.0-rc2]: https://github.com/nextcloud/user_sql/compare/v4.0.0-rc1...v4.0.0-rc2
[4.0.0-rc1]: https://github.com/nextcloud/user_sql/compare/v3.1.0...v4.0.0-rc1
[3.1.0]: https://github.com/nextcloud/user_sql/compare/v2.4.0...v3.1.0
