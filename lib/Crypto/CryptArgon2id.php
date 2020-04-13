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

namespace OCA\UserSQL\Crypto;

use OCA\UserSQL\Crypto\Param\IntParam;
use OCP\IL10N;

/**
 * Argon2id Crypt hash implementation.
 *
 * @see    crypt()
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class CryptArgon2id extends AbstractAlgorithm
{
    /**
     * @var int Maximum memory (in bytes) that may be used to compute.
     */
    private $memoryCost;
    /**
     * @var int Maximum amount of time it may take to compute.
     */
    private $timeCost;
    /**
     * @var int Number of threads to use for computing.
     */
    private $threads;

    /**
     * The class constructor.
     *
     * @param IL10N $localization The localization service.
     * @param int   $memoryCost   Maximum memory (in bytes) that may be used
     *                            to compute.
     * @param int   $timeCost     Maximum amount of time it may take to compute.
     * @param int   $threads      Number of threads to use for computing.
     */
    public function __construct(
        IL10N $localization, $memoryCost = -1, $timeCost = -1, $threads = -1
    ) {
        if (version_compare(PHP_VERSION, "7.2.0") === -1) {
            throw new \RuntimeException(
                " PASSWORD_ARGON2ID requires PHP 7.2.0 or above."
            );
        } else {
            if ($memoryCost === -1) {
                $memoryCost = PASSWORD_ARGON2_DEFAULT_MEMORY_COST;
            }
            if ($timeCost === -1) {
                $timeCost = PASSWORD_ARGON2_DEFAULT_TIME_COST;
            }
            if ($threads === -1) {
                $threads = PASSWORD_ARGON2_DEFAULT_THREADS;
            }
        }

        parent::__construct($localization);
        $this->memoryCost = $memoryCost;
        $this->timeCost = $timeCost;
        $this->threads = $threads;
    }

    /**
     * @inheritdoc
     */
    public function checkPassword($password, $dbHash, $salt = null)
    {
        return password_verify($password, $dbHash);
    }

    /**
     * @inheritdoc
     */
    public function getPasswordHash($password, $salt = null)
    {
        return password_hash(
            $password, PASSWORD_ARGON2ID, [
                "memory_cost" => $this->memoryCost,
                "time_cost" => $this->timeCost,
                "threads" => $this->threads
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function configuration()
    {
        return [
            new IntParam(
                "Memory cost (KiB)", PASSWORD_ARGON2_DEFAULT_MEMORY_COST, 1,
                1048576
            ),
            new IntParam(
                "Time cost", PASSWORD_ARGON2_DEFAULT_TIME_COST, 1, 1024
            ),
            new IntParam("Threads", PASSWORD_ARGON2_DEFAULT_THREADS, 1, 1024)
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getAlgorithmName()
    {
        return "Argon2id (Crypt)";
    }
}
