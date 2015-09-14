<?php
/**
 * @file
 * User.
 *
 * It contains the definition to:
 * @code
class User;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * User manager.
 *
 * @code
class User;
 * @endcode
 *
 * It holds an information about user, saves data, etc.
 */
class User extends Primitive {
    use DBWorker;
    /**
     * Table name of users.
     * @var string USER_TABLE_NAME
     */
    const USER_TABLE_NAME = 'user_users';

    /**
     * Table name of groups.
     * @var string GROUP_TABLE_NAME
     */
    const GROUP_TABLE_NAME = 'user_user_groups';

    //todo VZ: Why not to begin the user id from 0?
    /**
     * User ID.
     * @var int $id
     */
    private $id = false;

    /**
     * Primitive for work with users.
     * @var UserGroup $userGroup
     */
    protected $userGroup;

    /**
     * Information about the user.
     * @var array $info
     */
    private $info = [];

    //todo VZ: Why not to use 0 as the default user id?
    /**
     * @param bool|int $id User ID.
     */
    public function __construct($id = false) {
        parent::__construct();
        $this->id = $id;
        $this->userGroup = E()->UserGroup;
        //Если пользователь существует  - загружаем его информацию
        if ($this->id) {
            $this->loadInfo($this->id);
        }
    }

    /**
     * Load information about the user from data base.
     *
     * @param int $UID User ID.
     */
    protected function loadInfo($UID) {
        $result = $this->dbh->select(self::USER_TABLE_NAME, true, ['u_id' => $UID]);
        if ($result) {
            $this->id = $UID;
            $result[0]['u_password'] = true;
            $this->info = $result[0];
        }
    }

    /**
     * Get the list of groups to which belongs the user.
     *
     * @return array
     */
    public function getGroups() {
        return $this->userGroup->getUserGroups($this->id);
    }

    /**
     * Get user ID.
     *
     * @return int
     */
    public function getID() {
        return $this->id;
    }

    /**
     * @param bool $asArray
     * @return array
     */
    public function getSites($asArray = true) {
        $result = [];
        $r = array_filter(array_map(function ($groupID) use ($asArray) {
            return $this->userGroup->getSites($groupID, $asArray);
        }, array_filter($this->getGroups(), function ($groupID) {
            return $groupID != $this->userGroup->getDefaultGuestGroup();
        })));
        array_walk($r, function ($values) use (&$result) {
            $result = array_merge($result, $values);
        });
        return $result;
    }

    /**
     * Get the field value.
     *
     * @param string $fieldName Field name.
     * @return mixed
     */
    public function getValue($fieldName) {
        $result = false;
        if (isset($this->info[$fieldName])) {
            $result = $this->info[$fieldName];
        }
        return $result;
    }

    /**
     * Get the list of fields.
     *
     * @return array
     */
    public function getFields() {
        $result = $this->dbh->getColumnsInfo(self::USER_TABLE_NAME);
        return $result;
    }

    /**
     * Create new user.
     *
     * @param array $data Data.
     *
     * @throws SystemException 'ERR_INSUFFICIENT_DATA'
     * @throws SystemException 'ERR_NOT_UNIQUE_DATA'
     */
    public function create($data) {
        //проверяем имеются ли все необходимые значения
        $tableInfo = $this->dbh->getColumnsInfo(self::USER_TABLE_NAME);
        $necessaryFields = $uniqueFields = [];
        foreach ($tableInfo as $columnName => $columnInfo) {
            //отбираем все поля !nullable, не PRI, и без дефолтного значения
            if (!$columnInfo['nullable'] && $columnInfo['index'] != QAL::PRIMARY_INDEX && !$columnInfo['default']) {
                array_push($necessaryFields, $columnName);
            }
            //Отбираем все уникальные поля
            if ($columnInfo['index'] == QAL::UNIQUE_INDEX) {
                array_push($uniqueFields, $columnName);
            }
        }
        //если пересечение списка необходимых полей и списка полей данных не пустое  - значит недостаточно данных для сохранения

        if ($undefinedFields = array_diff($necessaryFields, array_keys($data))) {
            throw new SystemException('ERR_INSUFFICIENT_DATA', SystemException::ERR_WARNING, $undefinedFields);
        }
        //проверяем являются ли введенные поля уникальными
        if (!empty($uniqueFields)) {
            $condition = [];
            foreach ($uniqueFields as $fieldname) {
                $condition[] = $fieldname . ' = "' . $data[$fieldname] . '"';
            }
            $condition = implode(' OR ', $condition);
            if ($this->dbh->getScalar(self::USER_TABLE_NAME, 'COUNT(u_id) as num', $condition) > 0) {
                throw new SystemException('ERR_NOT_UNIQUE_DATA', SystemException::ERR_WARNING);
            }
        }

        $this->info = $data;
        //$this->info['u_password'] = $data['u_password'];
        $data['u_password'] = password_hash($data['u_password'], PASSWORD_DEFAULT);
        $this->id = $this->dbh->modify(QAL::INSERT, self::USER_TABLE_NAME, $data);
        $this->setGroups([$this->userGroup->getDefaultUserGroup()]);
    }

    /**
     * Update user data.
     *
     * @param array $data New user data.
     * @return boolean
     */
    public function update($data) {
        $result = false;
        if ($this->getID()) {
            if($this->getID() != $this->dbh->getScalar('user_users', 'u_id', ['u_name' => $data['u_name']])){
                throw new SystemException('ERR_DUPLICATE_LOGIN');
            }
            $result = $this->dbh->modify(QAL::UPDATE, self::USER_TABLE_NAME, $this->info = $data, ['u_id' => $this->getID()]);
        }
        return $result;
    }

    /**
     * @param $groupID int Group id
     * @return bool
     */
    public function isInGroup($groupID) {
        return in_array($groupID, $this->getGroups());
    }

    /**
     * Set user groups.
     *
     * @throws SystemException
     *
     * @param array|integer $groups Groups.
     */
    public function setGroups($groups) {
        //Устанавливать группы можно только тогда, когда пользователь создан
        if ($this->getID()) {
            if (!is_array($groups)) {
                $groups = [$groups];
            }
            //$this->dbh->beginTransaction();
            try {
                $this->dbh->modify(QAL::DELETE, self::GROUP_TABLE_NAME, NULL, ['u_id' => $this->getID()]);
                foreach ($groups as $groupID) {
                    $this->dbh->modify(QAL::INSERT, self::GROUP_TABLE_NAME, ['u_id' => $this->getID(), 'group_id' => $groupID]);
                }
                //$this->dbh->commit();
            } catch (SystemException $e) {
                //$this->dbh->rollback();
                //передаем исключение дальше
                throw new SystemException($e->getMessage(), $e->getCode(), $e->getCustomMessage());
            }
        }
    }

    /**
     * Get user by his ID in <a href="http://www.facebook.com">Facebook</a>.
     *
     * @param string $fbID Facebook user ID.
     * @return bool|User
     */
    public static function getFBUser($fbID) {
        $result = false;
        if ($UID = E()->getDB()->getScalar(self::USER_TABLE_NAME, 'u_id', ['u_fbid' => $fbID, 'u_is_active' => 1])) {
            return new User($UID);
        }
        return $result;
    }

    /**
     * Link Facebook user by his E-Mail.
     *
     * @param string $email User E-Mail.
     * @param string $fbID Facebook user ID.
     * @return bool|User
     */
    public static function linkFBUserByEmail($email, $fbID) {
        $result = false;
        if ($UID = E()->getDB()->getScalar(self::USER_TABLE_NAME, 'u_id', ['u_name' => $email, 'u_is_active' => 1])) {
            $result = new User($UID);
            $result->update(['u_fbid' => $fbID]);
        }
        return $result;
    }

    /**
     * Get user by his ID in <a href="http://www.vk.com">VKontakte</a>.
     *
     * @param string $vkID VKontakte user ID.
     * @return bool|User
     */
    public static function getVKUser($vkID) {
        $result = false;
        if ($UID = E()->getDB()->getScalar(self::USER_TABLE_NAME, 'u_id', ['u_vkid' => $vkID, 'u_is_active' => 1])) {
            return new User($UID);
        }
        return $result;
    }

    /**
     * Get user by his ID in <a href="http://www.odnoklassniki.ru">Одноклассники</a>.
     *
     * @param string $okID User ID in Одноклассники.
     * @return bool|User
     */
    public static function getOKUser($okID) {
        $result = false;
        if ($UID = E()->getDB()->getScalar(self::USER_TABLE_NAME, 'u_id', ['u_okid' => $okID, 'u_is_active' => 1])) {
            return new User($UID);
        }
        return $result;
    }

    /**
     * Generate random password with specific length from numbers and latin characters.
     *
     * @param int $length Password length.
     * @return string
     */
    public static function generatePassword($length = 8) {
        $chars = '0123456789abcdefghjiklmnopqrstuvwxyzABCDEFGHJIKLMNOPQRSTUVWXYZ';
        $password = '';
        $max = strlen($chars) - 1; // $max = count($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[mt_rand(0, $max)];
        }
        return $password;
    }

    public function asArray(){
        return $this->info;
    }
}
