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
 * Implements standard Unix DES-based algorithm or
 * alternative algorithms that may be available on the system.
 * @see crypt()
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
abstract class BaseCrypt implements HashAlgorithm
{
    use Singleton;

    const SALT_ALPHABET = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

    /**
     * @inheritdoc
     */
    abstract public function getVisibleName();

    /**
     * @inheritdoc
     */
    public function checkPassword($password, $dbHash)
    {
        return hash_equals($dbHash, crypt($password, $dbHash));
    }

    /**
     * @inheritdoc
     */
    public function getPasswordHash($password)
    {
        return crypt($password, self::getSalt());
    }

    /**
     * Generate salt for hashing algorithm.
     * @return string
     */
    protected abstract function getSalt();
}
