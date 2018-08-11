<?php
/**
 * Nextcloud - user_sql
 *
 * @copyright 2018 Marcin Łojewski <dev@mlojewski.me>
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

namespace OCA\UserSQL\Platform;

use Doctrine\DBAL\DBALException;
use OC\DB\Connection;

/**
 * Database platform tools.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
abstract class AbstractPlatform
{
    /**
     * @var Connection The database connection.
     */
    protected $connection;

    /**
     * The class constructor.
     *
     * @param Connection $connection The database connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get all the tables defined in the database.
     *
     * @param string $phrase       Show only tables containing given phrase.
     * @param bool   $schemaPrefix Show schema name in the results.
     *
     * @return array Array with table names.
     * @throws DBALException On a database exception.
     */
    public function getTables($phrase = "", $schemaPrefix = false)
    {
        $platform = $this->connection->getDatabasePlatform();

        $queryTables = $platform->getListTablesSQL();
        $queryViews = $platform->getListViewsSQL(
            $this->connection->getDatabase()
        );

        $tables = array();

        $result = $this->connection->executeQuery($queryTables);
        while ($row = $result->fetch()) {
            $name = $this->getTableName($row, $schemaPrefix);
            if (preg_match("/.*$phrase.*/i", $name)) {
                $tables[] = $name;
            }
        }

        $result = $this->connection->executeQuery($queryViews);
        while ($row = $result->fetch()) {
            $name = $this->getViewName($row, $schemaPrefix);
            if (preg_match("/.*$phrase.*/i", $name)) {
                $tables[] = $name;
            }
        }

        return $tables;
    }

    /**
     * Get a table name from a query result row.
     *
     * @param array  $row    The query result row.
     * @param string $schema Put schema name in the result.
     *
     * @return string The table name retrieved from the row.
     */
    protected abstract function getTableName($row, $schema);

    /**
     * Get a view name from a query result row.
     *
     * @param array  $row    The query result row.
     * @param string $schema Put schema name in the result.
     *
     * @return string The view name retrieved from the row.
     */
    protected abstract function getViewName($row, $schema);

    /**
     * Get all the columns defined in the table.
     *
     * @param string $table  The table name.
     * @param string $phrase Show only columns containing given phrase.
     *
     * @return array Array with column names.
     * @throws DBALException On a database exception.
     */
    public function getColumns($table, $phrase = "")
    {
        $platform = $this->connection->getDatabasePlatform();
        $query = $platform->getListTableColumnsSQL($table);
        $result = $this->connection->executeQuery($query);

        $columns = array();

        while ($row = $result->fetch()) {
            $name = $this->getColumnName($row);
            if (preg_match("/.*$phrase.*/i", $name)) {
                $columns[] = $name;
            }
        }

        return $columns;
    }

    /**
     * Get a column name from a query result row.
     *
     * @param array $row The query result row.
     *
     * @return string The column name retrieved from the row.
     */
    protected abstract function getColumnName($row);
}
