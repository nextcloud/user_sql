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

 /**
  * This is the AJAX portion of the settings page.
  * 
  * It can:
  *   - Verify the connection settings
  *   - Load autocomplete values for tables
  *   - Load autocomplete values for columns
  *   - Save settings for a given domain
  *   - Load settings for a given domain
  * 
  * It always returns JSON encoded responses
  */
 
namespace OCA\user_sql;

// Init owncloud

// Check if we are a user
\OCP\User::checkAdminUser();
\OCP\JSON::checkAppEnabled('user_sql');

// CSRF checks
\OCP\JSON::callCheck();


$helper = new \OCA\user_sql\lib\Helper;

$l = \OC::$server->getL10N('user_sql');

$params = $helper -> getParameterArray();
$response = new \OCP\AppFramework\Http\JSONResponse();

// Check if the request is for us
if(isset($_POST['appname']) && ($_POST['appname'] === 'user_sql') && isset($_POST['function']) && isset($_POST['domain']))
{
    $domain = $_POST['domain'];
    switch($_POST['function'])
    {
        // Save the settings for the given domain to the database
        case 'saveSettings':
            $parameters = array('host' => $_POST['sql_hostname'],
                'password' => $_POST['sql_password'],
                'user' => $_POST['sql_username'],
                'dbname' => $_POST['sql_database'],
                'tablePrefix' => ''
            );
            
            // Check if the table exists
            if(!$helper->verifyTable($parameters, $_POST['sql_driver'], $_POST['sql_table']))
            {
                $response->setData(array('status' => 'error',
                            'data' => array('message' => $l -> t('The selected SQL table '.$_POST['sql_table'].' does not exist!'))));
                break;
            }
            
            // Retrieve all column settings
            $columns = array();
            foreach($params as $param)
            {
                if(strpos($param, 'col_') === 0)
                {
                    if(isset($_POST[$param]) && $_POST[$param] !== '')
                        $columns[] = $_POST[$param];
                }
            }

            // Check if the columns exist
            $status = $helper->verifyColumns($parameters, $_POST['sql_driver'], $_POST['sql_table'], $columns);
            if($status !== true)
            {
                $response->setData(array('status' => 'error',
                            'data' => array('message' => $l -> t('The selected SQL column(s) do(es) not exist: '.$status))));
                break;
            }

            // If we reach this point, all settings have been verified
            foreach($params as $param)
            {
                // Special handling for checkbox fields
                if(isset($_POST[$param]))
                {
                    if($param === 'set_strip_domain')
                    {
                        \OC::$server->getConfig()->setAppValue('user_sql', 'set_strip_domain_'.$domain, 'true');
                    } 
                    elseif($param === 'set_allow_pwchange')
                    {
                        \OC::$server->getConfig()->setAppValue('user_sql', 'set_allow_pwchange_'.$domain, 'true');
                    }
                    elseif($param === 'set_active_invert')
                    {
                        \OC::$server->getConfig()->setAppValue('user_sql', 'set_active_invert_'.$domain, 'true');
                    }
                    elseif($param === 'set_enable_gethome')
                    {
                        \OC::$server->getConfig()->setAppValue('user_sql', 'set_enable_gethome_'.$domain, 'true');
                    }
                    else
                    {
                        \OC::$server->getConfig()->setAppValue('user_sql', $param.'_'.$domain, $_POST[$param]);
                    }
                } else
                {
                    if($param === 'set_strip_domain')
                    {
                        \OC::$server->getConfig()->setAppValue('user_sql', 'set_strip_domain_'.$domain, 'false');
                    }
                    elseif($param === 'set_allow_pwchange')
                    {
                        \OC::$server->getConfig()->setAppValue('user_sql', 'set_allow_pwchange_'.$domain, 'false');
                    }
                    elseif($param === 'set_active_invert')
                    {
                        \OC::$server->getConfig()->setAppValue('user_sql', 'set_active_invert_'.$domain, 'false');
                    }
                    elseif($param === 'set_enable_gethome')
                    {
                        \OC::$server->getConfig()->setAppValue('user_sql', 'set_enable_gethome_'.$domain, 'false');
                    }
                }
            }
            $response->setData(array('status' => 'success',            
                    'data' => array('message' => $l -> t('Application settings successfully stored.'))));
        break;

        // Load the settings for a given domain
        case 'loadSettingsForDomain':
            $retArr = array();
            foreach($params as $param)
            {
                $retArr[$param] = \OC::$server->getConfig()->getAppValue('user_sql', $param.'_'.$domain, '');     
            }
            $response->setData(array('status' => 'success',            
                            'settings' => $retArr));
        break;

        // Try to verify the database connection settings
        case 'verifySettings':
            $cm = new \OC\DB\ConnectionFactory();

            if(!isset($_POST['sql_driver']))
            {
                $response->setData(array('status' => 'error',
                            'data' => array('message' => $l -> t('Error connecting to database: No driver specified.'))));
                break;
            }

            if(($_POST['sql_hostname'] === '') || ($_POST['sql_database'] === ''))
            {
                $response->setData(array('status' => 'error',
                        'data' => array('message' => $l -> t('Error connecting to database: You must specify at least host and database'))));
                break;
            }

            $parameters = array('host' => $_POST['sql_hostname'],
                'password' => $_POST['sql_password'],
                'user' => $_POST['sql_username'],
                'dbname' => $_POST['sql_database'],
                'tablePrefix' => ''
            );

            try {
                $conn = $cm -> getConnection($_POST['sql_driver'], $parameters);
                $response->setData(array('status' => 'success',
                            'data' => array('message' => $l -> t('Successfully connected to database'))));
            }
            catch(\Exception $e)
            {
                $response->setData(array('status' => 'error',
                            'data' => array('message' => $l -> t('Error connecting to database: ').$e->getMessage())));
            }
        break;

        // Get the autocompletion values for a column
        case 'getColumnAutocomplete':
            
            
            $parameters = array('host' => $_POST['sql_hostname'],
                'password' => $_POST['sql_password'],
                'user' => $_POST['sql_username'],
                'dbname' => $_POST['sql_database'],
                'tablePrefix' => ''
            );
            
            if($helper->verifyTable($parameters, $_POST['sql_driver'], $_POST['sql_table']))
                $columns = $helper->getColumns($parameters, $_POST['sql_driver'], $_POST['sql_table']);
            else
                $columns = array();
            
            $search = $_POST['request'];
            $ret = array();

            foreach($columns as $name)
            {
                if(($search === '') || ($search === 'search') || (strpos($name, $search) === 0))
                {
                    $ret[] = array('label' => $name,
                                   'value' => $name);
                }
            }
            $response -> setData($ret);
        break;

        // Get the autocompletion values for a table
        case 'getTableAutocomplete':
            $parameters = array('host' => $_POST['sql_hostname'],
                'password' => $_POST['sql_password'],
                'user' => $_POST['sql_username'],
                'dbname' => $_POST['sql_database'],
                'tablePrefix' => ''
            );

            $tables = $helper->getTables($parameters, $_POST['sql_driver']);

            $search = $_POST['request'];
            $ret = array();
            foreach($tables as $name)
            {
                if(($search === '') || ($search === 'search') || (strpos($name, $search) === 0))
                {
                    $ret[] = array('label' => $name,
                                   'value' => $name);
                }
            }
            $response -> setData($ret);
        break;
    }

} else
{
    // If the request was not for us, set an error message
    $response->setData(array('status' => 'error', 
                    'data' => array('message' => $l -> t('Not submitted for us.'))));
}

// Return the JSON array
echo $response->render();
