<?php
/**
 * Nextcloud - user_sql
 * Copyright (C) 2012-2018 Andreas Böhler <dev (at) aboehler (dot) at>
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

namespace OCA\user_sql\HashAlgorithm;

/**
 * Singleton pattern trait.
 * @author Marcin Łojewski <dev@mlojewski.me>
 */
trait Singleton
{
    private static $instance;

    final private function __construct()
    {
        $this->init();
    }

    protected function init()
    {
    }

    final public static function getInstance()
    {
        return isset(static::$instance)
            ? static::$instance
            : static::$instance = new static;
    }

    final private function __clone()
    {
    }
}
