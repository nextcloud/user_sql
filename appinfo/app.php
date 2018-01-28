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

require_once(__DIR__ . '/../lib/user_sql.php');
require_once __DIR__ . '/../lib/group_sql.php';
$backend = new \OCA\user_sql\OC_USER_SQL;
$group_backend = new \OCA\user_sql\OC_GROUP_SQL;

\OC::$server->getUserManager()->registerBackend($backend);
\OC::$server->getGroupManager()->addBackend($group_backend);
?>
