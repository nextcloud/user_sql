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

use OC\DB\Connection;

/**
 * MySQL database platform.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class MySQLPlatform extends AbstractPlatform
{
    /**
     * The class constructor.
     *
     * @param Connection $connection The database connection.
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
    }

    /**
     * @inheritdoc
     */
    protected function getViewName($row, $schema)
    {
        return $row["TABLE_NAME"];
    }

    /**
     * @inheritdoc
     */
    protected function getTableName($row, $schema)
    {
        return $row["Tables_in_" . $this->connection->getDatabase()];
    }

    /**
     * @inheritdoc
     */
    protected function getColumnName($row)
    {
        return $row["Field"];
    }
}
