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
 * An integer parameter of a hash algorithm.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class IntParam extends CryptoParam
{
    const TYPE = "int";

    /**
     * @var int Minimal value for parameter.
     */
    public $min;
    /**
     * @var int Maximum value for parameter.
     */
    public $max;

    /**
     * Class constructor.
     *
     * @param $name  string Parameter name.
     * @param $value int Parameter default value.
     * @param $min   int Minimal value for parameter.
     * @param $max   int Maximum value for parameter.
     */
    public function __construct($name, $value = null, $min = null, $max = null)
    {
        parent::__construct(self::TYPE, $name, $value);
        $this->min = $min;
        $this->max = $max;
    }
}
