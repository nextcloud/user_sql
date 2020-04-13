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

use OCA\UserSQL\Crypto\Param\IntParam;
use OCP\IL10N;

/**
 * SHA256 Crypt hash implementation.
 *
 * @see    crypt()
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class CryptSHA256 extends AbstractCrypt
{
    /**
     * @var int The number of rounds.
     */
    private $rounds;

    /**
     * The class constructor.
     *
     * @param IL10N $localization The localization service.
     * @param int   $rounds       The number of rounds.
     *                            This value must be between 1000 and 999999999.
     */
    public function __construct(IL10N $localization, $rounds = 5000)
    {
        parent::__construct($localization);
        $this->rounds = $rounds;
    }

    /**
     * @inheritdoc
     */
    public function configuration()
    {
        return [new IntParam("Rounds", 5000, 1000, 999999999)];
    }

    /**
     * @inheritdoc
     */
    protected function getSalt()
    {
        return "$5\$rounds=" . $this->rounds . "$" . Utils::randomString(
                16, self::SALT_ALPHABET
            ) . "$";
    }

    /**
     * @inheritdoc
     */
    protected function getAlgorithmName()
    {
        return "SHA256 (Crypt)";
    }
}
