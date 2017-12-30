user_sql
========

Owncloud/Nextcloud SQL authentication

This repository contains continuation of work done in [this repo](https://www.aboehler.at/hg/user_sql/).

This plugin is heavily based on user_imap, user_pwauth, user_ldap and user_redmine!

Enable it in your Admin -> Apps section and configure your server's details.
Currently, it supports most of postfixadmin's encryption options, except dovecot and saslauthd.
It was tested and developed for a postfixadmin database.

Password changing is disabled by default, but can be enabled in the Admin area.
Caution: user_sql does not recreate password salts, which imposes a security risk. 
Password salts should be newly generated whenever the password changes.

Supervision can be enabled under supervisor settings. Supervision allows one
specified user to login into any account. Use supervisor username and target 
username separated by ';' to login as target user using supervisor's password 
(ex. superuser;user).

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
- set_supervisor
- supervisor
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

### Credits

  * Andreas Boehler for releasing the first version of this application
  * Johan Hendriks provided his user_postfixadmin
  * Ed Wildgoose for fixing possible SQL injection vulnerability
