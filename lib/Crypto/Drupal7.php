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
 * Drupal 7 overrides of phpass hash implementation.
 *
 * @author BrandonKerr
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class Drupal7 extends Phpass
{
    /**
     * The expected (and maximum) number of characters in a hashed password.
     */
    const DRUPAL_HASH_LENGTH = 55;

    /**
     * @inheritdoc
     */
    public function configuration()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function crypt($password, $setting)
    {
        return substr(parent::crypt($password, $setting), 0, self::DRUPAL_HASH_LENGTH);
    }

    /**
     * @inheritdoc
     */
    protected function hash($input)
    {
        return hash('sha512', $input, true);
    }

    /**
     * @inheritdoc
     */
    protected function getAlgorithmName()
    {
        return "Drupal 7";
    }
}
