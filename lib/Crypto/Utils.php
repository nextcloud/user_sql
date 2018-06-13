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
 * Cryptographic utilities.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
final class Utils
{
    /**
     * Convert hexadecimal message to its base64 form.
     *
     * @param $hex string The hexadecimal encoded message.
     *
     * @return string The same message encoded in base64.
     */
    public static function hexToBase64($hex)
    {
        $hexChr = "";
        foreach (str_split($hex, 2) as $hexPair) {
            $hexChr .= chr(hexdec($hexPair));
        }
        return base64_encode($hexChr);
    }

    /**
     * Generate random string from given alphabet.
     *
     * @param $length   int The output string length.
     * @param $alphabet string The output string alphabet.
     *
     * @return string Random string from given alphabet.
     */
    public static function randomString($length, $alphabet)
    {
        $string = "";
        for ($idx = 0; $idx != $length; ++$idx) {
            $string .= $alphabet[mt_rand(0, strlen($alphabet) - 1)];
        }
        return $string;
    }
}
