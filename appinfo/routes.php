<?php
/**
 * Nextcloud - user_sql
 *
 * @copyright 2012-2015 Andreas Böhler <dev (at) aboehler (dot) at>
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

use OCA\UserSQL\AppInfo\Application;

$application = new Application();
$application->registerRoutes(
    $this, [
        "routes" => [
            [
                "name" => "settings#verifyDbConnection",
                "url" => "/settings/db/verify",
                "verb" => "POST"
            ],
            [
                "name" => "settings#saveProperties",
                "url" => "/settings/properties",
                "verb" => "POST"
            ],
            [
                "name" => "settings#clearCache",
                "url" => "/settings/cache/clear",
                "verb" => "POST"
            ],
            [
                "name" => "settings#tableAutocomplete",
                "url" => "/settings/autocomplete/table",
                "verb" => "POST"
            ],
            [
                "name" => "settings#userTableAutocomplete",
                "url" => "/settings/autocomplete/table/user",
                "verb" => "POST"
            ],
            [
                "name" => "settings#userGroupTableAutocomplete",
                "url" => "/settings/autocomplete/table/user_group",
                "verb" => "POST"
            ],
            [
                "name" => "settings#groupTableAutocomplete",
                "url" => "/settings/autocomplete/table/group",
                "verb" => "POST"
            ],
            [
                "name" => "settings#cryptoParams",
                "url" => "/settings/crypto/params",
                "verb" => "GET"
            ],
        ]
    ]
);
