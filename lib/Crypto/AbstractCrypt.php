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
 * Abstract Unix Crypt hashing implementation.
 * The hashing algorithm depends on the chosen salt.
 *
 * @see    crypt()
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
abstract class AbstractCrypt extends AbstractAlgorithm
{
    /**
     * The chars used in the salt.
     */
    const SALT_ALPHABET = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

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
        return crypt($password, $this->getSalt());
    }

    /**
     * Generate a salt string for the hashing algorithm.
     *
     * @return string The salt string.
     */
    protected abstract function getSalt();
}
