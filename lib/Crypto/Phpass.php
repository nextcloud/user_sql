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
 * phpass hash implementation.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class Phpass extends AbstractAlgorithm
{
    const ITOA64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    private $iterationCount;

    /**
     * The class constructor.
     *
     * @param IL10N $localization   The localization service.
     * @param int   $iterationCount Iteration count (log2).
     *                              This value must be between 4 and 31.
     */
    public function __construct(IL10N $localization, $iterationCount = 8)
    {
        parent::__construct($localization);
        $this->iterationCount = $iterationCount;
    }

    /**
     * @inheritdoc
     */
    public function checkPassword($password, $dbHash, $salt = null)
    {
        return hash_equals($dbHash, $this->crypt($password, $dbHash));
    }

    /**
     * @param string $password Password to encrypt.
     * @param string $setting  Hash settings.
     *
     * @return string|null Generated hash. Null on invalid settings.
     */
    protected function crypt($password, $setting)
    {
        $countLog2 = strpos(self::ITOA64, $setting[3]);
        if ($countLog2 < 7 || $countLog2 > 30) {
            return null;
        }

        $count = 1 << $countLog2;

        $salt = substr($setting, 4, 8);
        if (strlen($salt) !== 8) {
            return null;
        }

        $hash = $this->hash($salt . $password);
        do {
            $hash = $this->hash($hash . $password);
        } while (--$count);

        $output = substr($setting, 0, 12);
        $output .= $this->encode64($hash, strlen($hash));

        return $output;
    }

    /**
     * Apply hash function to input.
     *
     * @param string $input Input string.
     *
     * @return string Hashed input.
     */
    protected function hash($input)
    {
        return md5($input, true);
    }

    /**
     * Encode binary input to base64 string.
     *
     * @param string $input Binary data.
     * @param int    $count Data size.
     *
     * @return string Base64 encoded data.
     */
    private function encode64($input, $count)
    {
        $output = '';
        $i = 0;
        do {
            $value = ord($input[$i++]);
            $output .= self::ITOA64[$value & 0x3f];
            if ($i < $count) {
                $value |= ord($input[$i]) << 8;
            }
            $output .= self::ITOA64[($value >> 6) & 0x3f];
            if ($i++ >= $count) {
                break;
            }
            if ($i < $count) {
                $value |= ord($input[$i]) << 16;
            }
            $output .= self::ITOA64[($value >> 12) & 0x3f];
            if ($i++ >= $count) {
                break;
            }
            $output .= self::ITOA64[($value >> 18) & 0x3f];
        } while ($i < $count);

        return $output;
    }

    /**
     * @inheritdoc
     */
    public function getPasswordHash($password, $salt = null)
    {
        return $this->crypt($password, $this->genSalt());
    }

    /**
     * Generate salt for the hash.
     *
     * @return string Salt string.
     */
    private function genSalt()
    {
        $output = '$P$';
        $output .= self::ITOA64[min($this->iterationCount + 5, 30)];
        $output .= $this->encode64(random_bytes(6), 6);

        return $output;
    }

    /**
     * @inheritdoc
     */
    public function configuration()
    {
        return [new IntParam("Iterations (log2)", 8, 4, 31)];
    }

    /**
     * @inheritdoc
     */
    protected function getAlgorithmName()
    {
        return "Portable PHP password";
    }
}
