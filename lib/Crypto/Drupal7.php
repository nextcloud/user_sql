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

require_once "Phpass.php";

/**
 * Drupal 7 overrides of phpass hash implementation.
 *
 * @author BrandonKerr
 */
class Drupal7 extends Phpass
{
	
	/**
	 * The expected (and maximum) number of characters in a hashed password.
	 */
	const DRUPAL_HASH_LENGTH = 55;

    /**
     * @param string $password Password to encrypt.
     * @param string $setting  Hash settings.
     *
     * @return string|null Generated hash. Null on invalid settings.
     */
    private function crypt($password, $setting)
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

        $hash = hash('sha512', $salt . $password, true);
        do {
            $hash = hash('sha512', $hash . $password, true);
        } while (--$count);

        $output = substr($setting, 0, 12);
        $output .= $this->encode64($hash, strlen($hash));

        return substr($output, 0, self::DRUPAL_HASH_LENGTH);
    }


    /**
     * @inheritdoc
     */
    protected function getAlgorithmName()
    {
        return "Drupal 7";
    }
}
