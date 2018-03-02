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

namespace OCA\UserSQL\Controller;

use Doctrine\DBAL\DBALException;
use Exception;
use OC\DatabaseException;
use OC\DB\Connection;
use OC\DB\ConnectionFactory;
use OCA\UserSQL\Cache;
use OCA\UserSQL\Constant\App;
use OCA\UserSQL\Platform\PlatformFactory;
use OCA\UserSQL\Properties;
use OCP\AppFramework\Controller;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;

/**
 * The settings controller.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class SettingsController extends Controller
{
    /**
     * @var ILogger The logger instance.
     */
    private $logger;
    /**
     * @var IL10N The localization service.
     */
    private $localization;
    /**
     * @var Properties The properties array.
     */
    private $properties;
    /**
     * @var Cache The cache instance.
     */
    private $cache;

    /**
     * The default constructor.
     *
     * @param string     $appName      The application name.
     * @param IRequest   $request      An instance of the request.
     * @param ILogger    $logger       The logger instance.
     * @param IL10N      $localization The localization service.
     * @param Properties $properties   The properties array.
     * @param Cache      $cache        The cache instance.
     */
    public function __construct(
        $appName, IRequest $request, ILogger $logger, IL10N $localization,
        Properties $properties, Cache $cache
    ) {
        parent::__construct($appName, $request);
        $this->appName = $appName;
        $this->logger = $logger;
        $this->localization = $localization;
        $this->properties = $properties;
        $this->cache = $cache;
    }

    /**
     * Verify the database connection parameters.
     *
     * @return array The request status.
     */
    public function verifyDbConnection()
    {
        $this->logger->debug(
            "Entering verifyDbConnection()", ["app" => $this->appName]
        );

        try {
            $this->getConnection();

            $this->logger->debug(
                "Returning verifyDbConnection(): success",
                ["app" => $this->appName]
            );

            return [
                "status" => "success",
                "data" => [
                    "message" => $this->localization->t(
                        "Successfully connected to the database."
                    )
                ]
            ];
        } catch (Exception $exception) {
            $this->logger->debug(
                "Returning verifyDbConnection(): error",
                ["app" => $this->appName]
            );

            return [
                "status" => "error",
                "data" => [
                    "message" => $this->localization->t(
                            "Error connecting to the database: "
                        ) . $exception->getMessage()
                ]
            ];
        }
    }

    /**
     * Get the database connection instance.
     *
     * @return Connection The database connection instance.
     * @throws DBALException On database connection problems.
     * @throws DatabaseException Whenever no database driver is specified.
     */
    private function getConnection()
    {
        $dbDriver = $this->request->getParam("db-driver");
        $dbHostname = $this->request->getParam("db-hostname");
        $dbDatabase = $this->request->getParam("db-database");
        $dbUsername = $this->request->getParam("db-username");
        $dbPassword = $this->request->getParam("db-password");

        if (empty($dbDriver)) {
            throw new DatabaseException("No database driver specified.");
        }

        $connectionFactory = new ConnectionFactory(
            \OC::$server->getSystemConfig()
        );

        $parameters = [
            "host" => $dbHostname,
            "password" => $dbPassword,
            "user" => $dbUsername,
            "dbname" => $dbDatabase,
            "tablePrefix" => ""
        ];

        $connection = $connectionFactory->getConnection($dbDriver, $parameters);
        $connection->executeQuery("SELECT 'user_sql'");

        return $connection;
    }

    /**
     * Save application properties.
     *
     * @return array The request status.
     */
    public function saveProperties()
    {
        $this->logger->debug(
            "Entering saveProperties()", ["app" => $this->appName]
        );

        $properties = $this->properties->getArray();

        foreach ($properties as $key => $value) {
            $reqValue = $this->request->getParam(str_replace(".", "-", $key));
            $appValue = $this->properties[$key];

            if ((!is_bool($appValue) && isset($reqValue)
                    && $reqValue !== $appValue)
                || (is_bool($appValue) && isset($reqValue) !== $appValue)
            ) {
                $value = isset($reqValue) ? $reqValue : App::FALSE_VALUE;
                $this->properties[$key] = $value;

                $this->logger->info(
                    "Property '$key' has been set to: " . $value,
                    ["app" => $this->appName]
                );
            }
        }

        $this->logger->debug(
            "Returning saveProperties(): success", ["app" => $this->appName]
        );

        return [
            "status" => "success",
            "data" => [
                "message" => $this->localization->t(
                    "Properties has been saved."
                )
            ]
        ];
    }

    /**
     * Clear the application cache memory.
     *
     * @return array The request status.
     */
    public function clearCache()
    {
        $this->logger->debug(
            "Entering clearCache()", ["app" => $this->appName]
        );

        $this->cache->clear();

        $this->logger->info(
            "Cache memory has been cleared.", ["app" => $this->appName]
        );

        return [
            "status" => "success",
            "data" => [
                "message" => $this->localization->t(
                    "Cache memory has been cleared."
                )
            ]
        ];
    }

    /**
     * Autocomplete for table select options.
     *
     * @return array The database table list.
     */
    public function tableAutocomplete()
    {
        $this->logger->debug(
            "Entering tableAutocomplete()", ["app" => $this->appName]
        );

        try {
            $connection = $this->getConnection();
            $platform = PlatformFactory::getPlatform($connection);
            $tables = $platform->getTables();

            $this->logger->debug(
                "Returning tableAutocomplete(): count(" . count($tables) . ")",
                ["app" => $this->appName]
            );

            return $tables;
        } catch (Exception $e) {
            $this->logger->logException($e);
            return [];
        }
    }

    /**
     * Autocomplete for column select options - user table.
     *
     * @return array The database table's column list.
     */
    public function userTableAutocomplete()
    {
        $this->logger->debug(
            "Entering userTableAutocomplete()", ["app" => $this->appName]
        );

        $columns = $this->columnAutocomplete("db-table-user");

        $this->logger->debug(
            "Returning userTableAutocomplete(): count(" . count($columns) . ")",
            ["app" => $this->appName]
        );

        return $columns;
    }

    /**
     * Autocomplete for column select options.
     *
     * @param string $table The table's form ID.
     *
     * @return array The table's column list.
     */
    private function columnAutocomplete($table)
    {
        try {
            $connection = $this->getConnection();
            $platform = PlatformFactory::getPlatform($connection);
            $columns = $platform->getColumns(
                $this->request->getParam($table)
            );

            return $columns;
        } catch (Exception $e) {
            $this->logger->logException($e);
            return [];
        }
    }

    /**
     * Autocomplete for column select options - user_group table.
     *
     * @return array The database table's column list.
     */
    public function userGroupTableAutocomplete()
    {
        $this->logger->debug(
            "Entering userGroupTableAutocomplete()", ["app" => $this->appName]
        );

        $columns = $this->columnAutocomplete("db-table-user_group");

        $this->logger->debug(
            "Returning userGroupTableAutocomplete(): count(" . count($columns)
            . ")", ["app" => $this->appName]
        );

        return $columns;
    }

    /**
     * Autocomplete for column select options - group table.
     *
     * @return array The database table's column list.
     */
    public function groupTableAutocomplete()
    {
        $this->logger->debug(
            "Entering groupTableAutocomplete()", ["app" => $this->appName]
        );

        $columns = $this->columnAutocomplete("db-table-group");

        $this->logger->debug(
            "Returning groupTableAutocomplete(): count(" . count($columns)
            . ")", ["app" => $this->appName]
        );

        return $columns;
    }
}
