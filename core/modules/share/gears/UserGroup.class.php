<?php
/**
 * @file
 * UserGroup.
 *
 * It contains the definition to:
 * @code
final class UserGroup;
@endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * User groups.
 *
 * @final
 */
final class UserGroup extends DBWorker {
    /**
     * Default group for guests.
     * @var int $defaultGuestGroup
     */
    private $defaultGuestGroup = false;

    /**
     * Default group for authenticated users.
     * @var int $defaultUserGroup
     */
    private $defaultUserGroup = false;

    /**
     * Information about all user groups.
     * @var array $groups
     */
    private $groups;

    public function __construct() {
        parent::__construct();
        /*
         * Загружаем инфомацию о группах пользователей в структуру вида:
         *     array(
         *         $group_id => array(group info)
         *     );
         */
        $this->groups = convertDBResult($this->dbh->select('user_groups'), 'group_id', true);
    }

    /**
     * Get all groups as array.
     *
     * @return array
     */
    public function asArray() {
        return $this->groups;
    }

    /**
     * Get default group ID for guests.
     *
     * @return int
     *
     * @throws SystemException 'ERR_DEV_NO_DEFAULT_GROUP'
     */
    public function getDefaultGuestGroup() {
        if (!$this->defaultGuestGroup) {
            $result = false;
            foreach ($this->groups as $groupId => $groupInfo) {
                if ($groupInfo['group_default'] == 1) {
                    $result = $groupId;
                    break;
                }
            }
            if ($result == false) {
            	throw new SystemException('ERR_DEV_NO_DEFAULT_GROUP', SystemException::ERR_CRITICAL);
            }
            $this->defaultGuestGroup = $result;
        }
        return (int)$this->defaultGuestGroup;
    }

    /**
     * Get default group ID for authenticated users.
     *
     * @return int
     *
     * @throws SystemException 'ERR_DEV_NO_DEFAULT_USER_GROUP'
     */
    public function getDefaultUserGroup() {
        if (!$this->defaultUserGroup) {
            $result = false;
            foreach ($this->groups as $groupId => $groupInfo) {
                if ($groupInfo['group_user_default'] == 1) {
                    $result = $groupId;
                    break;
                }
            }
            if ($result == false) {
            	throw new SystemException('ERR_DEV_NO_DEFAULT_USER_GROUP', SystemException::ERR_CRITICAL);
            }
            $this->defaultUserGroup = $result;
        }
        return $this->defaultUserGroup;
    }

    //todo VZ: Why not to use 0 as the default user id?
    /**
     * Get the list of groups to which belongs specific user.
     *
     * @param int|bool $userId User ID.
     * @return array
     */
    public function getUserGroups($userId = false) {
        static $cachedGroups = array();
        if (!isset($cachedGroups[$userId])) {
            $cachedGroups[$userId] = array($this->getDefaultGuestGroup());
            if (!empty($userId)) {
                $res = $this->dbh->select('user_user_groups', array('group_id'), array('u_id' => $userId));
                if (is_array($res)) {
                    $cachedGroups[$userId] = simplifyDBResult($res, 'group_id');
                }
            }
        }

        return $cachedGroups[$userId];
    }

    /**
     * Get information about specific group.
     *
     * @param int $groupId Group ID.
     * @return array
     */
    public function getInfo($groupId) {
        $result = array();
        if (isset($this->groups[$groupId])) {
            $result = $this->groups[$groupId];
        }
        return $result;
    }

    /**
     * Get the list of specific group members.
     *
     * @param int $groupID Group ID.
     * @return array
     */
    public function getMembers($groupID){
        $result = array();
    	$members = simplifyDBResult($this->dbh->select('user_user_groups', array('u_id'), array('group_id'=>$groupID)), 'u_id');
    	if (is_array($members)) {
    		foreach ($members as $memberID) {
    		    $member = new User($memberID);
    		    if ($member->getValue('u_is_active') == 1) {
    		      $result[] = $member;
    		    }

    		}
    	}
    	return $result;
    }
}
