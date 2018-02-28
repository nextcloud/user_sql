<?php

/**
 * Nextcloud - user_sql
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

namespace OCA\user_sql\lib;

use OCP\IConfig;
use OCP\Util;

class Helper
{

    protected $db;
    protected $db_conn;
    protected $settings;

    /**
     * The default constructor initializes some parameters
     */
    public function __construct()
    {
        $this->db_conn = false;
    }

    /**
     * Return an array with all supported parameters
     * @return array Containing strings of the parameters
     */
    public function getParameterArray()
    {
        $params = array(
            'sql_hostname',
            'sql_username',
            'sql_password',
            'sql_database',
            'sql_table',
            'sql_driver',
            'col_username',
            'col_password',
            'col_active',
            'col_displayname',
            'col_email',
            'col_gethome',
            'set_active_invert',
            'set_allow_pwchange',
            'set_default_domain',
            'set_strip_domain',
            'set_crypt_type',
            'set_mail_sync_mode',
            'set_enable_gethome',
            'set_gethome_mode',
            'set_gethome',
            'sql_group_table',
            'col_group_username',
            'col_group_name'
        );

        return $params;
    }

    /**
     * Load the settings for a given domain. If the domain is not found,
     * the settings for 'default' are returned instead.
     * @param string $domain The domain name
     * @return array of settings
     */
    public function loadSettingsForDomain($domain)
    {
        Util::writeLog('OC_USER_SQL', "Trying to load settings for domain: " . $domain, Util::DEBUG);
        $settings = array();
        $sql_host = \OC::$server->getConfig()->getAppValue('user_sql', 'sql_hostname_' . $domain, '');
        if ($sql_host === '') {
            $domain = 'default';
        }
        $params = $this->getParameterArray();
        foreach ($params as $param) {
            $settings[$param] = \OC::$server->getConfig()->getAppValue('user_sql', $param . '_' . $domain, '');
        }
        Util::writeLog('OC_USER_SQL', "Loaded settings for domain: " . $domain, Util::DEBUG);
        return $settings;
    }

    /**
     * Run a given query type and return the results
     * @param string $type The type of query to run
     * @param array $params The parameter array of the query (i.e. the values to bind as key-value pairs)
     * @param bool $execOnly Only execute the query, but don't fetch the results (optional, default = false)
     * @param bool $fetchArray Fetch an array instead of a single row (optional, default=false)
     * @param array $limits use the given limits for the query (optional, default = empty)
     * @return mixed
     */
    public function runQuery($type, $params, $execOnly = false, $fetchArray = false, $limits = array())
    {
        Util::writeLog('OC_USER_SQL', "Entering runQuery for type: " . $type, Util::DEBUG);
        if (!$this->db_conn) {
            return false;
        }

        switch ($type) {
            case 'getHome':
                $query = "SELECT " . $this->settings['col_gethome'] . " FROM " . $this->settings['sql_table'] . " WHERE " . $this->settings['col_username'] . " = :uid";
                break;
            case 'getMail':
                $query = "SELECT " . $this->settings['col_email'] . " FROM " . $this->settings['sql_table'] . " WHERE " . $this->settings['col_username'] . " = :uid";
                break;

            case 'setMail':
                $query = "UPDATE " . $this->settings['sql_table'] . " SET " . $this->settings['col_email'] . " = :currMail WHERE " . $this->settings['col_username'] . " = :uid";
                break;

            case 'getPass':
                $query = "SELECT " . $this->settings['col_password'] . " FROM " . $this->settings['sql_table'] . " WHERE " . $this->settings['col_username'] . " = :uid";
                if ($this->settings['col_active'] !== '') {
                    $query .= " AND " . ($this->settings['set_active_invert'] === 'true' ? "NOT " : "") . $this->settings['col_active'];
                }
                break;

            case 'setPass':
                $query = "UPDATE " . $this->settings['sql_table'] . " SET " . $this->settings['col_password'] . " = :enc_password WHERE " . $this->settings['col_username'] . " = :uid";
                break;

            case 'getRedmineSalt':
                $query = "SELECT salt FROM " . $this->settings['sql_table'] . " WHERE " . $this->settings['col_username'] . " = :uid;";
                break;

            case 'countUsers':
                $query = "SELECT COUNT(*) FROM " . $this->settings['sql_table'] . " WHERE " . $this->settings['col_username'] . " LIKE :search";
                if ($this->settings['col_active'] !== '') {
                    $query .= " AND " . ($this->settings['set_active_invert'] === 'true' ? "NOT " : "") . $this->settings['col_active'];
                }
                break;

            case 'getUsers':
                $query = "SELECT " . $this->settings['col_username'] . " FROM " . $this->settings['sql_table'];
                $query .= " WHERE " . $this->settings['col_username'] . " LIKE :search";
                if ($this->settings['col_active'] !== '') {
                    $query .= " AND " . ($this->settings['set_active_invert'] === 'true' ? "NOT " : "") . $this->settings['col_active'];
                }
                $query .= " ORDER BY " . $this->settings['col_username'];
                break;

            case 'userExists':
                $query = "SELECT " . $this->settings['col_username'] . " FROM " . $this->settings['sql_table'] . " WHERE " . $this->settings['col_username'] . " = :uid";
                if ($this->settings['col_active'] !== '') {
                    $query .= " AND " . ($this->settings['set_active_invert'] === 'true' ? "NOT " : "") . $this->settings['col_active'];
                }
                break;

            case 'getDisplayName':
                $query = "SELECT " . $this->settings['col_displayname'] . " FROM " . $this->settings['sql_table'] . " WHERE " . $this->settings['col_username'] . " = :uid";
                if ($this->settings['col_active'] !== '') {
                    $query .= " AND " . ($this->settings['set_active_invert'] === 'true' ? "NOT " : "") . $this->settings['col_active'];
                }
                break;

            case 'mysqlEncryptSalt':
                $query = "SELECT ENCRYPT(:pw, :salt);";
                break;

            case 'mysqlEncrypt':
                $query = "SELECT ENCRYPT(:pw);";
                break;

            case 'mysqlPassword':
                $query = "SELECT PASSWORD(:pw);";
                break;

            case 'getUserGroups':
                $query = "SELECT " . $this->settings['col_group_name'] . " FROM " . $this->settings['sql_group_table'] . " WHERE " . $this->settings['col_group_username'] . " = :uid";
                break;

            case 'getGroups':
                $query = "SELECT distinct " . $this->settings['col_group_name'] . " FROM " . $this->settings['sql_group_table'] . " WHERE " . $this->settings['col_group_name'] . " LIKE :search";
                break;

            case 'getGroupUsers':
                $query = "SELECT distinct " . $this->settings['col_group_username'] . " FROM " . $this->settings['sql_group_table'] . " WHERE " . $this->settings['col_group_name'] . " = :gid";
                break;

            case 'countUsersInGroup':
                $query = "SELECT count(" . $this->settings['col_group_username'] . ") FROM " . $this->settings['sql_group_table'] . " WHERE " . $this->settings['col_group_name'] . " = :gid AND " . $this->settings['col_group_username'] . " LIKE :search";
                break;
        }

        if (isset($limits['limit']) && $limits['limit'] !== null) {
            $limit = intval($limits['limit']);
            $query .= " LIMIT " . $limit;
        }

        if (isset($limits['offset']) && $limits['offset'] !== null) {
            $offset = intval($limits['offset']);
            $query .= " OFFSET " . $offset;
        }

        Util::writeLog('OC_USER_SQL', "Preparing query: $query", Util::DEBUG);
        $result = $this->db->prepare($query);
        foreach ($params as $param => $value) {
            $result->bindValue(":" . $param, $value);
        }
        Util::writeLog('OC_USER_SQL', "Executing query...", Util::DEBUG);
        if (!$result->execute()) {
            $err = $result->errorInfo();
            Util::writeLog('OC_USER_SQL', "Query failed: " . $err[2], Util::DEBUG);
            return false;
        }
        if ($execOnly === true) {
            return true;
        }
        Util::writeLog('OC_USER_SQL', "Fetching result...", Util::DEBUG);
        if ($fetchArray === true) {
            $row = $result->fetchAll();
        } else {
            $row = $result->fetch();
        }

        if (!$row) {
            return false;
        }
        return $row;
    }

    /**
     * Connect to the database using Nextcloud's DBAL
     * @param array $settings The settings for the connection
     * @return bool
     */
    public function connectToDb($settings)
    {
        $this->settings = $settings;
        $cm = new \OC\DB\ConnectionFactory(\OC::$server->getSystemConfig());
        $parameters = array(
            'host' => $this->settings['sql_hostname'],
            'password' => $this->settings['sql_password'],
            'user' => $this->settings['sql_username'],
            'dbname' => $this->settings['sql_database'],
            'tablePrefix' => ''
        );
        try {
            $this->db = $cm->getConnection($this->settings['sql_driver'], $parameters);
            $this->db->query("SET NAMES 'UTF8'");
            $this->db_conn = true;
            return true;
        } catch (\Exception $e) {
            Util::writeLog('OC_USER_SQL', 'Failed to connect to the database: ' . $e->getMessage(), Util::ERROR);
            $this->db_conn = false;
            return false;
        }
    }

    /**
     * Check if all of the given columns exist
     * @param array $parameters The connection parameters
     * @param string $sql_driver The SQL driver to use
     * @param string $table The table name to check
     * @param array $cols The columns to check
     * @param array True if found, otherwise false
     * @return bool|string
     */
    public function verifyColumns($parameters, $sql_driver, $table, $cols)
    {
        $columns = $this->getColumns($parameters, $sql_driver, $table);
        $res = true;
        $err = '';
        foreach ($cols as $col) {
            if (!in_array($col, $columns, true)) {
                $res = false;
                $err .= $col . ' ';
            }
        }
        if ($res) {
            return true;
        } else {
            return $err;
        }
    }

    /**
     * Check if a given table exists
     * @param array $parameters The connection parameters
     * @param string $sql_driver The SQL driver to use
     * @param string $table The table name to check
     * @param array True if found, otherwise false
     * @return bool
     */
    public function verifyTable($parameters, $sql_driver, $table)
    {
        $tablesWithSchema = $this->getTables($parameters, $sql_driver, true);
        $tablesWithoutSchema = $this->getTables($parameters, $sql_driver, false);
        return in_array($table, $tablesWithSchema, true) || in_array($table, $tablesWithoutSchema, true);
    }

    /**
     * Retrieve a list of tables for the given connection parameters
     * @param array $parameters The connection parameters
     * @param string $sql_driver The SQL driver to use
     * @param boolean $schema Return table name with schema
     * @return array The found tables, empty if an error occurred
     */
    public function getTables($parameters, $sql_driver, $schema = true)
    {
        $cm = new \OC\DB\ConnectionFactory(\OC::$server->getSystemConfig());
        try {
            $conn = $cm->getConnection($sql_driver, $parameters);
            $platform = $conn->getDatabasePlatform();

            $queryTables = $platform->getListTablesSQL();
            $queryViews = $platform->getListViewsSQL($parameters['dbname']);
            $ret = array();

            $result = $conn->executeQuery($queryTables);
            while ($row = $result->fetch()) {
                $name = $this->getTableNameFromRow($sql_driver, $parameters['dbname'], $row, $schema);
                $ret[] = $name;
            }

            $result = $conn->executeQuery($queryViews);
            while ($row = $result->fetch()) {
                $name = $this->getViewNameFromRow($sql_driver, $row, $schema);
                $ret[] = $name;
            }
            return $ret;
        } catch (\Exception $e) {
            return array();
        }
    }

    /**
     * Retrieve table name from database list table SQL
     * @param string $sql_driver The SQL driver to use
     * @param string $dbname The database name
     * @param array $row Query result row
     * @param boolean $schema Return table name with schema
     * @return string Table name
     */
    public function getTableNameFromRow($sql_driver, $dbname, $row, $schema)
    {
        switch ($sql_driver) {
            case 'mysql':
                return $row['Tables_in_' . $dbname];
            case 'pgsql':
                if ($schema) {
                    return $row['schema_name'] . '.' . $row['table_name'];
                } else {
                    return $row['table_name'];
                }
            default:
                return null;
        }
    }

    /**
     * Retrieve view name from database list table SQL
     * @param string $sql_driver The SQL driver to use
     * @param array $row Query result row
     * @param boolean $schema Return table name with schema
     * @return string Table name
     */
    public function getViewNameFromRow($sql_driver, $row, $schema)
    {
        switch ($sql_driver) {
            case 'mysql':
                return $row['TABLE_NAME'];
            case 'pgsql':
                if ($schema) {
                    return $row['schemaname'] . '.' . $row['viewname'];
                } else {
                    return $row['viewname'];
                }
            default:
                return null;
        }
    }

    /**
     * Retrieve a list of columns for the given connection parameters
     * @param array $parameters The connection parameters
     * @param string $sql_driver The SQL driver to use
     * @param string $table The SQL table to work with
     * @return array The found column, empty if an error occured
     */
    public function getColumns($parameters, $sql_driver, $table)
    {
        $cm = new \OC\DB\ConnectionFactory(\OC::$server->getSystemConfig());
        try {
            $conn = $cm->getConnection($sql_driver, $parameters);
            $platform = $conn->getDatabasePlatform();
            $query = $platform->getListTableColumnsSQL($table);
            $result = $conn->executeQuery($query);
            $ret = array();
            while ($row = $result->fetch()) {
                switch ($sql_driver) {
                    case 'mysql':
                        $name = $row['Field'];
                        break;
                    case 'pgsql':
                        $name = $row['field'];
                        break;
                    default:
                        return $ret;
                }
                $ret[] = $name;
            }
            return $ret;
        } catch (\Exception $e) {
            return array();
        }
    }
}
