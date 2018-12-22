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

namespace OCA\UserSQL\Crypto;

use OCP\IL10N;

/**
 * The abstract password algorithm class.
 * Each algorithm should extend this class, as it provides very base
 * functionality which seems to be necessary for every implementation.
 *
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
abstract class AbstractAlgorithm implements IPasswordAlgorithm
{
    /**
     * @var IL10N The localization service.
     */
    private $localization;

    /**
     * The class constructor.
     *
     * @param IL10N $localization The localization service.
     */
    public function __construct(IL10N $localization)
    {
        $this->localization = $localization;
    }

    /**
     * @inheritdoc
     */
    public function getVisibleName()
    {
        return $this->localization->t($this->getAlgorithmName());
    }

    /**
     * Get the algorithm name.
     *
     * @return string The algorithm name.
     */
    protected abstract function getAlgorithmName();

    /**
     * @inheritdoc
     */
    public function checkPassword($password, $dbHash, $salt = null)
    {
        return hash_equals($dbHash, $this->getPasswordHash($password, $salt));
    }

    /**
     * @inheritdoc
     */
    public abstract function getPasswordHash($password, $salt = null);

    /**
     * @inheritdoc
     */
    public function configuration()
    {
        return [];
    }
}
