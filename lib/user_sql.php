<?php

/**
 * nextCloud - user_sql
 *
 * @author Andreas Böhler and contributors
 * @copyright 2012-2015 Andreas Böhler <dev (at) aboehler (dot) at>
 *
 * credits go to Ed W for several SQL injection fixes and caching support
 * credits go to Frédéric France for providing Joomla support
 * credits go to Mark Jansenn for providing Joomla 2.5.18+ / 3.2.1+ support
 * credits go to Dominik Grothaus for providing SSHA256 support and fixing a few bugs
 * credits go to Sören Eberhardt-Biermann for providing multi-host support
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
use OC\User\Backend;

use \OCA\user_sql\lib\Helper;
use OCP\IConfig;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Notification\IManager as INotificationManager;
use OCP\Util;

abstract class BackendUtility {
    protected $access;

    /**
     * constructor, make sure the subclasses call this one!
     * @param Access $access an instance of Access for LDAP interaction
     */
    public function __construct(Access $access) {
        $this->access = $access;
    }
}


class OC_USER_SQL extends BackendUtility implements \OCP\IUserBackend,
                                                        \OCP\UserInterface
{
    protected $cache;
    protected $settings;
    protected $helper;
    protected $session_cache_name;
    protected $ocConfig;

    /**
     * The default constructor. It loads the settings for the given domain
     * and tries to connect to the database.
     */
    public function __construct()
    {
        $memcache = \OC::$server->getMemCacheFactory();
        if ( $memcache -> isAvailable())
        {
            $this -> cache = $memcache -> create();
        }
        $this -> helper = new \OCA\user_sql\lib\Helper();
        $domain = \OC::$server->getRequest()->getServerHost();
        $this -> settings = $this -> helper -> loadSettingsForDomain($domain);
        $this -> ocConfig = \OC::$server->getConfig();
        $this -> helper -> connectToDb($this -> settings);
        $this -> session_cache_name = 'USER_SQL_CACHE';
        return false;
    }

    /**
     * Sync the user's E-Mail address with the address stored by ownCloud.
     * We have three (four) sync modes:
     *   - none:     Does nothing
     *   - initial:  Do the sync only once from SQL -> ownCloud
     *   - forcesql: The SQL database always wins and sync to ownCloud
     *   - forceoc:  ownCloud always wins and syncs to SQL
     *
     * @param string $uid The user's ID to sync
     * @return bool Success or Fail
     */
    private function doEmailSync($uid)
    {
        Util::writeLog('OC_USER_SQL', "Entering doEmailSync for UID: $uid",
                             Util::DEBUG);
        if($this -> settings['col_email'] === '')
            return false;

        if($this -> settings['set_mail_sync_mode'] === 'none')
            return false;

        $ocUid = $uid;
        $uid = $this -> doUserDomainMapping($uid);

        $row = $this -> helper -> runQuery('getMail', array('uid' => $uid));
        if($row === false)
        {
            return false;
        }
        $newMail = $row[$this -> settings['col_email']];

        $currMail = $this->ocConfig->getUserValue(    $ocUid,
                                                    'settings',
                                                    'email', '');

        switch($this -> settings['set_mail_sync_mode'])
        {
            case 'initial':
                if($currMail === '')
                    $this->ocConfig->setUserValue(    $ocUid,
                                                    'settings',
                                                    'email',
                                                    $newMail);
                break;
            case 'forcesql':
                //if($currMail !== $newMail)
                    $this->ocConfig->setUserValue(    $ocUid,
                                                    'settings',
                                                    'email',
                                                    $newMail);
                break;
            case 'forceoc':
                if(($currMail !== '') && ($currMail !== $newMail))
                {
                    $row = $this -> helper -> runQuery('setMail',
                                                        array('uid' => $uid,
                                                        'currMail' => $currMail)
                                                        , true);

                    if($row === false)
                    {
                        Util::writeLog('OC_USER_SQL',
                        "Could not update E-Mail address in SQL database!",
                        Util::ERROR);
                    }
                }
                break;
        }

        return true;
    }

    /**
     * This maps the username to the specified domain name.
     * It can only append a default domain name.
     *
     * @param string $uid The UID to work with
     * @return string The mapped UID
     */
    private function doUserDomainMapping($uid)
    {
        $uid = trim($uid);

        if($this -> settings['set_default_domain'] !== '')
        {
            Util::writeLog('OC_USER_SQL', "Append default domain: ".
               $this -> settings['set_default_domain'], Util::DEBUG);
            if(strpos($uid, '@') === false)
            {
                $uid .= "@" . $this -> settings['set_default_domain'];
            }
        }

        $uid = strtolower($uid);
        Util::writeLog('OC_USER_SQL', 'Returning mapped UID: ' . $uid,
         Util::DEBUG);
        return $uid;
    }

    /**
     * Return the actions implemented by this backend
     * @param $actions
     * @return bool
     */
    public function implementsActions($actions)
    {
        return (bool)((Backend::CHECK_PASSWORD
            | Backend::GET_DISPLAYNAME
            | Backend::COUNT_USERS
            | ($this -> settings['set_allow_pwchange'] === 'true' ?
                Backend::SET_PASSWORD : 0)
            | ($this -> settings['set_enable_gethome'] === 'true' ?
                Backend::GET_HOME : 0)
            ) & $actions);
    }

    /**
     * Checks if this backend has user listing support
     * @return bool
     */
    public function hasUserListings()
    {
        return true;
    }

    /**
     * Return the user's home directory, if enabled
     * @param string $uid The user's ID to retrieve
     * @return mixed The user's home directory or false
     */
    public function getHome($uid)
    {
        Util::writeLog('OC_USER_SQL', "Entering getHome for UID: $uid",
                            Util::DEBUG);

        if($this -> settings['set_enable_gethome'] !== 'true')
            return false;

        $uidMapped = $this -> doUserDomainMapping($uid);
        $home = false;

        switch($this->settings['set_gethome_mode'])
        {
            case 'query':
                Util::writeLog('OC_USER_SQL',
                            "getHome with Query selected, running Query...",
                            Util::DEBUG);
                $row = $this -> helper -> runQuery('getHome',
                                                array('uid' => $uidMapped));
                if($row === false)
                {
                    Util::writeLog('OC_USER_SQL',
                                "Got no row, return false",
                                Util::DEBUG);
                    return false;
                }
                $home = $row[$this -> settings['col_gethome']];
            break;

            case 'static':
                Util::writeLog('OC_USER_SQL',
                                    "getHome with static selected",
                                    Util::DEBUG);
                $home = $this -> settings['set_gethome'];
                $home = str_replace('%ud', $uidMapped, $home);
                $home = str_replace('%u', $uid, $home);
                $home = str_replace('%d',
                                $this -> settings['set_default_domain'],
                                $home);
            break;
        }
        Util::writeLog('OC_USER_SQL',
                        "Returning getHome for UID: $uid with Home $home",
                        Util::DEBUG);
        return $home;
    }

    /**
     * Create a new user account using this backend
     * @return bool always false, as we can't create users
     */
    public function createUser()
    {
        // Can't create user
        Util::writeLog('OC_USER_SQL',
        'Not possible to create local users from web'.
        ' frontend using SQL user backend', Util::ERROR);
        return false;
    }

    /**
     * Delete a user account using this backend
     * @param string $uid The user's ID to delete
     * @return bool always false, as we can't delete users
     */
    public function deleteUser($uid)
    {
        // Can't delete user
        Util::writeLog('OC_USER_SQL', 'Not possible to delete local users'.
            ' from web frontend using SQL user backend', Util::ERROR);
        return false;
    }

    /**
     * Set (change) a user password
     * This can be enabled/disabled in the settings (set_allow_pwchange)
     *
     * @param string $uid      The user ID
     * @param string $password The user's new password
     * @return bool The return status
     */
    public function setPassword($uid, $password)
    {
        // Update the user's password - this might affect other services, that
        // use the same database, as well
        Util::writeLog('OC_USER_SQL', "Entering setPassword for UID: $uid",
         Util::DEBUG);

        if($this -> settings['set_allow_pwchange'] !== 'true')
            return false;

        $uid = $this -> doUserDomainMapping($uid);

        $row = $this -> helper -> runQuery('getPass', array('uid' => $uid));
        if($row === false)
        {
            return false;
        }
        $old_password = $row[$this -> settings['col_password']];

        // Added and disabled updating passwords for Drupal 7 WD 2018-01-04
        if($this -> settings['set_crypt_type'] === 'drupal')
        {
            return false;
        }
        elseif($this -> settings['set_crypt_type'] === 'joomla2')
        {
            if(!class_exists('\PasswordHash'))
                require_once('PasswordHash.php');
            $hasher = new \PasswordHash(10, true);
            $enc_password = $hasher -> HashPassword($password);
        }
        // Redmine stores the salt separatedly, this doesn't play nice with
        // the way we check passwords
        elseif($this -> settings['set_crypt_type'] === 'redmine')
        {
            $salt = $this -> helper -> runQuery('getRedmineSalt',
                        array('uid' => $uid));
            if(!$salt)
                return false;
            $enc_password = sha1($salt['salt'].sha1($password));

        }
        elseif($this -> settings['set_crypt_type'] === 'sha1')
        {
            $enc_password = sha1($password);
        }
        elseif($this -> settings['set_crypt_type'] === 'system')
        {
            $prefix=substr($old_password,0,2);
             if ($prefix==="$2")
                {
                    $enc_password = $this->pw_hash($password);
                }
            else
            {
             if (($prefix==="$1") or ($prefix[0] != "$")) //old md5 or DES
                       {
                           //Update encryption algorithm
                           $prefix="$6"; //change to sha512
                       }

              $newsalt=$this->create_systemsalt();
            $enc_password=crypt($password,$prefix ."$" . $newsalt);
            }

        }
        elseif($this -> settings['set_crypt_type'] === 'password_hash')
        {
                    $enc_password = $this->pw_hash($password);
        }
        elseif($this -> settings['set_crypt_type'] === 'courier_md5')
        {
            $enc_password = '{MD5}'.OC_USER_SQL::hex_to_base64(md5($password));
        }
        elseif($this -> settings['set_crypt_type'] === 'courier_md5raw')
        {
            $enc_password = '{MD5RAW}'.md5($password);
        }
        elseif($this -> settings['set_crypt_type'] === 'courier_sha1')
        {
            $enc_password = '{SHA}'.OC_USER_SQL::hex_to_base64(sha1($password));
        }
        elseif($this -> settings['set_crypt_type'] === 'courier_sha256')
        {
            $enc_password = '{SHA256}'.OC_USER_SQL::hex_to_base64(hash('sha256', $password, false));
        }
         else
        {
            $enc_password = $this -> pacrypt($password, $old_password);
        }
        $res = $this -> helper -> runQuery('setPass',
                    array('uid' => $uid, 'enc_password' => $enc_password),
                    true);
        if($res === false)
        {
            Util::writeLog('OC_USER_SQL', "Could not update password!",
                                Util::ERROR);
            return false;
        }
        Util::writeLog('OC_USER_SQL',
                    "Updated password successfully, return true",
                    Util::DEBUG);
        return true;
    }

    /**
     * Check if the password is correct
     * @param string $uid      The username
     * @param string $password The password
     * @return bool true/false
     *
     * Check if the password is correct without logging in the user
     */
    public function checkPassword($uid, $password)
    {
        Util::writeLog('OC_USER_SQL',
                    "Entering checkPassword() for UID: $uid",
                    Util::DEBUG);

        $uid = $this -> doUserDomainMapping($uid);

            $row = $this -> helper -> runQuery('getPass', array('uid' => $uid));
            if($row === false)
            {
                Util::writeLog('OC_USER_SQL', "Got no row, return false", Util::DEBUG);
                return false;
            }
            $db_pass = $row[$this -> settings['col_password']];

        Util::writeLog('OC_USER_SQL', "Encrypting and checking password",
                            Util::DEBUG);
        // Added handling for Drupal 7 passwords WD 2018-01-04
        if($this -> settings['set_crypt_type'] === 'drupal')
        {
            if(!function_exists('user_check_password'))
                require_once('drupal.php');
            $ret = user_check_password($password, $db_pass);
        }
        // Joomla 2.5.18 switched to phPass, which doesn't play nice with the
        // way we check passwords
        elseif($this -> settings['set_crypt_type'] === 'joomla2')
        {
            if(!class_exists('\PasswordHash'))
                require_once('PasswordHash.php');
            $hasher = new \PasswordHash(10, true);
            $ret = $hasher -> CheckPassword($password, $db_pass);
        }
       elseif($this -> settings['set_crypt_type'] === 'password_hash')
        {
            $ret = password_verify($password,$db_pass);
        }
        // Redmine stores the salt separatedly, this doesn't play nice with the
        // way we check passwords
        elseif($this -> settings['set_crypt_type'] === 'redmine')
        {
            $salt = $this -> helper -> runQuery('getRedmineSalt',
                                                array('uid' => $uid));
            if(!$salt)
                return false;
            $ret = sha1($salt['salt'].sha1($password)) === $db_pass;
        }

        elseif($this -> settings['set_crypt_type'] == 'sha1')
        {
            $ret = $this->hash_equals(sha1($password) , $db_pass);
        }
        elseif($this -> settings['set_crypt_type'] === 'courier_md5')
        {
            $ret = '{MD5}'.OC_USER_SQL::hex_to_base64(md5($password)) === $db_pass;
        }
        elseif($this -> settings['set_crypt_type'] === 'courier_md5raw')
        {
            $ret = '{MD5RAW}'.md5($password) === $db_pass;
        }
        elseif($this -> settings['set_crypt_type'] === 'courier_sha1')
        {
            $ret = '{SHA}'.OC_USER_SQL::hex_to_base64(sha1($password)) === $db_pass;
        }
        elseif($this -> settings['set_crypt_type'] === 'courier_sha256')
        {
            $ret = '{SHA256}'.OC_USER_SQL::hex_to_base64(hash('sha256', $password, false)) === $db_pass;
        } else

        {
           // $ret = $this -> pacrypt($password, $db_pass) === $db_pass;
            $ret = $this->hash_equals($this -> pacrypt($password, $db_pass),
                                        $db_pass);
        }
        if($ret)
        {
            Util::writeLog('OC_USER_SQL',
                                "Passwords matching, return true",
                                Util::DEBUG);
            if($this -> settings['set_strip_domain'] === 'true')
            {
                $uid = explode("@", $uid);
                $uid = $uid[0];
            }
            return $uid;
        } else
        {
            Util::writeLog('OC_USER_SQL',
                            "Passwords do not match, return false",
                            Util::DEBUG);
            return false;
        }
    }

    /**
     * Count the number of users
     * @return int The user count
     */
    public function countUsers()
    {
        Util::writeLog('OC_USER_SQL', "Entering countUsers()",
                            Util::DEBUG);

        $search = "%".$this -> doUserDomainMapping("");
        $userCount = $this -> helper -> runQuery('countUsers',
                                                array('search' => $search));
        if($userCount === false)
        {
            $userCount = 0;
        }
        else {
            $userCount = reset($userCount);
        }

        Util::writeLog('OC_USER_SQL', "Return usercount: ".$userCount,
                            Util::DEBUG);
        return $userCount;
    }

    /**
     * Get a list of all users
     * @param string $search The search term (can be empty)
     * @param int $limit     The search limit (can be null)
     * @param int $offset    The search offset (can be null)
     * @return array with all uids
     */
    public function getUsers($search = '', $limit = null, $offset = null)
    {
        Util::writeLog('OC_USER_SQL',
        "Entering getUsers() with Search: $search, ".
        "Limit: $limit, Offset: $offset", Util::DEBUG);
        $users = array();

        if($search !== '')
        {
            $search = "%".$this -> doUserDomainMapping($search."%")."%";
        }
        else
        {
           $search = "%".$this -> doUserDomainMapping("")."%";
        }

        $rows = $this -> helper -> runQuery('getUsers',
                                            array('search' => $search),
                                            false,
                                            true,
                                            array('limit' => $limit,
                                                'offset' => $offset));
        if($rows === false)
            return array();

        foreach($rows as $row)
        {
            $uid = $row[$this -> settings['col_username']];
            if($this -> settings['set_strip_domain'] === 'true')
            {
                $uid = explode("@", $uid);
                $uid = $uid[0];
            }
            $users[] = strtolower($uid);
        }
        Util::writeLog('OC_USER_SQL', "Return list of results",
                            Util::DEBUG);
        return $users;
    }

    /**
     * Check if a user exists
     * @param string $uid the username
     * @return boolean
     */
    public function userExists($uid)
    {

        $cacheKey = 'sql_user_exists_' . $uid;
        $cacheVal = $this -> getCache ($cacheKey);
        Util::writeLog('OC_USER_SQL',
                        "userExists() for UID: $uid cacheVal: $cacheVal",
                        Util::DEBUG);
        if(!is_null($cacheVal))
            return (bool)$cacheVal;

        Util::writeLog('OC_USER_SQL',
                        "Entering userExists() for UID: $uid",
                        Util::DEBUG);

        // Only if the domain is removed for internal user handling,
        // we should add the domain back when checking existance
        if($this -> settings['set_strip_domain'] === 'true')
        {
            $uid = $this -> doUserDomainMapping($uid);
        }

        $exists = (bool)$this -> helper -> runQuery('userExists',
                                            array('uid' => $uid));;
        $this -> setCache ($cacheKey, $exists, 60);

        if(!$exists)
        {
            Util::writeLog('OC_USER_SQL',
                    "Empty row, user does not exists, return false",
                    Util::DEBUG);
            return false;
        } else
        {
            Util::writeLog('OC_USER_SQL', "User exists, return true",
                Util::DEBUG);
            return true;
        }

    }

    /**
     * Get the display name of the user
     * @param string $uid The user ID
     * @return mixed The user's display name or FALSE
     */
    public function getDisplayName($uid)
    {
        Util::writeLog('OC_USER_SQL',
                "Entering getDisplayName() for UID: $uid",
                Util::DEBUG);

        $this -> doEmailSync($uid);
        $uid = $this -> doUserDomainMapping($uid);

        if(!$this -> userExists($uid))
        {
            return false;
        }

        $row = $this -> helper -> runQuery('getDisplayName',
                                            array('uid' => $uid));

        if(!$row)
        {
            Util::writeLog('OC_USER_SQL',
            "Empty row, user has no display name or ".
            "does not exist, return false",
            Util::DEBUG);
            return false;
        } else
        {
            Util::writeLog('OC_USER_SQL',
                                "User exists, return true",
                                Util::DEBUG);
            $displayName = $row[$this -> settings['col_displayname']];
            return $displayName; ;
        }
        return false;
    }

    public function getDisplayNames($search = '', $limit = null, $offset = null)
    {
        $uids = $this -> getUsers($search, $limit, $offset);
        $displayNames = array();
        foreach($uids as $uid)
        {
            $displayNames[$uid] = $this -> getDisplayName($uid);
        }
        return $displayNames;
    }

    /**
     * Returns the backend name
     * @return string
     */
    public function getBackendName()
    {
        return 'SQL';
    }

    /**
     * The following functions were directly taken from PostfixAdmin and just
     * slightly modified
     * to suit our needs.
     * Encrypt a password,using the apparopriate hashing mechanism as defined in
     * config.inc.php ($this->crypt_type).
     * When wanting to compare one pw to another, it's necessary to provide the
     * salt used - hence
     * the second parameter ($pw_db), which is the existing hash from the DB.
     *
     * @param string $pw        cleartext password
     * @param string $pw_db     encrypted password from database
     * @return string encrypted password.
     */
    private function pacrypt($pw, $pw_db = "")
    {
        Util::writeLog('OC_USER_SQL', "Entering private pacrypt()",
                                        Util::DEBUG);
        $pw = stripslashes($pw);
        $password = "";
        $salt = "";

        if($this -> settings['set_crypt_type'] === 'md5crypt')
        {
            $split_salt = preg_split('/\$/', $pw_db);
            if(isset($split_salt[2]))
            {
                $salt = $split_salt[2];
            }
            $password = $this -> md5crypt($pw, $salt);
        } elseif($this -> settings['set_crypt_type'] === 'md5')
        {
            $password = md5($pw);
        } elseif($this -> settings['set_crypt_type'] === 'system')
        {
            // We never generate salts, as user creation is not allowed here
            $password = crypt($pw, $pw_db);
        } elseif($this -> settings['set_crypt_type'] === 'cleartext')
        {
            $password = $pw;
        }

// See
// https://sourceforge.net/tracker/?func=detail&atid=937966&aid=1793352&group_id=191583
// this is apparently useful for pam_mysql etc.
        elseif($this -> settings['set_crypt_type'] === 'mysql_encrypt')
        {
            if($pw_db !== "")
            {
                $salt = substr($pw_db, 0, 2);

                $row = $this -> helper -> runQuery('mysqlEncryptSalt',
                                    array('pw' => $pw, 'salt' => $salt));
            } else
            {
                $row = $this -> helper -> runQuery('mysqlEncrypt',
                                                    array('pw' => $pw));
            }

            if($row === false)
            {
                return false;
            }
            $password = $row[0];
        } elseif($this -> settings['set_crypt_type'] === 'mysql_password')
        {
            $row = $this -> helper -> runQuery('mysqlPassword',
                                                array('pw' => $pw));

            if($row === false)
            {
                return false;
            }
            $password = $row[0];
        }

        // The following is by Frédéric France
        elseif($this -> settings['set_crypt_type'] === 'joomla')
        {
            $split_salt = preg_split('/:/', $pw_db);
            if(isset($split_salt[1]))
            {
                $salt = $split_salt[1];
            }
            $password = ($salt) ? md5($pw . $salt) : md5($pw);
            $password .= ':' . $salt;
        }

        elseif($this-> settings['set_crypt_type'] === 'ssha256')
        {
            $salted_password = base64_decode(
                                    preg_replace('/{SSHA256}/i','',$pw_db));
            $salt = substr($salted_password,-(strlen($salted_password)-32));
            $password = $this->ssha256($pw,$salt);
        } else
        {
            Util::writeLog('OC_USER_SQL',
                    "unknown/invalid crypt_type settings: ".
                    $this->settings['set_crypt_type'],
                    Util::ERROR);
            die('unknown/invalid Encryption type setting: ' .
                $this -> settings['set_crypt_type']);
        }
        Util::writeLog('OC_USER_SQL', "pacrypt() done, return",
                            Util::DEBUG);
        return $password;
    }

    /**
     * md5crypt
     * Creates MD5 encrypted password
     * @param string $pw    The password to encrypt
     * @param string $salt  The salt to use
     * @param string $magic ?
     * @return string The encrypted password
     */

    private function md5crypt($pw, $salt = "", $magic = "")
    {
        $MAGIC = "$1$";

        if($magic === "")
            $magic = $MAGIC;
        if($salt === "")
            $salt = $this -> create_md5salt();
        $slist = explode("$", $salt);
        if($slist[0] === "1")
            $salt = $slist[1];

        $salt = substr($salt, 0, 8);
        $ctx = $pw . $magic . $salt;
        $final = $this -> pahex2bin(md5($pw . $salt . $pw));

        for($i = strlen($pw); $i > 0; $i -= 16)
        {
            if($i > 16)
            {
                $ctx .= substr($final, 0, 16);
            } else
            {
                $ctx .= substr($final, 0, $i);
            }
        }
        $i = strlen($pw);

        while($i > 0)
        {
            if($i & 1)
                $ctx .= chr(0);
            else
                $ctx .= $pw[0];
            $i = $i>>1;
        }
        $final = $this -> pahex2bin(md5($ctx));

        for($i = 0; $i < 1000; $i++)
        {
            $ctx1 = "";
            if($i & 1)
            {
                $ctx1 .= $pw;
            } else
            {
                $ctx1 .= substr($final, 0, 16);
            }
            if($i % 3)
                $ctx1 .= $salt;
            if($i % 7)
                $ctx1 .= $pw;
            if($i & 1)
            {
                $ctx1 .= substr($final, 0, 16);
            } else
            {
                $ctx1 .= $pw;
            }
            $final = $this -> pahex2bin(md5($ctx1));
        }
        $passwd = "";
        $passwd .= $this -> to64(((ord($final[0])<<16) |
                            (ord($final[6])<<8) | (ord($final[12]))), 4);
        $passwd .= $this -> to64(((ord($final[1])<<16) |
                            (ord($final[7])<<8) | (ord($final[13]))), 4);
        $passwd .= $this -> to64(((ord($final[2])<<16) |
                            (ord($final[8])<<8) | (ord($final[14]))), 4);
        $passwd .= $this -> to64(((ord($final[3])<<16) |
                            (ord($final[9])<<8) | (ord($final[15]))), 4);
        $passwd .= $this -> to64(((ord($final[4])<<16) |
                            (ord($final[10])<<8) | (ord($final[5]))), 4);
        $passwd .= $this -> to64(ord($final[11]), 2);
        return "$magic$salt\$$passwd";
    }

    /**
     * Create a new salte
     * @return string The salt
     */
    private function create_md5salt()
    {
        srand((double) microtime() * 1000000);
        $salt = substr(md5(rand(0, 9999999)), 0, 8);
        return $salt;
    }

    /**
     * Encrypt using SSHA256 algorithm
     * @param string $pw   The password
     * @param string $salt The salt to use
     * @return string The hashed password, prefixed by {SSHA256}
     */
    private function ssha256($pw, $salt)
    {
        return '{SSHA256}'.base64_encode(hash('sha256',$pw.$salt,true).$salt);
    }

    /**
     * PostfixAdmin's hex2bin function
     * @param string $str The string to convert
     * @return string The converted string
     */
    private function pahex2bin($str)
    {
        if(function_exists('hex2bin'))
        {
            return hex2bin($str);
        } else
        {
            $len = strlen($str);
            $nstr = "";
            for($i = 0; $i < $len; $i += 2)
            {
                $num = sscanf(substr($str, $i, 2), "%x");
                $nstr .= chr($num[0]);
            }
            return $nstr;
        }
    }

    /**
     * Convert to 64?
     */
    private function to64($v, $n)
    {
           $ITOA64 =
           "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $ret = "";
        while(($n - 1) >= 0)
        {
            $n--;
            $ret .= $ITOA64[$v & 0x3f];
            $v = $v>>6;
        }
        return $ret;
    }

    /**
     * Store a value in memcache or the session, if no memcache is available
     * @param string $key  The key
     * @param mixed $value The value to store
     * @param int $ttl (optional) defaults to 3600 seconds.
     */
    private function setCache($key, $value, $ttl=3600)
    {
        if ($this -> cache === NULL)
        {
            $_SESSION[$this -> session_cache_name][$key] = array(
                'value' => $value,
                'time' => time(),
                'ttl' => $ttl,
            );
        } else
        {
            $this -> cache -> set($key,$value,$ttl);
        }
    }

    /**
     * Fetch a value from memcache or session, if memcache is not available.
     * Returns NULL if there's no value stored or the value expired.
     * @param string $key
     * @return mixed|NULL
     */
    private function getCache($key)
    {
        $retVal = NULL;
        if ($this -> cache === NULL)
        {
            if (isset($_SESSION[$this -> session_cache_name],
                        $_SESSION[$this -> session_cache_name][$key]))
            {
                $value = $_SESSION[$this -> session_cache_name][$key];
                if (time() < $value['time'] + $value['ttl'])
                {
                    $retVal = $value['value'];
                }
            }
        } else
        {
            $retVal = $this -> cache -> get ($key);
        }
        return $retVal;
    }

    private function create_systemsalt($length=20)
    {
        $fp = fopen('/dev/urandom', 'r');
        $randomString = fread($fp, $length);
        fclose($fp);
        $salt = base64_encode($randomString);
        return $salt;
    }

    private function pw_hash($password)
    {
        $options = [
                    'cost' => 10,
                   ];
        return password_hash($password, PASSWORD_BCRYPT, $options);

    }

    function hash_equals( $a, $b ) {
        $a_length = strlen( $a );

        if ( $a_length !== strlen( $b ) ) {
            return false;
            }
            $result = 0;

        // Do not attempt to "optimize" this.
        for ( $i = 0; $i < $a_length; $i++ ) {
        $result |= ord( $a[ $i ] ) ^ ord( $b[ $i ] );
        }

            //Hide the length of the string
        $additional_length=200-($a_length % 200);
        $tmp=0;
           $c="abCD";
        for ( $i = 0; $i < $additional_length; $i++ ) {
            $tmp |= ord( $c[ 0 ] ) ^ ord( $c[ 0 ] );
        }

        return $result === 0;
    }

    private static function hex_to_base64($hex)
    {
        $hex_chr = '';
        foreach(str_split($hex, 2) as $hexpair)
        {
            $hex_chr .= chr(hexdec($hexpair));
        }
        return base64_encode($hex_chr);
    }

}
