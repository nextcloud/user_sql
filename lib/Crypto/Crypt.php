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
 * Unix Crypt hash implementation.
 *
 * @see    crypt()
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class Crypt extends AbstractCrypt
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
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @inheritdoc
     */
    protected function getAlgorithmName()
    {
        return "Unix (Crypt)";
    }

    /**
     * Not used.
     */
    protected function getSalt()
    {
        return null;
    }
}
