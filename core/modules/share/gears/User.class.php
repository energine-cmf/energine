<?php

/**
 * Содержит класс User
 *
 * @package energine
 * @subpackage kernel
 * @author dr.Pavka
 * @copyright Energine 2006
 */

/**
 * Класс для работы с пользователем
 * Возвращает информацию о пользователе, сохраняет данные
 *
 * @package energine
 * @subpackage kernel
 * @author dr.Pavka
 */
class User extends DBWorker {
    /**
     * Имя таблицы пользователей
     *
     */
    const USER_TABLE_NAME = 'user_users';

    /**
     * Имя таблицы групп
     *
     */
    const GROUP_TABLE_NAME = 'user_user_groups';

    /**
     * Идентификатор пользователя
     *
     * @var int
     * @access private
     */
    private $id = false;
    /**
     * Объект по работе с пользователями
     *
     * @var UserGroup
     * @access protected
     */
    protected $userGroup;

    /**
     * Информация о пользователе
     *
     * @var array
     * @access private
     */
    private $info = array();

    /**
     * Конструктор класса
     *
     * @param int идентификатор пользователя
     * @return void
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
     * Загрузка информации из БД
     *
     * @param int идентификатор пользователя
     * @return void
     * @access protected
     */

    protected function loadInfo($UID) {
        $result = $this->dbh->select(self::USER_TABLE_NAME, true, array('u_id' => $UID));
        if (is_array($result) && !empty($result)) {
            $this->id = $UID;
            $result[0]['u_password'] = true;
            $this->info = $result[0];
        }
    }

    /**
     * Возвращает перечень групп в которые входит пользователь
     *
     * @return mixed
     * @access public
     */

    public function getGroups() {
        $result = array();
        $result = $this->userGroup->getUserGroups($this->id);
        return $result;
    }

    /**
     * Возвращает идентфикатор пользователя
     *
     * @return mixed
     * @access public
     */

    public function getID() {
        return $this->id;
    }

    /**
     * Возвращает значение поля
     *
     * @return mixed
     * @access public
     */

    public function getValue($fieldName) {
        $result = false;
        if (isset($this->info[$fieldName])) {
            $result = $this->info[$fieldName];
        }
        return $result;
    }

    /**
     * Возвращает перечень полей
     *
     * @return array
     * @access public
     */

    public function getFields() {
        $result = $this->dbh->getColumnsInfo(self::USER_TABLE_NAME);
        return $result;
    }

    /**
     * Создание нового пользователя
     *
     * @param array
     * @return void
     * @access public
     */

    public function create($data) {
        //проверяем имеются ли все необходимые значения
        $tableInfo = $this->dbh->getColumnsInfo(self::USER_TABLE_NAME);
        $necessaryFields = $uniqueFields = array();
        foreach ($tableInfo as $columnName => $columnInfo) {
            //отбираем все поля !nullable, не PRI, и без дефолтного значения
            if (!$columnInfo['nullable'] && $columnInfo['index'] != DBA::PRIMARY_INDEX && !$columnInfo['default']) {
                array_push($necessaryFields, $columnName);
            }
            //Отбираем все уникальные поля
            if ($columnInfo['index'] == DBA::UNIQUE_INDEX) {
                array_push($uniqueFields, $columnName);
            }
        }
        //если пересечение списка необходимых полей и списка полей данных не пустое  - значит недостаточно данных для сохранения

        if ($undefinedFields = array_diff($necessaryFields, array_keys($data))) {
            throw new SystemException('ERR_INSUFFICIENT_DATA', SystemException::ERR_WARNING, $undefinedFields);
        }
        //проверяем являются ли введенные поля уникальными
        if (!empty($uniqueFields)) {
            $condition = array();
            foreach ($uniqueFields as $fieldname) {
                $condition[] = $fieldname . ' = "' . $data[$fieldname] . '"';
            }
            $condition = implode(' OR ', $condition);
            if (simplifyDBResult($this->dbh->select(self::USER_TABLE_NAME, 'COUNT(u_id) as num', $condition), 'num', true) > 0) {
                throw new SystemException('ERR_NOT_UNIQUE_DATA', SystemException::ERR_WARNING);
            }
        }

        $this->info = $data;
        //$this->info['u_password'] = $data['u_password'];
        $data['u_password'] = sha1($data['u_password']);
        $this->id = $this->dbh->modify(QAL::INSERT, self::USER_TABLE_NAME, $data);
        $this->setGroups(array($this->userGroup->getDefaultUserGroup()));
    }

    /**
     * Обновление данных о пользователе
     *
     * @param array
     * @return boolean
     * @access public
     */

    public function update($data) {
        $result = false;
        if ($this->getID()) {
            $result = $this->dbh->modify(QAL::UPDATE, self::USER_TABLE_NAME, $data, array('u_id' => $this->getID()));
        }
        return $result;
    }

    /**
     * Устанавливает перечень групп в которые будет входить пользователь
     *
     * @param array
     * @return void
     * @access public
     */

    public function setGroups($groups) {
        //Устанавливать группы можно только тогда, когда пользователь создан
        if ($this->getID()) {
            //$this->dbh->beginTransaction();
            try {
                $this->dbh->modify(QAL::DELETE, self::GROUP_TABLE_NAME, null, array('u_id' => $this->getID()));
                foreach ($groups as $groupID) {
                    $this->dbh->modify(QAL::INSERT, self::GROUP_TABLE_NAME, array('u_id' => $this->getID(), 'group_id' => $groupID));
                }
                //$this->dbh->commit();
            }
            catch (SystemException $e) {
                //$this->dbh->rollback();
                //передаем исключение дальше
                throw new SystemException($e->getMessage(), $e->getCode(), $e->getCustomMessage());
            }
        }
    }

    /**
     * Поиск юзера по идентфикатору ФБ
     * @static
     * @param $fbID
     * @return bool|User
     */
    public static function getFBUser($fbID) {
        $result = false;
        if ($UID = simplifyDBResult(E()->getDB()->select(self::USER_TABLE_NAME, 'u_id', array('u_fbid' => $fbID, 'u_is_active' => 1)), 'u_id', true)) {
            return new User($UID);
        }
        return $result;
    }

    /**
     * @param string $email
     * @param int $fbID
     * @return bool|User
     */
    public static function linkFBUserByEmail($email, $fbID) {
        $result = false;
        if ($UID = simplifyDBResult(E()->getDB()->select(self::USER_TABLE_NAME, 'u_id', array('u_name' => $email, 'u_is_active' => 1)), 'u_id', true)) {
            $result = new User($UID);
            $result->update(array('u_fbid' => $fbID));
        }
        return $result;
    }

    /**
     * Поиск юзера по идентфикатору Вконтакте
     * @static
     * @param $vkID
     * @return bool|User
     */
    public static function getVKUser($vkID) {
        $result = false;
        if ($UID = simplifyDBResult(E()->getDB()->select(self::USER_TABLE_NAME, 'u_id', array('u_vkid' => $vkID, 'u_is_active' => 1)), 'u_id', true)) {
            return new User($UID);
        }
        return $result;
    }

    /**
     * Поиск юзера по идентфикатору Одноклассников
     * @static
     * @param $okID
     * @return bool|User
     */
    public static function getOKUser($okID) {
        $result = false;
        if ($UID = simplifyDBResult(E()->getDB()->select(self::USER_TABLE_NAME, 'u_id', array('u_okid' => $okID, 'u_is_active' => 1)), 'u_id', true)) {
            return new User($UID);
        }
        return $result;
    }

    /**
     * Генерирует пароль заданной длины из случайных буквенно-цифровых символов.
     *
     * @param int $length
     * @return string
     * @access public
     * @static
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
}
