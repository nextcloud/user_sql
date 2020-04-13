<?php
/**
 * Nextcloud - user_sql
 *
 * @copyright 2020 Marcin Łojewski <dev@mlojewski.me>
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

namespace OCA\UserSQL\Crypto\Param;

/**
 * A parameter of a hash algorithm.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class CryptoParam
{
    /**
     * @var string Type name used in JS.
     */
    public $type;
    /**
     * @var string Parameter name.
     */
    public $name;
    /**
     * @var mixed Parameter default value.
     */
    public $value;

    /**
     * Class constructor.
     *
     * @param $type  string Type name used in JS.
     * @param $name  string Parameter name.
     * @param $value mixed Parameter default value.
     */
    public function __construct($type, $name, $value = null)
    {
        $this->type = $type;
        $this->name = $name;
        $this->value = $value;
    }
}
