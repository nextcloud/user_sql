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

namespace OCA\UserSQL\Controller;

use Doctrine\DBAL\DBALException;
use Exception;
use OC\DatabaseException;
use OC\DB\Connection;
use OC\DB\ConnectionFactory;
use OCA\UserSQL\Cache;
use OCA\UserSQL\Constant\App;
use OCA\UserSQL\Constant\DB;
use OCA\UserSQL\Constant\Opt;
use OCA\UserSQL\Crypto\IPasswordAlgorithm;
use OCA\UserSQL\Crypto\Param\ChoiceParam;
use OCA\UserSQL\Crypto\Param\IntParam;
use OCA\UserSQL\Platform\PlatformFactory;
use OCA\UserSQL\Properties;
use OCP\AppFramework\Controller;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;
use ReflectionClass;
use ReflectionException;

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
     *
     * @AuthorizedAdminSetting(settings=OCA\UserSQL\Settings\Admin)
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
        $dbSSL_ca = $this->request->getParam("db-ssl_ca");
        $dbSSL_cert = $this->request->getParam("db-ssl_cert");
        $dbSSL_key = $this->request->getParam("db-ssl_key");

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
            "tablePrefix" => "",
            "driverOptions" => array()
        ];

        if ($dbDriver == 'mysql') {
            if ($dbSSL_ca) {
                $parameters["driverOptions"][\PDO::MYSQL_ATTR_SSL_CA] = \OC::$SERVERROOT . '/' . $dbSSL_ca;
            }
            if ($dbSSL_cert) {
                $parameters["driverOptions"][\PDO::MYSQL_ATTR_SSL_CERT] = \OC::$SERVERROOT . '/' . $dbSSL_cert;
            }
            if ($dbSSL_key) {
                $parameters["driverOptions"][\PDO::MYSQL_ATTR_SSL_KEY] = \OC::$SERVERROOT . '/' . $dbSSL_key;
            }
        }

        $connection = $connectionFactory->getConnection($dbDriver, $parameters);
        $connection->executeQuery("SELECT 'user_sql'");

        return $connection;
    }

    /**
     * Save application properties.
     *
     * @return array The request status.
     *
     * @AuthorizedAdminSetting(settings=OCA\UserSQL\Settings\Admin)
     */
    public function saveProperties()
    {
        $this->logger->debug(
            "Entering saveProperties()", ["app" => $this->appName]
        );

        $properties = $this->properties->getArray();

        try {
            $this->getConnection();
        } catch (Exception $exception) {
            $this->logger->debug(
                "Returning saveProperties(): error",
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

        if (!$this->validateCryptoParams()) {
            return [
                "status" => "error", "data" => [
                    "message" => $this->localization->t(
                        "Hash algorithm parameter is out of range."
                    )
                ]
            ];
        }

        $safeStore = $this->request->getParam(str_replace(".", "-", Opt::SAFE_STORE), App::FALSE_VALUE);
        if ($safeStore !== $this->properties[Opt::SAFE_STORE]) {
            unset($this->properties[DB::HOSTNAME]);
            unset($this->properties[DB::PASSWORD]);
            unset($this->properties[DB::USERNAME]);
            unset($this->properties[DB::DATABASE]);
            unset($this->properties[DB::SSL_CA]);
            unset($this->properties[DB::SSL_CERT]);
            unset($this->properties[DB::SSL_KEY]);
            $this->properties[Opt::SAFE_STORE] = $safeStore;
        }

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
            } elseif (!is_bool($appValue) && !isset($reqValue)) {
                unset($this->properties[$key]);

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
     * Validate request crypto params.
     *
     * @return bool TRUE if crypto params are correct FALSE otherwise.
     */
    private function validateCryptoParams()
    {
        $cryptoClass = $this->request->getParam("opt-crypto_class");
        $configuration = $this->cryptoClassConfiguration($cryptoClass);

        for ($i = 0; $i < count($configuration); ++$i) {
            $reqParam = $this->request->getParam(
                "opt-crypto_param_" . $i, null
            );
            if (is_null($reqParam)) {
                return false;
            }

            $cryptoParam = $configuration[$i];
            switch ($cryptoParam->type) {
            case ChoiceParam::TYPE:
                if (!in_array($reqParam, $cryptoParam->choices)) {
                    return false;
                }
                break;
            case IntParam::TYPE:
                if ($reqParam < $cryptoParam->min || $reqParam > $cryptoParam->max) {
                    return false;
                }
                break;
            }
        }

        return true;
    }

    /**
     * Get a crypto class configuration from request.
     *
     * @param $cryptoClass string Crypto class name.
     *
     * @return array A crypto class configuration.
     */
    private function cryptoClassConfiguration($cryptoClass)
    {
        /**
         * @var $passwordAlgorithm IPasswordAlgorithm
         */
        $passwordAlgorithm = new $cryptoClass($this->localization);
        return $passwordAlgorithm->configuration();
    }

    /**
     * Clear the application cache memory.
     *
     * @return array The request status.
     *
     * @AuthorizedAdminSetting(settings=OCA\UserSQL\Settings\Admin)
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
     *
     * @AuthorizedAdminSetting(settings=OCA\UserSQL\Settings\Admin)
     */
    public function tableAutocomplete()
    {
        $this->logger->debug(
            "Entering tableAutocomplete()", ["app" => $this->appName]
        );

        try {
            $connection = $this->getConnection();
            $platform = PlatformFactory::getPlatform($connection);
            $input = $this->request->getParam("input");
            $tables = $platform->getTables($input);

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
     *
     * @AuthorizedAdminSetting(settings=OCA\UserSQL\Settings\Admin)
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
                $this->request->getParam($table),
                $this->request->getParam("input")
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
     *
     * @AuthorizedAdminSetting(settings=OCA\UserSQL\Settings\Admin)
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
     *
     * @AuthorizedAdminSetting(settings=OCA\UserSQL\Settings\Admin)
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

    /**
     * Get parameters for a password algorithm.
     *
     * @return array Password algorithm parameters.
     * @throws ReflectionException Whenever Opt class cannot be initiated.
     *
     * @AuthorizedAdminSetting(settings=OCA\UserSQL\Settings\Admin)
     */
    public function cryptoParams()
    {
        $this->logger->debug(
            "Entering cryptoParams()", ["app" => $this->appName]
        );

        $cryptoClass = $this->request->getParam("cryptoClass");
        $configuration = $this->cryptoClassConfiguration($cryptoClass);

        if ($cryptoClass === $this->properties[Opt::CRYPTO_CLASS]) {
            foreach ($configuration as $key => $value) {
                $opt = new ReflectionClass("OCA\UserSQL\Constant\Opt");
                $param = $this->properties[$opt->getConstant(
                    "CRYPTO_PARAM_" . $key
                )];

                if (!is_null($param)) {
                    $value->value = $param;
                }
            }
        }

        $this->logger->debug(
            "Returning cryptoParams(): count(" . count($configuration) . ")",
            ["app" => $this->appName]
        );

        return ["status" => "success", "data" => (array)$configuration];
    }
}
