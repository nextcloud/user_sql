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
use OCA\UserSQL\Model\Group;
use OCA\UserSQL\Query\DataQuery;

/**
 * The group repository.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class GroupRepository
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
     * Get a group entity object.
     *
     * @param string $gid The group ID.
     *
     * @return Group The group entity, NULL if it does not exists or
     *               FALSE on failure.
     */
    public function findByGid($gid)
    {
        return $this->dataQuery->queryEntity(
            Query::FIND_GROUP, Group::class, [Query::GID_PARAM => $gid]
        );
    }

    /**
     * Get all groups a user belongs to.
     *
     * @param string $uid The user ID.
     *
     * @return Group[] Array of group entity objects or FALSE on failure.
     */
    public function findAllByUid($uid)
    {
        return $this->dataQuery->queryEntities(
            Query::FIND_USER_GROUPS, Group::class, [Query::UID_PARAM => $uid]
        );
    }

    /**
     * Get a list of all user IDs belonging to the group.
     *
     * @param string $gid    The group ID.
     * @param string $search The UID search term. Defaults to "" (empty string).
     * @param int    $limit  (optional) Results limit.
     *                       Defaults to -1 (no limit).
     * @param int    $offset (optional) Results offset. Defaults to 0.
     *
     * @return string[] Array of UIDs belonging to the group
     *                  or FALSE on failure.
     */
    public function findAllUidsBySearchTerm(
        $gid, $search = "", $limit = -1, $offset = 0
    ) {
        return $this->dataQuery->queryColumn(
            Query::FIND_GROUP_UIDS,
            [Query::GID_PARAM => $gid, Query::SEARCH_PARAM => $search], $limit,
            $offset
        );
    }

    /**
     * Get a list of all user IDs and their display-name belonging to the group.
     *
     * @param string $gid    The group ID.
     * @param string $search The UID search term. Defaults to "" (empty string).
     * @param int    $limit  (optional) Results limit.
     *                       Defaults to -1 (no limit).
     * @param int    $offset (optional) Results offset. Defaults to 0.
     *
     * @return array<string, string> Array of display-names indexed by UIDs belonging to the group
     *                  or FALSE on failure.
     */
    public function findAllUsersBySearchTerm(
        $gid, $search = "", $limit = -1, $offset = 0
    ) {
		$data = $this->dataQuery->queryColumns(
            Query::FIND_GROUP_USERS,
            [Query::GID_PARAM => $gid, Query::SEARCH_PARAM => $search], $limit,
            $offset
        );
		return array_column($data, QUERY::NAME_PARAM, Query::UID_PARAM);
    }

    /**
     * Get an array of group entity objects.
     *
     * @param string $search The search term. Defaults to "" (empty string).
     * @param int    $limit  (optional) Results limit.
     *                       Defaults to -1 (no limit).
     * @param int    $offset (optional) Results offset. Defaults to 0.
     *
     * @return Group[] Array of group entity objects or FALSE on failure.
     */
    public function findAllBySearchTerm($search = "", $limit = -1, $offset = 0)
    {
        return $this->dataQuery->queryEntities(
            Query::FIND_GROUPS, Group::class, [Query::SEARCH_PARAM => $search],
            $limit, $offset
        );
    }

    /**
     * Get the number of users in given group matching the search term.
     *
     * @param string $gid    The group ID.
     * @param string $search The UID search term. Defaults to "" (empty string).
     *
     * @return int The number of users in given group matching the search term
     *             or FALSE on failure.
     */
    public function countAll($gid, $search = "")
    {
        return $this->dataQuery->queryValue(
            Query::COUNT_GROUPS,
            [Query::GID_PARAM => $gid, Query::SEARCH_PARAM => $search]
        );
    }

    /**
     * Find out if the user belongs to any admin group.
     *
     * @param string $uid The user ID.
     *
     * @return bool|null TRUE if the user belongs to any admin group,
     *                   FALSE if not, NULL on failure.
     */
    public function belongsToAdmin($uid)
    {
        return $this->dataQuery->queryValue(
            Query::BELONGS_TO_ADMIN, [Query::UID_PARAM => $uid], null
        );
    }
}
