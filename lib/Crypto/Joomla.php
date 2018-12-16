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

namespace OCA\UserSQL\Crypto;

use OCP\IL10N;

/**
 * Joomla hash implementation.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class Joomla extends AbstractAlgorithm
{
    /**
     * The class constructor.
     *
     * @param IL10N $localization The localization service.
     */
    public function __construct(IL10N $localization)
    {
        parent::__construct($localization);
    }

    /**
     * @inheritdoc
     */
    public function getPasswordHash($password, $salt = null)
    {
        $salt = Utils::randomString(
            32, "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"
        );

        return md5($password . $salt) . ":" . $salt;
    }

    /**
     * @inheritdoc
     */
    public function checkPassword($password, $dbHash, $salt = null)
    {
        return hash_equals($dbHash, self::generateHash($password, $dbHash));
    }

    private static function generateHash($password, $dbHash)
    {
        $split_salt = preg_split("/:/", $dbHash);
        $salt = false;
        if (isset($split_salt[1])) {
            $salt = $split_salt[1];
        }
        $pwHash = ($salt) ? md5($password . $salt) : md5($password);
        $pwHash .= ":" . $salt;
        return $pwHash;
    }

    /**
     * @inheritdoc
     */
    protected function getAlgorithmName()
    {
        return "Joomla MD5 Encryption";
    }
}
