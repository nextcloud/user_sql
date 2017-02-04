<?php

/**
* ownCloud - user_sql
*
* @author Andreas Böhler
* @copyright 2012-2015 Andreas Böhler <dev (at) aboehler (dot) at>
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

require_once('apps/user_sql/user_sql.php');
\OCP\App::registerAdmin('user_sql','settings');

$backend = new \OCA\user_sql\OC_USER_SQL;


// register user backend
\OC_User::useBackend($backend);
