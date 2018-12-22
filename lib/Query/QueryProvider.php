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

namespace OCA\UserSQL\Query;

use OCA\UserSQL\Constant\DB;
use OCA\UserSQL\Constant\Opt;
use OCA\UserSQL\Constant\Query;
use OCA\UserSQL\Properties;

/**
 * Provides queries array.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class QueryProvider implements \ArrayAccess
{
    /**
     * @var Properties The properties array.
     */
    private $properties;
    /**
     * @var array The queries array.
     */
    private $queries;

    /**
     * The class constructor.
     *
     * @param Properties $properties The properties array.
     */
    public function __construct(Properties $properties)
    {
        $this->properties = $properties;
        $this->loadQueries();
    }

    /**
     * Load queries to the array.
     */
    private function loadQueries()
    {
        $group = $this->properties[DB::GROUP_TABLE];
        $userGroup = $this->properties[DB::USER_GROUP_TABLE];
        $user = $this->properties[DB::USER_TABLE];

        $gAdmin = $this->properties[DB::GROUP_ADMIN_COLUMN];
        $gGID = $this->properties[DB::GROUP_GID_COLUMN];
        $gName = $this->properties[DB::GROUP_NAME_COLUMN];

        $uActive = $this->properties[DB::USER_ACTIVE_COLUMN];
        $uAvatar = $this->properties[DB::USER_AVATAR_COLUMN];
        $uEmail = $this->properties[DB::USER_EMAIL_COLUMN];
        $uHome = $this->properties[DB::USER_HOME_COLUMN];
        $uName = $this->properties[DB::USER_NAME_COLUMN];
        $uPassword = $this->properties[DB::USER_PASSWORD_COLUMN];
        $uQuota = $this->properties[DB::USER_QUOTA_COLUMN];
        $uSalt = $this->properties[DB::USER_SALT_COLUMN];
        $uUID = $this->properties[DB::USER_UID_COLUMN];

        $ugGID = $this->properties[DB::USER_GROUP_GID_COLUMN];
        $ugUID = $this->properties[DB::USER_GROUP_UID_COLUMN];

        $emailParam = Query::EMAIL_PARAM;
        $gidParam = Query::GID_PARAM;
        $nameParam = Query::NAME_PARAM;
        $passwordParam = Query::PASSWORD_PARAM;
        $quotaParam = Query::QUOTA_PARAM;
        $searchParam = Query::SEARCH_PARAM;
        $uidParam = Query::UID_PARAM;

        $reverseActiveOpt = $this->properties[Opt::REVERSE_ACTIVE];

        $groupColumns
            = "g.$gGID AS gid, " .
            (empty($gName) ? "g." . $gGID : "g." . $gName) . " AS name, " .
            (empty($gAdmin) ? "false" : "g." . $gAdmin) . " AS admin";
        $userColumns
            = "u.$uUID AS uid, " .
            (empty($uName) ? "u." . $uUID : "u." . $uName) . " AS name, " .
            (empty($uEmail) ? "null" : "u." . $uEmail) . " AS email, " .
            (empty($uQuota) ? "null" : "u." . $uQuota) . " AS quota, " .
            (empty($uHome) ? "null" : "u." . $uHome) . " AS home, " .
            (empty($uActive) ? "true" : (empty($reverseActiveOpt) ? "" : "NOT ") . "u." . $uActive) . " AS active, " .
            (empty($uAvatar) ? "false" : "u." . $uAvatar) . " AS avatar, " .
            (empty($uSalt) ? "null" : "u." . $uSalt) . " AS salt";

        $this->queries = [
            Query::BELONGS_TO_ADMIN =>
                "SELECT COUNT(g.$gGID) > 0 AS admin " .
                "FROM $group g, $userGroup ug " .
                "WHERE ug.$ugGID = g.$gGID " .
                "AND ug.$ugUID = :$uidParam " .
                "AND g.$gAdmin",

            Query::COUNT_GROUPS =>
                "SELECT COUNT(ug.$ugGID) " .
                "FROM $userGroup ug " .
                "WHERE ug.$ugGID = :$gidParam " .
                "AND ug.$ugUID " .
                "LIKE :$searchParam",

            Query::COUNT_USERS =>
                "SELECT COUNT(u.$uUID) AS count " .
                "FROM $user u " .
                "WHERE u.$uUID LIKE :$searchParam",

            Query::FIND_GROUP =>
                "SELECT $groupColumns " .
                "FROM $group g " .
                "WHERE g.$gGID = :$gidParam",

            Query::FIND_GROUP_USERS =>
                "SELECT ug.$ugUID AS uid " .
                "FROM $userGroup ug " .
                "WHERE ug.$ugGID = :$gidParam " .
                "AND ug.$ugUID " .
                "LIKE :$searchParam " .
                "ORDER BY ug.$ugUID",

            Query::FIND_GROUPS =>
                "SELECT $groupColumns " .
                "FROM $group g " .
                "WHERE g.$gGID LIKE :$searchParam " .
                "ORDER BY g.$gGID",

            Query::FIND_USER =>
                "SELECT $userColumns, u.$uPassword AS password " .
                "FROM $user u " .
                "WHERE u.$uUID = :$uidParam",

            Query::FIND_USER_CASE_INSENSITIVE =>
                "SELECT $userColumns, u.$uPassword AS password " .
                "FROM $user u " .
                "WHERE lower(u.$uUID) = lower(:$uidParam)",

            Query::FIND_USER_GROUPS =>
                "SELECT $groupColumns " .
                "FROM $group g, $userGroup ug " .
                "WHERE ug.$ugGID = g.$gGID " .
                "AND ug.$ugUID = :$uidParam " .
                "ORDER BY g.$gGID",

            Query::FIND_USERS =>
                "SELECT $userColumns " .
                "FROM $user u " .
                "WHERE u.$uUID LIKE :$searchParam " .
                "ORDER BY u.$uUID",

            Query::UPDATE_DISPLAY_NAME =>
                "UPDATE $user " .
                "SET $uName = :$nameParam " .
                "WHERE $uUID = :$uidParam",

            Query::UPDATE_EMAIL =>
                "UPDATE $user " .
                "SET $uEmail = :$emailParam " .
                "WHERE $uUID = :$uidParam",

            Query::UPDATE_PASSWORD =>
                "UPDATE $user " .
                "SET $uPassword = :$passwordParam " .
                "WHERE $uUID = :$uidParam",

            Query::UPDATE_QUOTA =>
                "UPDATE $user " .
                "SET $uQuota = :$quotaParam " .
                "WHERE $uUID = :$uidParam",
        ];
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return isset($this->queries[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        if (isset($this->queries[$offset])) {
            return $this->queries[$offset];
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->queries[$offset] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->queries[$offset]);
    }
}
