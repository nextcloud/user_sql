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

Credits

  * Andreas Boehler for releasing the first version of this application
  * Johan Hendriks provided his user_postfixadmin
  * Ed Wildgoose for fixing possible SQL injection vulnerability
