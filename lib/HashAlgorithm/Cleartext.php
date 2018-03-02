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

namespace OCA\UserSQL\HashAlgorithm;

use OCA\UserSQL\HashAlgorithm\Base\HashAlgorithm;
use OCA\UserSQL\HashAlgorithm\Base\Singleton;

/**
 * Cleartext password implementation.
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class Cleartext implements HashAlgorithm
{
    use Singleton;

    /**
     * @inheritdoc
     */
    public function getVisibleName()
    {
        return "Cleartext";
    }

    /**
     * @inheritdoc
     */
    public function getPasswordHash($password)
    {
        return $password;
    }

    /**
     * @inheritdoc
     */
    public function checkPassword($password, $dbHash)
    {
        return hash_equals($dbHash, $password);
    }
}
