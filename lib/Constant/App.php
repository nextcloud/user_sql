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

namespace OCA\UserSQL\Constant;

/**
 * The application constants.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
final class App
{
    const FALSE_VALUE = "0";
    const TRUE_VALUE = "1";

    const HOME_QUERY = "query";
    const HOME_STATIC = "static";

    const EMAIL_FORCE_NC = "force_nc";
    const EMAIL_FORCE_SQL = "force_sql";
    const EMAIL_INITIAL = "initial";
}
