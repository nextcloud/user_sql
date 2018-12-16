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

/**
 * WCF2 hash implementation.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class WCF2 extends AbstractCrypt
{
    /**
     * @inheritdoc
     */
    public function checkPassword($password, $dbHash, $salt = null)
    {
        return hash_equals($dbHash, crypt(crypt($password, $dbHash), $dbHash));
    }

    /**
     * @inheritdoc
     */
    public function getPasswordHash($password, $salt = null)
    {
        $salt = $this->getSalt();
        return crypt(crypt($password, $salt), $salt);
    }

    /**
     * @inheritdoc
     */
    protected function getSalt()
    {
        return "$2a$08$" . Utils::randomString(22, self::SALT_ALPHABET) . "$";
    }

    /**
     * @inheritdoc
     */
    protected function getAlgorithmName()
    {
        return "WoltLab Community Framework 2.x";
    }
}
