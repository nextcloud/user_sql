<?php
/**
 * Nextcloud - user_sql
 * Copyright (C) 2018 Marcin Łojewski <dev@mlojewski.me>
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

namespace OCA\UserSQL\HashAlgorithm;

use OCA\UserSQL\HashAlgorithm\Base\BaseCrypt;
use OCA\UserSQL\HashAlgorithm\Base\Utils;

/**
 * Extended DES Crypt hashing implementation.
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class CryptExtendedDES extends BaseCrypt
{
    use Utils;

    /**
     * @inheritdoc
     */
    public function getVisibleName()
    {
        return "Extended DES (Crypt)";
    }

    /**
     * @inheritdoc
     */
    protected function getSalt()
    {
        // TODO - add support for options: iteration_count.
        return self::base64IntEncode(1000) . self::randomString(4, self::SALT_ALPHABET);
    }

    private static function base64IntEncode($number)
    {
        $alphabet = str_split(self::SALT_ALPHABET);
        $chars = array();
        $base = sizeof($alphabet);
        while ($number) {
            $rem = $number % $base;
            $number = (int)($number / $base);
            $arr[] = $alphabet[$rem];
        }
        $string = implode($chars);
        return str_pad($string, 4, '.', STR_PAD_RIGHT);
    }
}
