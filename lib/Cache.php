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

namespace OCA\UserSQL;

use OC\Memcache\NullCache;
use OCA\UserSQL\Constant\App;
use OCA\UserSQL\Constant\Opt;
use OCP\ICache;
use OCP\IConfig;
use OCP\ILogger;

/**
 * Used to store key-value pairs in the cache memory.
 * If there's no distributed cache available NULL cache is used.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class Cache
{
    /**
     * @var ICache The cache instance.
     */
    private $cache;

    /**
     * The default constructor. Initiates the cache memory.
     *
     * @param string  $AppName The application name.
     * @param IConfig $config  The config instance.
     * @param ILogger $logger  The logger instance.
     */
    public function __construct($AppName, IConfig $config, ILogger $logger)
    {
        $factory = \OC::$server->getMemCacheFactory();
        $useCache = $config->getAppValue(
            $AppName, Opt::USE_CACHE, App::FALSE_VALUE
        );

        if ($useCache === App::FALSE_VALUE) {
            $this->cache = new NullCache();
        } elseif ($factory->isAvailable()) {
            $this->cache = $factory->createDistributed();
            $logger->debug("Distributed cache initiated.", ["app" => $AppName]);
        } else {
            $logger->warning(
                "There's no distributed cache available, fallback to null cache.",
                ["app" => $AppName]
            );
            $this->cache = new NullCache();
        }
    }

    /**
     * Fetch a value from the cache memory.
     *
     * @param string $key The cache value key.
     *
     * @return mixed|NULL Cached value or NULL if there's no value stored.
     */
    public function get($key)
    {
        return $this->cache->get($key);
    }

    /**
     * Store a value in the cache memory.
     *
     * @param string $key   The cache value key.
     * @param mixed  $value The value to store.
     * @param int    $ttl   (optional) TTL in seconds. Defaults to 1 hour.
     *
     * @return bool TRUE on success, FALSE otherwise.
     */
    public function set($key, $value, $ttl = 3600)
    {
        return $this->cache->set($key, $value, $ttl);
    }

    /**
     * Clear the cache of all entries.
     *
     * @return bool TRUE on success, FALSE otherwise.
     */
    public function clear()
    {
        return $this->cache->clear();
    }
}
