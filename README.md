user_sql
========

**Nextcloud SQL user authentication.**

![](https://github.com/nextcloud/user_sql/blob/master/screenshot.png)

Use external database as a source for Nextcloud users and groups.
Retrieve the users and groups info. Allow the users to change their passwords.
Sync the users' email addresses with the addresses stored by Nextcloud.

## Getting Started

1. SSH into your server.

2. Get into the apps folder of your Nextcloud installation, for example */var/www/nextcloud/apps*.

3. Git clone this project: `git clone https://github.com/nextcloud/user_sql.git`

4. Login to your Nextcloud instance as admin.

5. Navigate to Apps from the menu then find and enable the *User and Group SQL Backends* app.

6. Navigate to Admin from menu and switch to Additional Settings, scroll down the page and you will see *SQL Backends* settings.

*You can skip the first three steps as this app is available in the official Nextcloud App Store.*

## Configuration

Below are detailed descriptions of all available options. The options are mandatory if not said differently.

### Database connection

This section contains database connection parameters. 

**SQL driver** - The database driver to use. Currently supported drivers are: mysql, pgsql.

**Hostname** - The hostname on which the database server resides.
 
**Database** - The name of the database.

**Username** - The name of the user for the connection. (optional) 

**Password** - The password of the user for the connection. (optional)

#### Options

**Allow display name change** - With this option enabled user can change its display name. The change is propagated to the database. (optional, default: 0)

**Allow password change** - Can user change its password. The password hash is propagated to the database. See [Hash algorithms](#Hash algorithms). (optional, default: 0)
 
**Use cache** - Use database query results cache. The cache can be cleared any time with the *Clear cache* button click. (optional, default: 0)

**Hashing algorithm** - How users passwords are store in the database. See [Hash algorithms](#Hash algorithms).

**Email sync** - Sync e-mail address with the Nextcloud.
 - *None* - Disables this feature. This is the default option.
 - *Synchronise only once* - Copy the e-mail address to the Nextcloud storage if its not set.
 - *Nextcloud always wins* - Always copy the e-mail address to the database. This updates the user table.
 - *SQL always wins* - Always copy the e-mail address to the Nextcloud storage.

**Home mode** - User's storage path.
 - *Default* - Let the Nextcloud manage this. The default option.
 - *Query* - Use location from the database pointed by the home column.
 - *Static* - Use static location. The `%u` variable by replaced with the username of the user.

**Home Location** - User storage location for the static home mode. Mandatory if the *Home mode* is set to `Static`.

## Integrations

### WordPress
Thanks to this app, Nextcloud can easily integrate with Wordpress.

In the Nextcloud Column Settings of SQL User Backend, configure it as
```
Table: wp_users
Username Column: user_login
Password Column: user_pass
Encryption Type: Joomla > 2.5.18 phppass
```

### JHipster
It is very easy to integrate Nextcloud with JHipster.

Follow the Using the Database instructions in [Using Jhipster in development](http://www.jhipster.tech/development/) to configure your database. Assume you chose MySQL as JHipster database.

In the Nextcloud Column Settings of SQL User Backend, configure it as
```
Table: jhi_users
Username Column: login
Password Column: password_hash
Encryption Type: Joomla > 2.5.18 phppass
User Activate Column: activated
Email Column: email
```

## Features
Currently, it supports most of postfixadmin's encryption options, except dovecot and saslauthd.
It was tested and developed for a postfixadmin database.

Password changing is disabled by default, but can be enabled in the Admin area.
Caution: user_sql does not recreate password salts, which imposes a security risk. 
Password salts should be newly generated whenever the password changes.

The column autocomplete works only for MySQL and PostgreSQL database which is used to validate form data.
If you use other database use *occ* command to set the application config parameters with domain suffix.

For example to set 'sql_hostname' parameter in default domain use:

```occ config:app:set user_sql 'sql_hostname_default' --value='localhost'```

### Currently supported parameters

- sql_hostname
- sql_username
- sql_password
- sql_database
- sql_table
- sql_driver
- col_username
- col_password
- col_active
- col_displayname
- col_email
- col_gethome
- set_active_invert
- set_allow_pwchange
- set_default_domain
- set_strip_domain
- set_crypt_type
- set_mail_sync_mode
- set_enable_gethome
- set_gethome_mode
- set_gethome
- sql_group_table
- col_group_username
- col_group_name

## Acknowledgments
This repository contains continuation of work done in [this repo](https://www.aboehler.at/hg/user_sql/).

This plugin is heavily based on user_imap, user_pwauth, user_ldap and user_redmine!

### Credits

  * Andreas Boehler for releasing the first version of this application
  * Johan Hendriks provided his user_postfixadmin
  * Ed Wildgoose for fixing possible SQL injection vulnerability
