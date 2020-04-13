<?php
/**
 * Nextcloud - user_sql
 *
 * @copyright 2019 Björn Kinscher <dev@bjoern-kinscher.de>
 * @author    Björn Kinscher <dev@bjoern-kinscher.de>
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

namespace OCA\UserSQL\Action;

use OCA\UserSQL\Constant\App;
use OCA\UserSQL\Constant\Opt;
use OCA\UserSQL\Model\User;
use OCA\UserSQL\Properties;
use OCA\UserSQL\Repository\UserRepository;
use OCP\IConfig;
use OCP\ILogger;

/**
 * Synchronizes the user name.
 *
 * @author Björn Kinscher <dev@bjoern-kinscher.de>
 */
class NameSync implements IUserAction
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
     * @var Properties The properties array.
     */
    private $properties;
    /**
     * @var IConfig The config instance.
     */
    private $config;
    /**
     * @var UserRepository The user repository.
     */
    private $userRepository;

    /**
     * The default constructor.
     *
     * @param string         $appName        The application name.
     * @param ILogger        $logger         The logger instance.
     * @param Properties     $properties     The properties array.
     * @param IConfig        $config         The config instance.
     * @param UserRepository $userRepository The user repository.
     */
    public function __construct(
        $appName, ILogger $logger, Properties $properties, IConfig $config,
        UserRepository $userRepository
    ) {
        $this->appName = $appName;
        $this->logger = $logger;
        $this->properties = $properties;
        $this->config = $config;
        $this->userRepository = $userRepository;
    }

    /**
     * @inheritdoc
     * @throws \OCP\PreConditionNotMetException
     */
    public function doAction(User $user)
    {
        $this->logger->debug(
            "Entering NameSync#doAction($user->uid)", ["app" => $this->appName]
        );

        $ncName = $this->config->getUserValue(
            $user->uid, "settings", "displayName", ""
        );

        $result = false;

        switch ($this->properties[Opt::NAME_SYNC]) {
        case App::SYNC_INITIAL:
            if (empty($ncName) && !empty($user->name)) {
                $this->config->setUserValue(
                    $user->uid, "settings", "displayName", $user->name
                );
                \OC::$server->getUserManager()->get($user->uid)->setDisplayName($user->name);
            }

            $result = true;
            break;
        case App::SYNC_FORCE_NC:
            if (!empty($ncName) && $user->name !== $ncName) {
                $user = $this->userRepository->findByUid($user->uid);
                if (!($user instanceof User)) {
                    break;
                }

                $user->name = $ncName;
                $result = $this->userRepository->save($user, UserRepository::DISPLAY_NAME_FIELD);
            }

            break;
        case App::SYNC_FORCE_SQL:
            if (!empty($user->name) && $user->name !== $ncName) {
                $this->config->setUserValue(
                    $user->uid, "settings", "displayName", $user->name
                );
                \OC::$server->getUserManager()->get($user->uid)->setDisplayName($user->name);
            }

            $result = true;
            break;
        }

        $this->logger->debug(
            "Returning NameSync#doAction($user->uid): " . ($result ? "true"
                : "false"),
            ["app" => $this->appName]
        );

        return $result;
    }
}
