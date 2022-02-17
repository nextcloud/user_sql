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

namespace OCA\UserSQL\Settings;

use OCA\UserSQL\Properties;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\IDelegatedSettings;

/**
 * The administrator's settings page.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class Admin implements IDelegatedSettings
{
    /**
     * @var string The application name.
     */
    private $appName;
    /**
     * @var Properties The properties array.
     */
    private $properties;

    /**
     * The class constructor,
     *
     * @param string     $AppName    The application name.
     * @param Properties $properties The properties array.
     */
    public function __construct($AppName, Properties $properties)
    {
        $this->appName = $AppName;
        $this->properties = $properties;
    }

    /**
     * @inheritdoc
     */
    public function getForm()
    {
        return new TemplateResponse($this->appName, "admin", $this->properties->getArray());
    }

    /**
     * @inheritdoc
     */
    public function getSection()
    {
        return $this->appName;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return 25;
    }

    public function getName(): ?string {
        return null; // Only one setting in this section
    }

    public function getAuthorizedAppConfig(): array {
        return []; // Custom controller
    }
}
