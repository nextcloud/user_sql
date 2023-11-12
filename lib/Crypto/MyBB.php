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
 * MyBB hash implementation.
 *
 * @author Sebijk
 */
class MyBB extends AbstractAlgorithm
{
    /**
     * @inheritdoc
     */
    public function getPasswordHash($password, $salt = null)
    {
        if (is_null($salt)) {
            return false;
        }
        return md5(md5($salt).md5($password));
    }

    /**
     * @inheritdoc
     */
    protected function getAlgorithmName()
    {
        return "MyBB";
    }
}