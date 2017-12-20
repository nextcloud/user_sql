<?php

namespace OCA\user_sql;

use \OCA\user_sql\lib\Helper;

class OC_GROUP_SQL extends \OC_Group_Backend implements \OCP\GroupInterface
{
    protected $settings;
    protected $helper;

    public function __construct()
    {
        $this -> helper = new \OCA\user_sql\lib\Helper();
        $domain = \OC::$server->getRequest()->getServerHost();
        $this -> settings = $this -> helper -> loadSettingsForDomain($domain);
        $this -> helper -> connectToDb($this -> settings);        
        return false;
    }

    public function getUserGroups($uid) {
        if(empty($this -> settings['sql_group_table']))
        {
            \OCP\Util::writeLog('OC_USER_SQL', "Group table not configured", \OCP\Util::DEBUG);
            return [];
        }
        $rows = $this -> helper -> runQuery('getUserGroups', array('uid' => $uid), false, true);
        if($rows === false)
        {
            \OCP\Util::writeLog('OC_USER_SQL', "Found no group", \OCP\Util::DEBUG);
            return [];
        }
        $groups = array();
        foreach($rows as $row)
        {
            $groups[] = $row[$this -> settings['col_group_name']];
        } 
        return $groups;
    }

    public function getGroups($search = '', $limit = null, $offset = null) {
        if(empty($this -> settings['sql_group_table']))
        {
            return [];
        }
        $search = "%".$search."%";
        $rows = $this -> helper -> runQuery('getGroups', array('search' => $search), false, true, array('limit' => $limit, 'offset' => $offset));
        if($rows === false)
        {
            return [];
        }   
        $groups = array();
        foreach($rows as $row)
        {
            $groups[] = $row[$this -> settings['col_group_name']];
        }   
        return $groups;
    }

    public function usersInGroup($gid, $search = '', $limit = null, $offset = null) {
        if(empty($this -> settings['sql_group_table']))
        {
            \OCP\Util::writeLog('OC_USER_SQL', "Group table not configured", \OCP\Util::DEBUG);
            return [];
        }
        $rows = $this -> helper -> runQuery('getGroupUsers', array('gid' => $gid), false, true);
        if($rows === false)
        {
            \OCP\Util::writeLog('OC_USER_SQL', "Found no users for group", \OCP\Util::DEBUG);
            return [];
        }
        $users = array();
        foreach($rows as $row)
        {
            $users[] = $row[$this -> settings['col_group_username']];
        } 
        return $users;
    }

    public function countUsersInGroup($gid, $search = '') {
        if(empty($this -> settings['sql_group_table']))
        {
            return 0;
        }
        $search = "%".$search."%";
        $count = $this -> helper -> runQuery('countUsersInGroup', array('gid' => $gid, 'search' => $search));
        if($count === false)
        {
            return 0;
        } else {
            return intval(reset($count));
        }
    }
}
?>
