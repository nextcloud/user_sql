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

namespace OCA\UserSQL\Repository;

use OCA\UserSQL\Constant\Query;
use OCA\UserSQL\Model\User;
use OCA\UserSQL\Query\DataQuery;

/**
 * The user repository.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class UserRepository
{
    /**
     * @var DataQuery The data query object.
     */
    private $dataQuery;

    /**
     * The class constructor.
     *
     * @param DataQuery $dataQuery The data query object.
     */
    public function __construct(DataQuery $dataQuery)
    {
        $this->dataQuery = $dataQuery;
    }

    /**
     * Get a user entity object.
     *
     * @param string $uid The user ID.
     *
     * @return User The user entity, NULL if it does not exists or
     *              FALSE on failure.
     */
    public function findByUid($uid)
    {
        return $this->dataQuery->queryEntity(
            Query::FIND_USER, User::class, [Query::UID_PARAM => $uid]
        );
    }

    /**
     * Get an array of user entity objects.
     *
     * @param string $search The search term. Defaults to "" (empty string).
     * @param int    $limit  (optional) Results limit.
     *                       Defaults to -1 (no limit).
     * @param int    $offset (optional) Results offset. Defaults to 0.
     *
     * @return User[] Array of user entity objects or FALSE on failure.
     */
    public function findAllBySearchTerm($search = "", $limit = -1, $offset = 0)
    {
        return $this->dataQuery->queryEntities(
            Query::FIND_USERS, User::class, [Query::SEARCH_PARAM => $search],
            $limit, $offset
        );
    }

    /**
     * Get the number of users.
     *
     * @param string $search The search term. Defaults to "" (empty string).
     *
     * @return int The number of users or FALSE on failure.
     */
    public function countAll($search = "")
    {
        return $this->dataQuery->queryValue(
            Query::COUNT_USERS, [Query::SEARCH_PARAM => $search]
        );
    }

    /**
     * Save an user entity object.
     *
     * @param User $user The user entity.
     *
     * @return bool TRUE on success, FALSE otherwise.
     */
    public function save($user)
    {
        return $this->dataQuery->update(
            Query::SAVE_USER, [
                Query::NAME_PARAM => $user->name,
                Query::PASSWORD_PARAM => $user->password,
                Query::EMAIL_PARAM => $user->email,
                Query::QUOTA_PARAM => $user->quota,
                Query::UID_PARAM => $user->uid
            ]
        );
    }
}
