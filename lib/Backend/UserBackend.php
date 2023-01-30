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

namespace OCA\UserSQL\Backend;

use OC\User\Backend;
use OCA\UserSQL\Action\EmailSync;
use OCA\UserSQL\Action\IUserAction;
use OCA\UserSQL\Action\NameSync;
use OCA\UserSQL\Action\QuotaSync;
use OCA\UserSQL\Cache;
use OCA\UserSQL\Constant\App;
use OCA\UserSQL\Constant\DB;
use OCA\UserSQL\Constant\Opt;
use OCA\UserSQL\Crypto\IPasswordAlgorithm;
use OCA\UserSQL\Model\User;
use OCA\UserSQL\Properties;
use OCA\UserSQL\Repository\UserRepository;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IConfig;
use OCP\IL10N;
use OCP\ILogger;
use OCP\Security\Events\ValidatePasswordPolicyEvent;
use OCP\User\Backend\ABackend;
use OCP\User\Backend\ICheckPasswordBackend;
use OCP\User\Backend\ICountUsersBackend;
use OCP\User\Backend\IGetDisplayNameBackend;
use OCP\User\Backend\IGetHomeBackend;
use OCP\User\Backend\IPasswordConfirmationBackend;
use OCP\User\Backend\IProvideAvatarBackend;
use OCP\User\Backend\ISetDisplayNameBackend;
use OCP\User\Backend\ISetPasswordBackend;

/**
 * The SQL user backend manager.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
final class UserBackend extends ABackend implements
    ICheckPasswordBackend,
    ICountUsersBackend,
    IGetDisplayNameBackend,
    IGetHomeBackend,
    IPasswordConfirmationBackend,
    IProvideAvatarBackend,
    ISetDisplayNameBackend,
    ISetPasswordBackend
{
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
     * @var UserRepository The user repository.
     */
    private $userRepository;
    /**
     * @var Properties The properties array.
     */
    private $properties;
    /**
     * @var IL10N The localization service.
     */
    private $localization;
    /**
     * @var IConfig The config instance.
     */
    private $config;
    /**
     * @var IEventDispatcher The event dispatcher.
     */
    private $eventDispatcher;
    /**
     * @var IUserAction[] The actions to execute.
     */
    private $actions;

    /**
     * The default constructor.
     *
     * @param string           $AppName         The application name.
     * @param Cache            $cache           The cache instance.
     * @param ILogger          $logger          The logger instance.
     * @param Properties       $properties      The properties array.
     * @param UserRepository   $userRepository  The user repository.
     * @param IL10N            $localization    The localization service.
     * @param IConfig          $config          The config instance.
     * @param IEventDispatcher $eventDispatcher The event dispatcher.
     */
    public function __construct(
        $AppName, Cache $cache, ILogger $logger, Properties $properties,
        UserRepository $userRepository, IL10N $localization, IConfig $config,
        IEventDispatcher $eventDispatcher
    ) {
        $this->appName = $AppName;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->properties = $properties;
        $this->userRepository = $userRepository;
        $this->localization = $localization;
        $this->config = $config;
        $this->eventDispatcher = $eventDispatcher;
        $this->actions = [];

        $this->initActions();
    }

    /**
     * Initiate the actions array.
     */
    private function initActions()
    {
        if (!empty($this->properties[Opt::EMAIL_SYNC])
            && !empty($this->properties[DB::USER_EMAIL_COLUMN])
        ) {
            $this->actions[] = new EmailSync(
                $this->appName, $this->logger, $this->properties, $this->config,
                $this->userRepository
            );
        }
        if (!empty($this->properties[Opt::QUOTA_SYNC])
            && !empty($this->properties[DB::USER_QUOTA_COLUMN])
        ) {
            $this->actions[] = new QuotaSync(
                $this->appName, $this->logger, $this->properties, $this->config,
                $this->userRepository
            );
        }
        if (!empty($this->properties[Opt::NAME_SYNC])
            && !empty($this->properties[DB::USER_NAME_COLUMN])
        ) {
            $this->actions[] = new NameSync(
                $this->appName, $this->logger, $this->properties, $this->config,
                $this->userRepository
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function hasUserListings()
    {
        return true;
    }

    /**
     * Count users in the database.
     *
     * @return int The number of users.
     */
    public function countUsers()
    {
        $this->logger->debug(
            "Entering countUsers()", ["app" => $this->appName]
        );

        $cacheKey = self::class . "users#";
        $count = $this->cache->get($cacheKey);

        if (!is_null($count)) {
            $this->logger->debug(
                "Returning from cache countUsers(): $count",
                ["app" => $this->appName]
            );
            return $count;
        }

        $count = $this->userRepository->countAll("%");

        if ($count === false) {
            return 0;
        }

        $this->cache->set($cacheKey, $count);
        $this->logger->debug(
            "Returning countUsers(): $count", ["app" => $this->appName]
        );

        return $count;
    }

    /**
     * @inheritdoc
     */
    public function userExists($uid)
    {
        $this->logger->debug(
            "Entering userExists($uid)", ["app" => $this->appName]
        );

        $user = $this->getUser($uid);

        if ($user === false) {
            return false;
        }

        $exists = !is_null($user);
        $this->logger->debug(
            "Returning userExists($uid): " . ($exists ? "true" : "false"),
            ["app" => $this->appName]
        );

        return $exists;
    }

    /**
     * Get a user entity object. If it's found value from cache is used.
     *
     * @param string $uid The user ID.
     *
     * @return User The user entity, NULL if it does not exists or
     *              FALSE on failure.
     */
    private function getUser($uid)
    {
        $cacheKey = self::class . "user_" . $uid;
        $cachedUser = $this->cache->get($cacheKey);

        if (!is_null($cachedUser)) {
            $user = new User();
            foreach ($cachedUser as $key => $value) {
                $user->{$key} = $value;
            }

            $this->logger->debug(
                "Found user in cache: " . $user->uid, ["app" => $this->appName]
            );

            return $user;
        }

        $user = $this->userRepository->findByUid($uid);

        if ($user instanceof User) {
            $this->cache->set($cacheKey, $user);

            foreach ($this->actions as $action) {
                $action->doAction($user);
            }
        }

        return $user;
    }

    /**
     * @inheritdoc
     */
    public function getDisplayName($uid): string
    {
        $this->logger->debug(
            "Entering getDisplayName($uid)", ["app" => $this->appName]
        );

        $user = $this->getUser($uid);

        if (!($user instanceof User)) {
            return false;
        }

        if (is_null($user->name)) {
            return false;
        }

        $name = $user->name;
        $this->logger->debug(
            "Returning getDisplayName($uid): $name",
            ["app" => $this->appName]
        );

        return $name;
    }

    /**
     * Check if the user's password is correct then return its ID or
     * FALSE on failure.
     *
     * @param string $username The username.
     * @param string $password The password.
     *
     * @return string|bool The user ID on success, false otherwise.
     */
    public function checkPassword(string $username, string $password)
    {
        $this->logger->debug(
            "Entering checkPassword($username, *)", ["app" => $this->appName]
        );

        $passwordAlgorithm = $this->getPasswordAlgorithm();
        if ($passwordAlgorithm === null) {
            return false;
        }

        $caseSensitive = empty($this->properties[Opt::CASE_INSENSITIVE_USERNAME]);
        $emailLogin = !empty($this->properties[Opt::EMAIL_LOGIN]);
        if ($emailLogin) {
            $user = $this->userRepository->findByUsernameOrEmail($username, $caseSensitive);
        } else {
            $user = $this->userRepository->findByUsername($username, $caseSensitive);
        }

        if (!($user instanceof User)) {
            return false;
        }

        $uid = $user->uid;
        $password = $this->addSalt($user, $password);

        $isCorrect = $passwordAlgorithm->checkPassword(
            $password, $user->password, $user->salt
        );

        if ($user->active == false) {
            $this->logger->info(
                "User account is inactive for user: $uid",
                ["app" => $this->appName]
            );
            return false;
        }

        if ($isCorrect !== true) {
            $this->logger->info(
                "Invalid password attempt for user: $uid",
                ["app" => $this->appName]
            );
            return false;
        }

        $this->logger->info(
            "Successful password attempt for user: $uid",
            ["app" => $this->appName]
        );

        return $uid;
    }

    /**
     * Get a password algorithm implementation instance.
     *
     * @return IPasswordAlgorithm The password algorithm instance or FALSE
     * on failure.
     */
    private function getPasswordAlgorithm()
    {
        $cryptoType = $this->properties[Opt::CRYPTO_CLASS];
        $cryptoParam0 = $this->properties[Opt::CRYPTO_PARAM_0];
        $cryptoParam1 = $this->properties[Opt::CRYPTO_PARAM_1];
        $cryptoParam2 = $this->properties[Opt::CRYPTO_PARAM_2];
        $passwordAlgorithm = new $cryptoType(
            $this->localization, $cryptoParam0, $cryptoParam1, $cryptoParam2
        );

        if ($passwordAlgorithm === null) {
            $this->logger->error(
                "Cannot get password algorithm instance: " . $cryptoType,
                ["app" => $this->appName]
            );
        }

        return $passwordAlgorithm;
    }

    /**
     * Append or prepend salt from external column if available.
     *
     * @param User   $user     The user instance.
     * @param string $password The password.
     *
     * @return string Salted password.
     */
    private function addSalt(User $user, string $password): string
    {
        if ($user->salt !== null) {
            if (!empty($this->properties[Opt::APPEND_SALT])) {
                return $password . $user->salt;
            } elseif (!empty($this->properties[Opt::PREPEND_SALT])) {
                return $user->salt . $password;
            }
        }

        return $password;
    }

    /**
     * @inheritdoc
     */
    public function getDisplayNames($search = "", $limit = null, $offset = null)
    {
        $this->logger->debug(
            "Entering getDisplayNames($search, $limit, $offset)",
            ["app" => $this->appName]
        );

        $users = $this->getUsers(
            $search, $limit, $offset, function ($user) {
            return $user;
        }
        );

        $names = [];
        foreach ($users as $user) {
            if (is_null($user->name)) {
                $names[$user->uid] = $user->uid;
            } else {
                $names[$user->uid] = $user->name;
            }
        }

        $this->logger->debug(
            "Returning getDisplayNames($search, $limit, $offset): count("
            . count($users) . ")", ["app" => $this->appName]
        );

        return $names;
    }

    /**
     * @inheritdoc
     */
    public function getUsers($search = "", $limit = null, $offset = null, $callback = null)
    {
        $this->logger->debug(
            "Entering getUsers($search, $limit, $offset)",
            ["app" => $this->appName]
        );

        $cacheKey = self::class . "users_" . $search . "_" . $limit . "_"
            . $offset;
        $users = $this->cache->get($cacheKey);

        if (!is_null($users)) {
            $this->logger->debug(
                "Returning from cache getUsers($search, $limit, $offset): count("
                . count($users) . ")", ["app" => $this->appName]
            );
            // convert to user-model
            foreach ($users as $index => $cachedUser) {
              if (!is_array($cachedUser)) {
                break;
              }
              $user = new User();
              foreach ($cachedUser as $key => $value) {
                $user->{$key} = $value;
              }
              $users[$index] = $user;
            }

            return $users;
        }

        $users = $this->userRepository->findAllBySearchTerm(
            "%" . $search . "%", $limit, $offset
        );

        if ($users === false) {
            return [];
        }

        foreach ($users as $user) {
            $this->cache->set("user_" . $user->uid, $user);
        }

        $callback = is_callable($callback)
            ? $callback
            : function ($user) {
                return $user->uid;
            };
        $users = array_map($callback, $users);

        $this->cache->set($cacheKey, $users);
        $this->logger->debug(
            "Returning getUsers($search, $limit, $offset): count("
            . count(
                $users
            ) . ")", ["app" => $this->appName]
        );

        return $users;
    }

    /**
     * Set a user password.
     *
     * @param string $uid      The user ID.
     * @param string $password The password to set.
     *
     * @return bool TRUE if the password has been set, FALSE otherwise.
     */
    public function setPassword(string $uid, string $password): bool
    {
        $this->logger->debug(
            "Entering setPassword($uid, *)", ["app" => "user_sql"]
        );

        if (empty($this->properties[Opt::PASSWORD_CHANGE])) {
            return false;
        }

        $passwordAlgorithm = $this->getPasswordAlgorithm();
        if ($passwordAlgorithm === false) {
            return false;
        }

        $event = new ValidatePasswordPolicyEvent($password);
        $this->eventDispatcher->dispatchTyped($event);

        $user = $this->userRepository->findByUid($uid);
        if (!($user instanceof User)) {
            return false;
        }

        $password = $this->addSalt($user, $password);

        $passwordHash = $passwordAlgorithm->getPasswordHash($password);
        if ($passwordHash === false) {
            return false;
        }

        $user->password = $passwordHash;
        $result = $this->userRepository->save($user, UserRepository::PASSWORD_FIELD);

        if ($result === true) {
            $this->logger->info(
                "Password has been set successfully for user: $uid",
                ["app" => $this->appName]
            );
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getHome(string $uid)
    {
        $this->logger->debug(
            "Entering getHome($uid)", ["app" => $this->appName]
        );

        if (empty($this->properties[Opt::HOME_MODE])) {
            return false;
        }

        $home = false;
        switch ($this->properties[Opt::HOME_MODE]) {
        case App::HOME_STATIC:
            $home = $this->properties[Opt::HOME_LOCATION];
            $home = str_replace("%u", $uid, $home);
            break;
        case App::HOME_QUERY:
            $user = $this->getUser($uid);
            if (!($user instanceof User)) {
                return false;
            }
            $home = $user->home;
            break;
        }

        $this->logger->debug(
            "Returning getHome($uid): " . $home, ["app" => $this->appName]
        );

        return $home;
    }

    /**
     * Can user change its avatar.
     *
     * @param string $uid The user ID.
     *
     * @return bool TRUE if the user can change its avatar, FALSE otherwise.
     */
    public function canChangeAvatar(string $uid): bool
    {
        $this->logger->debug(
            "Entering canChangeAvatar($uid)", ["app" => $this->appName]
        );

        if (empty($this->properties[DB::USER_AVATAR_COLUMN])) {
            return !empty($this->properties[Opt::PROVIDE_AVATAR]);
        }

        $user = $this->userRepository->findByUid($uid);
        if (!($user instanceof User)) {
            return false;
        }

        $avatar = $user->avatar;
        $this->logger->debug(
            "Returning canChangeAvatar($uid): " . ($avatar ? "true"
                : "false"), ["app" => $this->appName]
        );

        return $avatar;
    }

    /**
     * Set a user display name.
     *
     * @param string $uid         The user ID.
     * @param string $displayName The display name to set.
     *
     * @return bool TRUE if the password has been set, FALSE otherwise.
     */
    public function setDisplayName(string $uid, string $displayName): bool
    {
        $this->logger->debug(
            "Entering setDisplayName($uid, $displayName)",
            ["app" => $this->appName]
        );

        if (empty($this->properties[Opt::NAME_CHANGE])) {
            return false;
        }

        $user = $this->userRepository->findByUid($uid);
        if (!($user instanceof User)) {
            return false;
        }

        $user->name = $displayName;
        $result = $this->userRepository->save($user, UserRepository::DISPLAY_NAME_FIELD);

        if ($result === true) {
            $this->logger->info(
                "Display name has been set successfully for user: $uid",
                ["app" => $this->appName]
            );
            return true;
        }

        return false;
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
            && !empty($this->properties[DB::USER_TABLE])
            && !empty($this->properties[DB::USER_UID_COLUMN])
            && !empty($this->properties[DB::USER_PASSWORD_COLUMN])
            && !empty($this->properties[Opt::CRYPTO_CLASS]);
    }

    /**
     * @inheritdoc
     */
    public function getBackendName()
    {
        return "User SQL";
    }

    /**
     * @inheritdoc
     */
    public function deleteUser($uid)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function implementsActions($actions): bool
    {
        if ($actions & Backend::SET_PASSWORD) {
            return !empty($this->properties[Opt::PASSWORD_CHANGE]);
        }

        return parent::implementsActions($actions);
    }

    /**
     * @inheritdoc
     */
    public function canConfirmPassword(string $uid): bool
    {
        return true;
    }
}
