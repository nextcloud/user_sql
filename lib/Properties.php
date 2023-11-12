<?php
/**
 * Nextcloud - user_sql
 *
 * @copyright 2020 Marcin Łojewski <dev@mlojewski.me>
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

namespace OCA\UserSQL;

use OCA\UserSQL\Constant\App;
use OCA\UserSQL\Constant\DB;
use OCA\UserSQL\Constant\Opt;
use OCP\IConfig;
use OCP\ILogger;

/**
 * Store and retrieve application properties.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class Properties implements \ArrayAccess
{
    /**
     * @var string The cache key name.
     */
    const CACHE_KEY = "Properties_data";

    /**
     * @var string The application name.
     */
    private $appName;
    /**
     * @var IConfig The config instance.
     */
    private $config;
    /**
     * @var ILogger The logger instance.
     */
    private $logger;
    /**
     * @var Cache The cache instance.
     */
    private $cache;
    /**
     * @var array The properties array.
     */
    private $data;
    /**
     * @var boolean Store confidential data in file.
     */
    private $safeStore;

    /**
     * The default constructor.
     *
     * @param string  $AppName The application name.
     * @param IConfig $config  The config instance.
     * @param ILogger $logger  The logger instance.
     * @param Cache   $cache   The cache instance.
     */
    public function __construct(
        $AppName, IConfig $config, ILogger $logger, Cache $cache
    ) {
        $this->appName = $AppName;
        $this->config = $config;
        $this->logger = $logger;
        $this->cache = $cache;

        $this->loadProperties();
    }

    /**
     * Load the application properties.
     *
     * First the values are fetched from the cache memory.
     * If these are not available, the database values are fetched.
     */
    private function loadProperties()
    {
        $this->data = $this->cache->get(self::CACHE_KEY);

        if (!is_null($this->data)) {
            return;
        }

        $params = $this->getParameterArray();
        $this->data = [];
        $this->safeStore
            = $this->config->getAppValue($this->appName, Opt::SAFE_STORE, App::FALSE_VALUE) === App::TRUE_VALUE;

        foreach ($params as $param) {
            if ($this->isSystemValue($param)) {
                $value = $this->config->getSystemValue("user_sql." . $param, null);
            } else {
                $value = $this->config->getAppValue($this->appName, $param, null);
            }

            if ($this->isBooleanParam($param)) {
                if ($value === App::FALSE_VALUE) {
                    $value = false;
                } elseif ($value === App::TRUE_VALUE) {
                    $value = true;
                }
            }

            $this->data[$param] = $value;
        }

        $this->store();

        $this->logger->debug(
            "The application properties has been loaded.",
            ["app" => $this->appName]
        );
    }

    /**
     * Return an array with all supported parameters.
     *
     * @return array Array containing strings of the parameters.
     */
    private function getParameterArray()
    {
        $params = [];

        foreach ([DB::class, Opt::class] as $class) {
            try {
                $reflection = new \ReflectionClass($class);
                $params = array_merge(
                    $params, array_values($reflection->getConstants())
                );
            } catch (\ReflectionException $exception) {
                $this->logger->logException(
                    $exception, ["app" => $this->appName]
                );
            }
        }

        return $params;
    }

    /**
     * @param $param string Parameter name.
     *
     * @return bool TRUE if this is a system wide parameter FALSE otherwise.
     */
    private function isSystemValue($param)
    {
        return $this->safeStore && in_array($param, array(DB::HOSTNAME, DB::PASSWORD, DB::USERNAME, DB::DATABASE, DB::SSL_CA, DB::SSL_CERT, DB::SSL_KEY));
    }

    /**
     * Is given parameter a boolean parameter.
     *
     * @param $param string Parameter name.
     *
     * @return bool Is a boolean parameter.
     */
    private function isBooleanParam($param)
    {
        return in_array(
            $param, [
                Opt::APPEND_SALT, Opt::CASE_INSENSITIVE_USERNAME, Opt::EMAIL_LOGIN,
                Opt::NAME_CHANGE, Opt::PASSWORD_CHANGE, Opt::PREPEND_SALT,
                Opt::PROVIDE_AVATAR, Opt::REVERSE_ACTIVE, Opt::SAFE_STORE,
                Opt::USE_CACHE
            ]
        );
    }

    /**
     * Store properties in the cache memory.
     */
    private function store()
    {
        $this->cache->set(self::CACHE_KEY, $this->data);
    }

    /**
     * Get properties array.
     *
     * @return array The properties array.
     */
    public function getArray()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists(mixed $offset):bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet(mixed $offset):mixed
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetSet(mixed $offset, mixed $value):void
    {
        if ($offset == Opt::SAFE_STORE) {
            $this->safeStore = ($value === App::TRUE_VALUE);
        }

        if ($this->isSystemValue($offset)) {
            $this->config->setSystemValue("user_sql." . $offset, $value);
        } else {
            $this->config->setAppValue($this->appName, $offset, $value);
        }

        if ($this->isBooleanParam($offset)) {
            if ($value === App::FALSE_VALUE) {
                $value = false;
            } elseif ($value === App::TRUE_VALUE) {
                $value = true;
            }
        }

        $this->data[$offset] = $value;

        if ($offset === Opt::USE_CACHE && $value === false) {
            $this->cache->clear();
        } else {
            $this->store();
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset(mixed $offset):void
    {
        if ($offset == Opt::SAFE_STORE) {
            $this->safeStore = App::FALSE_VALUE;
        }

        if ($this->isSystemValue($offset)) {
            $this->config->deleteSystemValue("user_sql." . $offset);
        } else {
            $this->config->deleteAppValue($this->appName, $offset);
        }
        unset($this->data[$offset]);
    }
}
