<?php

/**
 * ownCloud - user_sql
 *
 * @author Andreas Böhler and contributors
 * @copyright 2012-2015 Andreas Böhler <dev (at) aboehler (dot) at>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
 
$installedVersion = \OC::$server->getConfig()->getAppValue('user_sql', 'installed_version');

$params = array('sql_host' => 'sql_hostname', 
                'sql_user' => 'sql_username',
                'sql_database' => 'sql_database',
                'sql_password' => 'sql_password', 
                'sql_table' => 'sql_table',
                'sql_column_username' => 'col_username',
                'sql_column_password' => 'col_password',
                'sql_type' => 'sql_driver', 
                'sql_column_active' => 'col_active',
                'strip_domain' => 'set_strip_domain',
                'default_domain' => 'set_default_domain',
                'crypt_type' => 'set_crypt_type', 
                'sql_column_displayname' => 'col_displayname',
                'allow_password_change' => 'set_allow_pwchange',
                'sql_column_active_invert' => 'set_active_invert',
                'sql_column_email' => 'col_email',
                'mail_sync_mode' => 'set_mail_sync_mode'
                );
                
$delParams = array('domain_settings', 
                   'map_array', 
                   'domain_array'
                  );

if(version_compare($installedVersion, '1.99', '<'))
{
    foreach($params as $oldPar => $newPar)
    {
        $val = \OC::$server->getConfig()->getAppValue('user_sql', $oldPar);
        if(($oldPar === 'strip_domain') || ($oldPar === 'allow_password_change') || ($oldPar === 'sql_column_active_invert'))
        {
            if($val)
                $val = 'true';
            else
                $val = 'false';
        }
        if($val)
            \OC::$server->getConfig()->setAppValue('user_sql', $newPar.'_default', $val);
        \OC::$server->getConfig()->deleteAppValue('user_sql', $oldPar);
    }

    foreach($delParams as $param)
    {
        \OC::$server->getConfig()->deleteAppValue('user_sql', $param);
    }
}
