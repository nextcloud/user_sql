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
 * Factory for the database platform class instance.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class PlatformFactory
{
    /**
     * Get the database platform.
     *
     * @param Connection $connection The database connection.
     *
     * @return AbstractPlatform The database platform.
     */
    public static function getPlatform(Connection $connection)
    {
        switch ($connection->getDriver()->getName()) {
        case "pdo_mysql":
            return new MySQLPlatform($connection);
        case "pdo_pgsql":
            return new PostgreSQLPlatform($connection);
        default:
            throw new \InvalidArgumentException(
                "Unknown database driver: " . $connection->getDriver()->getName(
                )
            );
        }
    }
}
