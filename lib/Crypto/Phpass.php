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
 * phpass hashing implementation.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class Phpass extends AbstractAlgorithm
{
    /**
     * @var PasswordHash
     */
    private $hasher;

    /**
     * The class constructor.
     *
     * @param IL10N $localization The localization service.
     * @param int   $hashCostLog2 Log2 Hash cost.
     * @param bool  $hashPortable Use portable hash implementation.
     */
    public function __construct(
        IL10N $localization, $hashCostLog2 = 8, $hashPortable = true
    ) {
        parent::__construct($localization);
        $this->hasher = new PasswordHash($hashCostLog2, $hashPortable);
    }

    /**
     * @inheritdoc
     */
    public function checkPassword($password, $dbHash)
    {
        return $this->hasher->CheckPassword($password, $dbHash);
    }

    /**
     * @inheritdoc
     */
    public function getPasswordHash($password)
    {
        return $this->hasher->HashPassword($password);
    }

    /**
     * @inheritdoc
     */
    protected function getAlgorithmName()
    {
        return "Portable PHP password";
    }
}
