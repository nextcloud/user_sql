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

namespace OCA\UserSQL\Backend;

use OCA\UserSQL\Cache;
use OCA\UserSQL\Constant\DB;
use OCA\UserSQL\Constant\Opt;
use OCA\UserSQL\Model\Group;
use OCA\UserSQL\Properties;
use OCA\UserSQL\Repository\GroupRepository;
use OCP\Group\Backend\ABackend;
use OCP\Group\Backend\ICountUsersBackend;
use OCP\Group\Backend\IGroupDetailsBackend;
use OCP\Group\Backend\IIsAdminBackend;
use OCP\Group\Backend\ISearchableGroupBackend;
use OCP\ILogger;
use OCP\IUserManager;

use OC\User\LazyUser;

/**
 * The SQL group backend manager.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
final class GroupBackend extends ABackend implements
    ICountUsersBackend,
    IGroupDetailsBackend,
    IIsAdminBackend,
	ISearchableGroupBackend
{
    const USER_SQL_GID = "user_sql";

    /**
     * @var string The application name.
     */
    private $appName;
    /**
     * @var ILogger The logger instance.
     */
    private $logger;
    /**
     * @var Cache The cache instance.
     */
    private $cache;
    /**
     * @var GroupRepository The group repository.
     */
    private $groupRepository;
    /**
     * @var Properties The properties array.
     */
    private $properties;

    /**
     * The default constructor.
     *
     * @param string          $AppName         The application name.
     * @param Cache           $cache           The cache instance.
     * @param ILogger         $logger          The logger instance.
     * @param Properties      $properties      The properties array.
     * @param GroupRepository $groupRepository The group repository.
     */
    public function __construct(
        $AppName, Cache $cache, ILogger $logger, Properties $properties,
        GroupRepository $groupRepository
    ) {
        $this->appName = $AppName;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->properties = $properties;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @inheritdoc
     */
    public function getGroups($search = "", $limit = null, $offset = null)
    {
        $this->logger->debug(
            "Entering getGroups($search, $limit, $offset)",
            ["app" => $this->appName]
        );

        $cacheKey = self::class . "groups_" . $search . "_" . $limit . "_"
            . $offset;
        $groups = $this->cache->get($cacheKey);

        if (!is_null($groups)) {
            $this->logger->debug(
                "Returning from cache getGroups($search, $limit, $offset): count("
                . count($groups) . ")", ["app" => $this->appName]
            );
            return $groups;
        }

        $groups = $this->groupRepository->findAllBySearchTerm("%" . $search . "%", $limit, $offset);
        $groups = $this->setCacheAndMap($cacheKey, $groups);

        $this->logger->debug(
            "Returning getGroups($search, $limit, $offset): count(" . count(
                $groups
            ) . ")", ["app" => $this->appName]
        );

        return $groups;
    }

    /**
     * Set groups in cache and map them to GIDs.
     *
     * @param $cacheKey string Cache key.
     * @param $groups   array Fetched groups.
     *
     * @return array Array of GIDs.
     */
    private function setCacheAndMap($cacheKey, $groups)
    {
        if ($groups === false) {
            return $this->defaultGroupSet() ? [self::USER_SQL_GID] : [];
        }

        foreach ($groups as $group) {
            $this->cache->set("group_" . $group->gid, $group);
        }

        $groups = array_map(
            function ($group) {
                return $group->gid;
            }, $groups
        );
        if ($this->defaultGroupSet()) {
            $groups[] = self::USER_SQL_GID;
        }

        $this->cache->set($cacheKey, $groups);
        return $groups;
    }

    /**
     * @return bool Whether default group option is set.
     */
    private function defaultGroupSet()
    {
        return !empty($this->properties[Opt::DEFAULT_GROUP]);
    }

    /**
     * @inheritdoc
     */
    public function countUsersInGroup(string $gid, string $search = ""): int
    {
        $this->logger->debug(
            "Entering countUsersInGroup($gid, $search)",
            ["app" => $this->appName]
        );

        $cacheKey = self::class . "users#_" . $gid . "_" . $search;
        $count = $this->cache->get($cacheKey);

        if (!is_null($count)) {
            $this->logger->debug(
                "Returning from cache countUsersInGroup($gid, $search): $count",
                ["app" => $this->appName]
            );
            return $count;
        }

        $count = $this->groupRepository->countAll($this->substituteGid($gid), "%" . $search . "%");

        if ($count === false) {
            return 0;
        }

        $this->cache->set($cacheKey, $count);
        $this->logger->debug(
            "Returning countUsersInGroup($gid, $search): $count",
            ["app" => $this->appName]
        );

        return $count;
    }

    /**
     * Substitute GID to '%' if it's default group.
     *
     * @param $gid string Group ID.
     *
     * @return string '%' if it's default group otherwise given GID.
     */
    private function substituteGid($gid)
    {
        return $this->defaultGroupSet() && $gid === self::USER_SQL_GID ? "%" : $gid;
    }

    /**
     * @inheritdoc
     */
    public function inGroup($uid, $gid)
    {
        $this->logger->debug(
            "Entering inGroup($uid, $gid)", ["app" => $this->appName]
        );

        $cacheKey = self::class . "user_group_" . $uid . "_" . $gid;
        $inGroup = $this->cache->get($cacheKey);

        if (!is_null($inGroup)) {
            $this->logger->debug(
                "Returning from cache inGroup($uid, $gid): " . ($inGroup
                    ? "true" : "false"), ["app" => $this->appName]
            );
            return $inGroup;
        }

        $inGroup = in_array($gid, $this->getUserGroups($uid));

        $this->cache->set($cacheKey, $inGroup);
        $this->logger->debug(
            "Returning inGroup($uid, $gid): " . ($inGroup ? "true" : "false"),
            ["app" => $this->appName]
        );

        return $inGroup;
    }

    /**
     * @inheritdoc
     */
    public function getUserGroups($uid)
    {
        $this->logger->debug(
            "Entering getUserGroups($uid)", ["app" => $this->appName]
        );

        $cacheKey = self::class . "user_groups_" . $uid;
        $groups = $this->cache->get($cacheKey);

        if (!is_null($groups)) {
            $this->logger->debug(
                "Returning from cache getUserGroups($uid): count(" . count(
                    $groups
                ) . ")", ["app" => $this->appName]
            );
            return $groups;
        }

        $groups = $this->groupRepository->findAllByUid($uid);
        $groups = $this->setCacheAndMap($cacheKey, $groups);

        $this->logger->debug(
            "Returning getUserGroups($uid): count(" . count(
                $groups
            ) . ")", ["app" => $this->appName]
        );

        return $groups;
    }

    /**
     * @inheritdoc
     */
    public function groupExists($gid)
    {
        $this->logger->debug(
            "Entering groupExists($gid)", ["app" => $this->appName]
        );

        if ($this->defaultGroupSet() && $gid === self::USER_SQL_GID) {
            return true;
        }

        $group = $this->getGroup($gid);

        if ($group === false) {
            return false;
        }

        $exists = !is_null($group);
        $this->logger->debug(
            "Returning groupExists($gid): " . ($exists ? "true" : "false"),
            ["app" => $this->appName]
        );

        return $exists;
    }

    /**
     * Get a group entity object. If it's found value from cache is used.
     *
     * @param $gid $uid The group ID.
     *
     * @return Group The group entity, NULL if it does not exists or
     *               FALSE on failure.
     */
    private function getGroup($gid)
    {
        $cacheKey = self::class . "group_" . $gid;
        $cachedGroup = $this->cache->get($cacheKey);

        if (!is_null($cachedGroup)) {
            if ($cachedGroup === false) {
                $this->logger->debug(
                    "Found null group in cache: $gid", ["app" => $this->appName]
                );
                return null;
            }

            $group = new Group();
            foreach ($cachedGroup as $key => $value) {
                $group->{$key} = $value;
            }

            $this->logger->debug(
                "Found group in cache: " . $group->gid,
                ["app" => $this->appName]
            );

            return $group;
        }

        $group = $this->groupRepository->findByGid($gid);

        if ($group instanceof Group) {
            $this->cache->set($cacheKey, $group);
        } elseif (is_null($group)) {
            $this->cache->set($cacheKey, false);
        }

        return $group;
    }

    /**
     * @inheritdoc
     */
    public function usersInGroup($gid, $search = "", $limit = -1, $offset = 0)
    {
        $this->logger->debug(
            "Entering usersInGroup($gid, $search, $limit, $offset)",
            ["app" => $this->appName]
        );

        $cacheKey = self::class . "group_uids_" . $gid . "_" . $search . "_"
            . $limit . "_" . $offset;
        $uids = $this->cache->get($cacheKey);

        if (!is_null($uids)) {
            $this->logger->debug(
                "Returning from cache usersInGroup($gid, $search, $limit, $offset): count("
                . count($uids) . ")", ["app" => $this->appName]
            );
            return $uids;
        }

        $uids = $this->groupRepository->findAllUidsBySearchTerm(
            $this->substituteGid($gid), "%" . $search . "%", $limit, $offset
        );

        if ($uids === false) {
            return [];
        }

        $this->cache->set($cacheKey, $uids);
        $this->logger->debug(
            "Returning usersInGroup($gid, $search, $limit, $offset): count("
            . count($uids) . ")", ["app" => $this->appName]
        );

        return $uids;
    }

	/**
     * @inheritdoc
     */
	public function searchInGroup(string $gid, string $search = '', int $limit = -1, int $offset = 0): array
	{
		$this->logger->debug(
            "Entering searchInGroup($gid, $search, $limit, $offset)",
            ["app" => $this->appName]
        );

        $cacheKey = self::class . "group_users_" . $gid . "_" . $search . "_"
            . $limit . "_" . $offset;
        $names = $this->cache->get($cacheKey);

        if ($names === null) {
			$names = $this->groupRepository->findAllUsersBySearchTerm(
				$this->substituteGid($gid), "%" . $search . "%", $limit, $offset
			);

			if ($names === false) {
				return [];
			}

			$this->cache->set($cacheKey, $names);
			$this->logger->debug(
				"Using from DB searchInGroup($gid, $search, $limit, $offset): count("
					. count($names) . ")", ["app" => $this->appName]
			);
		} else {
            $this->logger->debug(
                "Using from cache searchInGroup($gid, $search, $limit, $offset): count("
					. count($names) . ")", ["app" => $this->appName]
            );
        }

		$users = [];
		$userManager = \OCP\Server::get(IUserManager::class);
		foreach ($names as $uid => $name) {
			$users[$uid] = new LazyUser($uid, $userManager, $name);
		}

		return $users;
	}

    /**
     * @inheritdoc
     */
    public function isAdmin(string $uid = null): bool
    {
        $this->logger->debug(
            "Entering isAdmin($uid)", ["app" => $this->appName]
        );

        if (empty($this->properties[DB::GROUP_ADMIN_COLUMN]) || $uid === null) {
            return false;
        }

        $cacheKey = self::class . "admin_" . $uid;
        $admin = $this->cache->get($cacheKey);

        if (!is_null($admin)) {
            $this->logger->debug(
                "Returning from cache isAdmin($uid): " . ($admin ? "true"
                    : "false"), ["app" => $this->appName]
            );
            return $admin;
        }

        $admin = $this->groupRepository->belongsToAdmin($uid);

        if (is_null($admin)) {
            return false;
        }

        $this->cache->set($cacheKey, $admin);
        $this->logger->debug(
            "Returning isAdmin($uid): " . ($admin ? "true" : "false"),
            ["app" => $this->appName]
        );

        return $admin;
    }

    /**
     * @inheritdoc
     */
    public function getGroupDetails(string $gid): array
    {
        $this->logger->debug(
            "Entering getGroupDetails($gid)", ["app" => $this->appName]
        );

        if ($this->defaultGroupSet() && $gid === self::USER_SQL_GID) {
            return ["displayName" => $this->properties[Opt::DEFAULT_GROUP]];
        }

        $group = $this->getGroup($gid);

        if (!($group instanceof Group)) {
            return [];
        }

        $details = ["displayName" => $group->name];
        $this->logger->debug(
            "Returning getGroupDetails($gid): " . implode(", ", $details),
            ["app" => $this->appName]
        );

        return $details;
    }

    /**
     * Check if this backend is correctly set and can be enabled.
     *
     * @return bool TRUE if all necessary options for this backend
     *              are configured, FALSE otherwise.
     */
    public function isConfigured()
    {
        return !empty($this->properties[DB::DATABASE])
            && !empty($this->properties[DB::DRIVER])
            && !empty($this->properties[DB::HOSTNAME])
            && !empty($this->properties[DB::USERNAME])
            && !empty($this->properties[DB::GROUP_TABLE])
            && !empty($this->properties[DB::USER_GROUP_TABLE])
            && !empty($this->properties[DB::GROUP_GID_COLUMN])
            && !empty($this->properties[DB::USER_GROUP_GID_COLUMN])
            && !empty($this->properties[DB::USER_GROUP_UID_COLUMN]);
    }
}
