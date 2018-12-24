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
 * Interface which defines all function required by a hash algorithm.
 * Please note that this interface must be implemented by every hash function supported in this app.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
interface IPasswordAlgorithm
{
    /**
     * Get the hash algorithm name.
     * This name is visible in the admin panel.
     *
     * @return string
     */
    public function getVisibleName();

    /**
     * Hash given password.
     * This value is stored in the database, when the password is changed.
     *
     * @param String $password The new password.
     * @param String $salt     Optional. Salt value.
     *
     * @return boolean True if the password was hashed successfully, false otherwise.
     */
    public function getPasswordHash($password, $salt = null);

    /**
     * Check password given by the user against hash stored in the database.
     *
     * @param String $password Password given by the user.
     * @param String $dbHash   Password hash stored in the database.
     * @param String $salt     Optional. Salt value.
     *
     * @return boolean True if the password is correct, false otherwise.
     */
    public function checkPassword($password, $dbHash, $salt = null);

    /**
     * Configuration for the algorithm.
     * The return array should contain entries of class <code>CryptoParam</code>
     *
     * @return array The configuration array.
     */
    public function configuration();
}
