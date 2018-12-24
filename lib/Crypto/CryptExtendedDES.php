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
 * Extended DES Crypt hash implementation.
 *
 * @see    crypt()
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class CryptExtendedDES extends AbstractCrypt
{
    /**
     * @var int The number of iterations.
     */
    private $iterationCount;

    /**
     * The class constructor.
     *
     * @param IL10N $localization   The localization service.
     * @param int   $iterationCount The number of iterations.
     */
    public function __construct(IL10N $localization, $iterationCount = 1000)
    {
        parent::__construct($localization);
        $this->iterationCount = $iterationCount;
    }

    /**
     * @inheritdoc
     */
    public function configuration()
    {
        return [new CryptoParam("Iterations", 1000, 0, 16777215)];
    }

    /**
     * @inheritdoc
     */
    protected function getSalt()
    {
        return self::encodeIterationCount($this->iterationCount)
            . Utils::randomString(4, self::SALT_ALPHABET);
    }

    /**
     * Get the number of iterations as describe below.
     * The 4 bytes of iteration count are encoded as printable characters,
     * 6 bits per character, least significant character first.
     * The values 0 to 63 are encoded as "./0-9A-Za-z".
     *
     * @param int $number The number of iterations.
     *
     * @return string
     */
    private static function encodeIterationCount($number)
    {
        $alphabet = str_split(self::SALT_ALPHABET);
        $chars = array();
        $base = sizeof($alphabet);

        while ($number) {
            $rem = $number % $base;
            $number = (int)($number / $base);
            $chars[] = $alphabet[$rem];
        }

        return str_pad(implode($chars), 4, ".", STR_PAD_RIGHT);
    }

    /**
     * @inheritdoc
     */
    protected function getAlgorithmName()
    {
        return "Extended DES (Crypt)";
    }
}
