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
 * Blowfish Crypt hash implementation.
 *
 * @see    crypt()
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class CryptBlowfish extends AbstractAlgorithm
{
    /**
     * @var int Denotes the algorithmic cost that should be used.
     */
    private $cost;

    /**
     * The class constructor.
     *
     * @param IL10N $localization The localization service.
     * @param int   $cost         Denotes the algorithmic cost that should
     *                            be used.
     */
    public function __construct(IL10N $localization, $cost = 10)
    {
        parent::__construct($localization);
        $this->cost = $cost;
    }

    /**
     * @inheritdoc
     */
    public function checkPassword($password, $dbHash, $salt = null)
    {
        return password_verify($password, $dbHash);
    }

    /**
     * @inheritdoc
     */
    public function getPasswordHash($password, $salt = null)
    {
        return password_hash(
            $password, PASSWORD_BCRYPT, ["cost" => $this->cost]
        );
    }

    /**
     * @inheritdoc
     */
    public function configuration()
    {
        return [new CryptoParam("Cost", 10, 4, 31)];
    }

    /**
     * Get the algorithm name.
     *
     * @return string The algorithm name.
     */
    protected function getAlgorithmName()
    {
        return "Blowfish (Crypt)";
    }
}
