<?php
/**
 * Nextcloud - user_sql
 * Copyright (C) 2012-2018 Andreas Böhler <dev (at) aboehler (dot) at>
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

namespace OCA\user_sql\HashAlgorithm;

/**
 * Interface which defines all function required by a hash algorithm.
 * Please note that this interface must be implemented by every hash function supported in this app.
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
interface HashAlgorithm
{
    /**
     * Used by reflection to get the class instance.
     * @return HashAlgorithm
     */
    public static function getInstance();

    /**
     * Get the hash algorithm name.
     * This name is visible in the admin panel.
     * @return string
     */
    public function getVisibleName();

    /**
     * Hash given password.
     * This value is stored in the database, when the password is changed.
     * @param String $password The new password.
     * @return boolean True if the password was hashed successfully, false otherwise.
     */
    public function getPasswordHash($password);

    /**
     * Check password given by the user against hash stored in the database.
     * @param String $password Password given by the user.
     * @param String $dbHash Password hash stored in the database.
     * @return boolean True if the password is correct, false otherwise.
     */
    public function checkPassword($password, $dbHash);
}
