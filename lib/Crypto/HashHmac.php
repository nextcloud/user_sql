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

use OCA\UserSQL\Crypto\Param\ChoiceParam;
use OCP\IL10N;

/**
 * HMAC hash implementation.
 *
 * @see    hash_hmac()
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
class HashHmac extends AbstractAlgorithm
{
    const DEFAULT_ALGORITHM = "ripemd160";

    /**
     * @var string Hashing algorithm name.
     */
    private $hashingAlgorithm;

    /**
     * The class constructor.
     *
     * @param IL10N  $localization     The localization service.
     * @param string $hashingAlgorithm Hashing algorithm name.
     */
    public function __construct(IL10N $localization, $hashingAlgorithm = self::DEFAULT_ALGORITHM)
    {
        parent::__construct($localization);
        $this->hashingAlgorithm = $hashingAlgorithm;
    }

    /**
     * @inheritdoc
     */
    public function getPasswordHash($password, $salt = null)
    {
        return hash_hmac($this->hashingAlgorithm, $password, $salt);
    }

    /**
     * @inheritdoc
     */
    public function configuration()
    {
        return [
            new ChoiceParam("Hashing algorithm", self::DEFAULT_ALGORITHM, hash_hmac_algos())
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getAlgorithmName()
    {
        return "Hash HMAC";
    }
}
