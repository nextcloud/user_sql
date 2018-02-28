<?php
/**
 * Nextcloud - user_sql
 * Copyright (C) 2012-2018 Andreas Böhler <dev (at) aboehler (dot) at>
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

namespace OCA\user_sql\HashAlgorithm\Base;

/**
 * Base64 utilities trait.
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
trait Base64
{
    /**
     * Convert hexadecimal message to its base64 form.
     * @param $hex string Hexadecimal encoded message.
     * @return string Same message encoded in base64.
     */
    private static function hexToBase64($hex)
    {
        $hexChr = '';
        foreach (str_split($hex, 2) as $hexPair) {
            $hexChr .= chr(hexdec($hexPair));
        }
        return base64_encode($hexChr);
    }
}
