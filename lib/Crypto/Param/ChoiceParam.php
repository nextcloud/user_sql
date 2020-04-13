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
 * A choice parameter of a hash algorithm.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class ChoiceParam extends CryptoParam
{
    const TYPE = "choice";

    /**
     * @var array Available choices.
     */
    public $choices;

    /**
     * Class constructor.
     *
     * @param $name      string Parameter name.
     * @param $value     mixed Parameter default value.
     * @param $choices   array Available choices.
     */
    public function __construct($name, $value = null, $choices = [])
    {
        parent::__construct(self::TYPE, $name, $value);
        $this->choices = $choices;
    }
}
