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

namespace OCA\UserSQL\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\QueryException;

/**
 * The application bootstrap class.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class Application extends App
{
    /**
     * The class constructor.
     *
     * @param array $urlParams An array with variables extracted
     *                         from the routes.
     */
    public function __construct(array $urlParams = array())
    {
        parent::__construct("user_sql", $urlParams);
    }

    /**
     * Register the application backends
     * if all necessary configuration is provided.
     *
     * @throws QueryException If the query container's could not be resolved
     */
    public function registerBackends()
    {
        $userBackend = $this->getContainer()->query(
            '\OCA\UserSQL\Backend\UserBackend'
        );
        $groupBackend = $this->getContainer()->query(
            '\OCA\UserSQL\Backend\GroupBackend'
        );

        if ($userBackend->isConfigured()) {
            \OC::$server->getUserManager()->registerBackend($userBackend);
        }
        if ($groupBackend->isConfigured()) {
            \OC::$server->getGroupManager()->addBackend($groupBackend);
        }
    }
}
