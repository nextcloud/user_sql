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

namespace OCA\UserSQL\Model;

/**
 * The user entity.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class User
{
    /**
     * @var mixed The UID.
     */
    public $uid;
    /**
     * @var string The user's username (login name).
     */
    public $username;
    /**
     * @var string The user's email address.
     */
    public $email;
    /**
     * @var string The user quota.
     */
    public $quota;
    /**
     * @var string The user's display name.
     */
    public $name;
    /**
     * @var string The user's password (hash).
     */
    public $password;
    /**
     * @var string The user's home location.
     */
    public $home;
    /**
     * @var bool Is user account active.
     */
    public $active;
    /**
     * @var bool Can user change its avatar.
     */
    public $avatar;
    /**
     * @var string The password's salt.
     */
    public $salt;
}
