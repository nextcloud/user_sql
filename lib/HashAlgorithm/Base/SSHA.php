<?php
/**
 * Nextcloud - user_sql
 * Copyright (C) 2018 Marcin Łojewski <dev@mlojewski.me>
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

namespace OCA\UserSQL\HashAlgorithm\Base;

/**
 * SSHA* hashing implementation.
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
abstract class SSHA implements HashAlgorithm
{
    use Singleton;
    use Utils;

    /**
     * @inheritdoc
     */
    public function checkPassword($password, $dbHash)
    {
        $saltedPassword = base64_decode(preg_replace("/" . $this->getPrefix() . "/i", "", $dbHash));
        $salt = substr($saltedPassword, -(strlen($saltedPassword) - 32));
        $hash = self::ssha($password, $salt);

        return hash_equals($dbHash, $hash);
    }

    /**
     * Get hash prefix eg. {SSHA256}.
     * @return string
     */
    public abstract function getPrefix();

    /**
     * Encrypt using SSHA256 algorithm
     * @param string $password The password.
     * @param string $salt The salt to use.
     * @return string The hashed password, prefixed by {SSHA256}.
     */
    private function ssha($password, $salt)
    {
        return $this->getPrefix() . base64_encode(hash($this->getAlgorithm(), $password . $salt, true) . $salt);
    }

    /**
     * Get algorithm used by the hash() function.
     * @see hash()
     * @return string
     */
    public abstract function getAlgorithm();

    /**
     * @inheritdoc
     */
    public function getPasswordHash($password)
    {
        return self::ssha($password,
            self::randomString(32, "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"));
    }
}
