<?php

/**
 * ownCloud - user_sql
 *
 * @author Andreas Böhler
 * @copyright 2012 Andreas Böhler <andreas (at) aboehler (dot) at>
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
 
namespace OCA\user_sql;

use OCA\user_sql\lib\Helper;

$helper = new \OCA\user_sql\lib\Helper();
$params = $helper -> getParameterArray();
$settings = $helper -> loadSettingsForDomain('default');

\OCP\Util::addStyle('user_sql', 'settings');
\OCP\Util::addScript('user_sql', 'settings');
\OCP\User::checkAdminUser();

// fill template
$tmpl = new \OCP\Template('user_sql', 'settings');
foreach($params as $param)
{
    $value = htmlentities($settings[$param]);
    $tmpl -> assign($param, $value);
}

$trusted_domains = \OC::$server->getConfig()->getSystemValue('trusted_domains');
$inserted = array('default');
array_splice($trusted_domains, 0, 0, $inserted);
$tmpl -> assign('allowed_domains', array_unique($trusted_domains));
// workaround to detect OC version
$ocVersion = @reset(\OCP\Util::getVersion());
$tmpl -> assign('ocVersion', $ocVersion);

return $tmpl -> fetchPage();
