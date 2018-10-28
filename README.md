user_sql
========

**Nextcloud SQL user authentication.**

![screenshot](https://github.com/nextcloud/user_sql/blob/develop/img/screenshot.png)

Use external database as a source for Nextcloud users and groups.
Retrieve the users and groups info. Allow the users to change their passwords.
Sync the users' email addresses with the addresses stored by Nextcloud.

## Getting Started

1. SSH into your server.

2. Get into the apps folder of your Nextcloud installation, for example */var/www/nextcloud/apps*.

3. Git clone this project: `git clone https://github.com/nextcloud/user_sql.git`.

4. Login to your Nextcloud instance as admin.

5. Navigate to Apps from the menu then find and enable the *User and Group SQL Backends* app.

6. Navigate to Admin from menu and switch to Additional Settings, scroll down the page and you will see *SQL Backends* settings.

*You can skip the first three steps as this app is available in the official [Nextcloud App Store](https://apps.nextcloud.com/apps/user_sql).*

## Configuration

Below are detailed descriptions of all available options.

#### Database connection

This section contains the database connection parameters.

Name | Description | Details
--- | --- | ---
**SQL driver** | The database driver to use. Currently supported drivers are: mysql, pgsql. | Mandatory.
**Hostname** | The hostname on which the database server resides. | Mandatory.
**Database** | The name of the database. | Mandatory.
**Username** | The name of the user for the connection. | Optional.
**Password** | The password of the user for the connection. | Optional.

#### Options

Here are all currently supported options.

Name | Description | Details
--- | --- | ---
**Allow display name change** | With this option enabled user can change its display name. The display name change is propagated to the database. | Optional.<br/>Default: false.<br/>Requires: user *Display name* column.
**Allow password change** | Can user change its password. The password change is propagated to the database. See [Hash algorithms](#hash-algorithms). | Optional.<br/>Default: false.
**Case-insensitive username** | Whether user query should be case-sensitive or case-insensitive. | Optional.<br/>Default: false.
**Use cache** | Use database query results cache. The cache can be cleared any time with the *Clear cache* button click. | Optional.<br/>Default: false.
**Hash algorithm** | How users passwords are stored in the database. See [Hash algorithms](#hash-algorithms). | Mandatory.
**Email sync** | Sync e-mail address with the Nextcloud.<br/>- *None* - Disables this feature. This is the default option.<br/>- *Synchronise only once* - Copy the e-mail address to the Nextcloud preferences if its not set.<br/>- *Nextcloud always wins* - Always copy the e-mail address to the database. This updates the user table.<br/>- *SQL always wins* - Always copy the e-mail address to the Nextcloud preferences. | Optional.<br/>Default: *None*.<br/>Requires: user *Email* column.
**Quota sync** | Sync user quota with the Nextcloud.<br/>- *None* - Disables this feature. This is the default option.<br/>- *Synchronise only once* - Copy the user quota to the Nextcloud preferences if its not set.<br/>- *Nextcloud always wins* - Always copy the user quota to the database. This updates the user table.<br/>- *SQL always wins* - Always copy the user quota to the Nextcloud preferences. | Optional.<br/>Default: *None*.<br/>Requires: user *Quota* column.
**Home mode** | User storage path.<br/>- *Default* - Let the Nextcloud manage this. The default option.<br/>- *Query* - Use location from the user table pointed by the *home* column.<br/>- *Static* - Use static location pointed by the *Home Location* option. | Optional<br/>Default: *Default*.
**Home Location** | User storage path for the `Static` *Home mode*. The `%u` variable is replaced with the username of the user. | Mandatory if the *Home mode* is set to `Static`.

#### User table

The definition of user table. The table containing user accounts.

Name | Description | Details
--- | --- | ---
**Table name** | The table name. | Mandatory for user backend.
**Username** | Username column. | Mandatory for user backend.
**Email** | E-mail column. | Mandatory for *Email sync* option.
**Quota** | Quota column. | Mandatory for *Quota sync* option.
**Home** | Home path column. | Mandatory for `Query` *Home sync* option.
**Password** | Password hash column. | Mandatory for user backend.
**Display name** | Display name column. | Optional.
**Active** | Flag indicating if user can log in. | Optional.<br/>Default: true.
**Provide avatar** | Flag indicating if user can change its avatar. | Optional.<br/>Default: false.
**Salt** | Salt which is appended to password when checking or changing the password. | Optional.
**Prepend salt** | Prepend a salt to the password instead of appending it. | Optional.<br/>Default: false.

#### Group table

The group definitions table.

Name | Description | Details
--- | --- | ---
**Table name** | The table name. | Mandatory for group backend.
**Is admin** | Flag indicating if its the admin group | Optional.
**Display name** | Display name column. | Optional.
**Group name** | Group name column. | Mandatory for group backend.

#### User group table

Associative table which maps users to groups.

Name | Description | Details
--- | --- | ---
**Table name** | The table name. | Mandatory for group backend.
**Username** | Username column. | Mandatory for group backend.
**Group name** | Group name column. | Mandatory for group backend.

## Integrations

The basic functionality requires only one database table: [User table](#user-table).

For all options to work three tables are required:
 - [User table](#user-table),
 - [Group table](#group-table),
 - [User group table](#user-group-table).

If you already have an existing database you can always create database views which fits this model,
but be aware that some functionalities requires data changes (update queries).

If you don't have any database model yet you can use below tables (MySQL):
```
CREATE TABLE sql_user
(
  username       VARCHAR(16) PRIMARY KEY,
  display_name   TEXT        NULL,
  email          TEXT        NULL,
  quota          TEXT        NULL,
  home           TEXT        NULL,
  password       TEXT        NOT NULL,
  active         TINYINT(1)  NOT NULL DEFAULT '1',
  provide_avatar BOOLEAN     NOT NULL DEFAULT FALSE
);

CREATE TABLE sql_group
(
  name         VARCHAR(16) PRIMARY KEY,
  display_name TEXT        NULL,
  admin        BOOLEAN     NOT NULL DEFAULT FALSE
);

CREATE TABLE sql_user_group
(
  username   VARCHAR(16) NOT NULL,
  group_name VARCHAR(16) NOT NULL,
  PRIMARY KEY (username, group_name),
  FOREIGN KEY (username) REFERENCES sql_user (username),
  FOREIGN KEY (group_name) REFERENCES sql_group (name),
  INDEX sql_user_group_username_idx (username),
  INDEX sql_user_group_group_name_idx (group_name)
);
```

#### WordPress

Thanks to this app, Nextcloud can easily integrate with Wordpress.

In the Nextcloud user table settings of SQL Backends, configure it as:
```
User table: wp_users
Username column: user_login
Password column: user_pass

Hash algorithm: Unix (Crypt) or Portable PHP password
```

#### JHipster

It is very easy to integrate Nextcloud with JHipster.

Follow the Using the Database instructions in [Using Jhipster in development](http://www.jhipster.tech/development/)
to configure your database. Assume you chose MySQL as JHipster database.
In the Nextcloud user table settings of SQL Backends, configure it as:
```
User table: jhi_users
Username column: login
Password column: password_hash
Email column: email
Active column: activated

Hash algorithm: Unix (Crypt)
```

## Hash algorithms

Below is a table containing all of the supported hash implementations with example hashes.
The hashed password is "password", the salt if required have been generated randomly.

Hash name | Details | Hash example value
--- | --- | ---
Cleartext | Never use this. Only for development. | password
Courier base64-encoded MD5 | No salt supported. | {MD5RAW}5f4dcc3b5aa765d61d8327deb882cf99
Courier hexadecimal MD5 | No salt supported. | {MD5}X03MO1qnZdYdgyfeuILPmQ==
Courier base64-encoded SHA1 | No salt supported. | {SHA}W6ph5Mm5Pz8GgiULbPgzG37mj9g=
Courier base64-encoded SHA256 | No salt supported. | {SHA256}XohImNooBHFR0OVvjcYpJ3NgPQ1qq73WKhHvch0VQtg=
Unix (Crypt) | See [crypt](http://php.net/manual/en/function.crypt.php). | $2y$10$5rsN1fmoSkaRy9bqhozAXOr0mn0QiVIfd2L04Bbk1Go9MjdvotwBq
Argon2 (Crypt) | Requires PHP >= 7.2.<br/>Uses default parameters. See [password_hash](http://php.net/manual/en/function.password-hash.php). | $argon2i$v=19$m=1024,t=2,p=2$NnpSNlRNLlZobnJHUDh0Sw$oW5E1cfdPzLWfkTvQFUyzTR00R0aLwEdYwldcqW6Pmo
Blowfish (Crypt) | Uses default parameters. See [password_hash](http://php.net/manual/en/function.password-hash.php). | $2y$10$5rsN1fmoSkaRy9bqhozAXOr0mn0QiVIfd2L04Bbk1Go9MjdvotwBq
Extended DES (Crypt) | | cDRpdxPmHpzS.
MD5 (Crypt) | | $1$RzaFbNcU$u9adfTY/Q6za6nu0Ogrl1/
SHA256 (Crypt) | Generates hash with 5000 rounds. | $5$rounds=5000$VIYD0iHkg7uY9SRc$v2XLS/9dvfFN84mzGvW9wxnVt9Xd/urXaaTkpW8EwD1
SHA512 (Crypt) | Generates hash with 5000 rounds. | $6$rounds=5000$yH.Q0OL4qbCOUJ3q$Xry5EVFva3wKnfo8/ktrugmBd8tcl34NK6rXInv1HhmdSUNLEm0La9JnA57rqwQ.9/Bz513MD4tvmmISLUIHs/
Standard DES (Crypt) | | yTBnb7ab/N072
Drupal 7 | See [phpass](http://www.openwall.com/phpass/). | $S$DC7eCpJQ3SUQtW4Bp.vKb2rpeaffi4iqk9OpYwJyEoSMsezn67Sl
Joomla MD5 Encryption | Generates 32 chars salt. | 14d21b49b0f13e2acba962b6b0039edd:haJK0yTvBXTNMh76xwEw5RYEVpJsN8us
MD5 | No salt supported. | 5f4dcc3b5aa765d61d8327deb882cf99
Portable PHP password | See [phpass](http://www.openwall.com/phpass/). | $P$BxrwraqNTi4as0EI.IpiA/K.muk9ke/
SHA1 | No salt supported. | 5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8
SHA512 Whirlpool | No salt supported. | a96b16ebb691dbe968b0d66d0d924cff5cf5de5e0885181d00761d87f295b2bf3d3c66187c050fc01c196ff3acaa48d3561ffd170413346e934a32280d632f2e
SSHA256 | Generates 32 chars salt. | {SSHA256}+WxTB3JxprNteeovsuSYtgI+UkVPA9lfwGoYkz3Ff7hjd1FSdmlTMkNsSExyR21KM3NvNTZ5V0p4WXJMUjFzUg==
SSHA512 | Generates 32 chars salt. | {SSHA512}It+v1kAEUBbhMJYJ2swAtz+RLE6ispv/FB6G/ALhK/YWwEmrloY+0jzrWIfmu+rWUXp8u0Tg4jLXypC5oXAW00IyYnRVdEZJbE9wak96bkNRVWFCYmlJNWxrdTA0QmhL
WoltLab Community Framework 2.x | Double salted bcrypt. | $2a$08$XEQDKNU/Vbootwxv5Gp7gujxFX/RUFsZLvQPYM435Dd3/p17fto02
Whirlpool | | 74dfc2b27acfa364da55f93a5caee29ccad3557247eda238831b3e9bd931b01d77fe994e4f12b9d4cfa92a124461d2065197d8cf7f33fc88566da2db2a4d6eae

## Development

#### New database driver support

Add a new class in the `OCA\UserSQL\Platform` namespace which extends the `AbstractPlatform` class.
Add this driver in `admin.php` template  to `$drivers` variable and in method `getPlatform(Connection $connection)`
of `PlatformFactory` class.

#### New hash algorithm support

Create a new class in `OCA\UserSQL\Crypto` namespace which implements `IPasswordAlgorithm` interface.
Do not forget to write unit tests.

### Acknowledgments

This repository contains continuation of work done in [this repo](https://www.aboehler.at/hg/user_sql/).
This plugin was heavily based on user_imap, user_pwauth, user_ldap and user_redmine!

Since version 4.0.0 the whole core implementation has been rewritten.

### Credits

  * Andreas Boehler for releasing the first version of this application
  * Johan Hendriks provided his user_postfixadmin
  * Ed Wildgoose for fixing possible SQL injection vulnerability
