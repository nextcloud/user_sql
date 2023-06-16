<?php
/**
 * Nextcloud - user_sql
 *
 * @copyright 2021 Marcin Łojewski <dev@mlojewski.me>
 * @author    Marcin Łojewski <dev@mlojewski.me>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace OCA\UserSQL\Query;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Exception as DBALException;
use OC\DB\Connection;
use OC\DB\ConnectionFactory;
use OCA\UserSQL\Constant\DB;
use OCA\UserSQL\Constant\Query;
use OCA\UserSQL\Properties;
use OCP\ILogger;

/**
 * Used to query a database.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class DataQuery
{
    /**
     * @var string The application name.
     */
    private $appName;
    /**
     * @var ILogger The logger instance.
     */
    private $logger;
    /**
     * @var Properties The properties array.
     */
    private $properties;
    /**
     * @var QueryProvider The query provider.
     */
    private $queryProvider;
    /**
     * @var Connection The database connection.
     */
    private $connection;

    /**
     * The class constructor.
     *
     * @param string        $AppName       The application name.
     * @param ILogger       $logger        The logger instance.
     * @param Properties    $properties    The properties array.
     * @param QueryProvider $queryProvider The query provider.
     */
    public function __construct(
        $AppName, ILogger $logger, Properties $properties,
        QueryProvider $queryProvider
    ) {
        $this->appName = $AppName;
        $this->logger = $logger;
        $this->properties = $properties;
        $this->queryProvider = $queryProvider;
        $this->connection = false;
    }

    /**
     * Execute an update query.
     *
     * @param string $queryName The query name.
     * @param array  $params    The query parameters.
     *
     * @see Query
     * @return bool TRUE on success, FALSE otherwise.
     */
    public function update($queryName, $params = [])
    {
        return $this->execQuery($queryName, $params) !== false;
    }

    /**
     * Run a given query and return the result.
     *
     * @param string $queryName The query to execute.
     * @param array  $params    The query parameters to bind.
     * @param int    $limit     Results limit. Defaults to -1 (no limit).
     * @param int    $offset    Results offset. Defaults to 0.
     *
     * @return Statement|bool Result of query or FALSE on failure.
     */
    private function execQuery(
        $queryName, $params = [], $limit = -1, $offset = 0
    ) {
        if ($this->connection === false) {
            $this->connectToDatabase();
        }

        $query = $this->queryProvider[$queryName];
        try {
            $result = $this->connection->prepare($query, $limit, $offset);
        } catch (DBALException  $exception) {
            $this->logger->error(
                "Could not prepare the query: " . $exception->getMessage(),
                ["app" => $this->appName]
            );
            return false;
        }

        foreach ($params as $param => $value) {
            $result->bindValue(":" . $param, $value);
        }

        $this->logger->debug(
            "Executing query: " . $query . ", " . implode(",", $params),
            ["app" => $this->appName]
        );

        try {
            $result = $result->execute();
            return $result;

        } catch (DBALException  $exception) {
            $this->logger->error(
                "Could not execute the query: " . $exception->getMessage(),
                ["app" => $this->appName]
            );
            return false;
        }
    }

    /**
     * Connect to the database using Nextcloud's DBAL.
     */
    private function connectToDatabase()
    {
        $connectionFactory = new ConnectionFactory(
            \OC::$server->getSystemConfig()
        );

        $parameters = array(
            "host" => $this->properties[DB::HOSTNAME],
            "password" => $this->properties[DB::PASSWORD],
            "user" => $this->properties[DB::USERNAME],
            "dbname" => $this->properties[DB::DATABASE],
            "tablePrefix" => "",
            "driverOptions" => array()
        );

        if ($this->properties[DB::DRIVER] == 'mysql') {
            if ($this->properties[DB::SSL_CA]) {
                $parameters["driverOptions"][\PDO::MYSQL_ATTR_SSL_CA] = \OC::$SERVERROOT . '/' . $this->properties[DB::SSL_CA];
            }
            if ($this->properties[DB::SSL_CERT]) {
                $parameters["driverOptions"][\PDO::MYSQL_ATTR_SSL_CERT] = \OC::$SERVERROOT . '/' . $this->properties[DB::SSL_CERT];
            }
            if ($this->properties[DB::SSL_KEY]) {
                $parameters["driverOptions"][\PDO::MYSQL_ATTR_SSL_KEY] = \OC::$SERVERROOT . '/' . $this->properties[DB::SSL_KEY];
            }
        }

        $this->connection = $connectionFactory->getConnection(
            $this->properties[DB::DRIVER], $parameters
        );

        $this->logger->debug(
            "Database connection established.", ["app" => $this->appName]
        );
    }

    /**
     * Fetch a value from the first row and the first column which
     * the given query returns. Empty result set is consider to be a failure.
     *
     * @param string $queryName The query to execute.
     * @param array  $params    The query parameters to bind.
     * @param bool   $failure   Value returned on database query failure.
     *                          Defaults to FALSE.
     *
     * @return array|bool Queried value or $failure value on failure.
     */
    public function queryValue($queryName, $params = [], $failure = false)
    {
        $result = $this->execQuery($queryName, $params);
        if ($result === false) {
            return false;
        }

        $row = $result->fetchOne();
        if ($row === false) {
            return $failure;
        }

        return $row;
    }

    /**
     * Fetch values from the first column which the given query returns.
     *
     * @param string $queryName The query to execute.
     * @param array  $params    The query parameters to bind.
     * @param int    $limit     Results limit. Defaults to -1 (no limit).
     * @param int    $offset    Results offset. Defaults to 0.
     *
     * @return array|bool Queried column or FALSE on failure.
     */
    public function queryColumn(
        $queryName, $params = [], $limit = -1, $offset = 0
    ) {
        $result = $this->execQuery($queryName, $params, $limit, $offset);
        if ($result === false) {
            return false;
        }

        return $result->fetchFirstColumn();
    }

    /**
     * Fetch entity returned by the given query.
     *
     * @param string $queryName   The query to execute.
     * @param string $entityClass The entity class name.
     * @param array  $params      The query parameters to bind.
     *
     * @return mixed|null The queried entity, NULL if it does not exists or
     *                    FALSE on failure.
     */
    public function queryEntity($queryName, $entityClass, $params = [])
    {
        $result = $this->execQuery($queryName, $params);
        if ($result === false) {
            return false;
        }

        $entity = $result->fetchAssociative();

        if ($entity === false) {
            return null;
        }

        if (empty($entity) === true) {
            $this->logger->debug(
                "Empty result for query: " . $queryName,
                ["app" => $this->appName]
            );
            return null;
        }

        return self::arrayToObject($entity, $entityClass);
    }

    private function arrayToObject($array, $entityClass)
    {
        $object = new $entityClass();
        foreach ($array as $name => $value) {
            $object->$name = $array[$name];
        }
        return $object;
    }

    /**
     * Fetch entities returned by the given query.
     *
     * @param string $queryName   The query to execute.
     * @param string $entityClass The entity class name.
     * @param array  $params      The query parameters to bind.
     * @param int    $limit       Results limit. Defaults to -1 (no limit).
     * @param int    $offset      Results offset. Defaults to 0.
     *
     * @return mixed|null The queried entities or FALSE on failure.
     */
    public function queryEntities(
        $queryName, $entityClass, $params = [], $limit = -1, $offset = 0
    ) {
        $result = $this->execQuery($queryName, $params, $limit, $offset);
        if ($result === false) {
            return false;
        }

        return self::iterableToObjectArray($result->iterateAssociative(), $entityClass);
    }

    private function iterableToObjectArray($array, $entityClass)
    {
        $result = array();
        foreach ($array as $element) {
            $result[] = self::arrayToObject($element, $entityClass);
        }
        return $result;
    }
}
