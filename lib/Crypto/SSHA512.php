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
 * SSHA512 hash implementation.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class SSHA512 extends SSHA
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
    public function getPrefix()
    {
        return "{SSHA512}";
    }

    /**
     * @inheritdoc
     */
    public function getAlgorithm()
    {
        return "sha512";
    }

    /**
     * @inheritdoc
     */
    public function getHashLength()
    {
        return 64;
    }

    /**
     * @inheritdoc
     */
    protected function getAlgorithmName()
    {
        return "SSHA512";
    }
}
