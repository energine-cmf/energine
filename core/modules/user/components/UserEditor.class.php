<?php
/**
 * @file
 * UserEditor
 *
 * It contains the definition to:
 * @code
class UserEditor;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */

namespace Energine\user\components;
use Energine\share\components\Grid, Energine\share\gears\SystemException, Energine\share\gears\QAL, Energine\share\gears\JSONCustomBuilder, Energine\share\gears\FieldDescription, Energine\share\gears\Field;
/**
 * User editor.
 *
 * @code
class UserEditor;
 * @endcode
 */
class UserEditor extends Grid {
    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('user_users');
        $this->setTitle($this->translate('TXT_USER_EDITOR'));
    }

    /**
     * @copydoc Grid::deleteData
     *
     * @throws SystemException 'ERR_CANT_DELETE_YOURSELF'
     */
    public function deleteData($id) {
        //если мы пытаемся удалить текущего пользователя
        //генерим ошибку
        if ($this->document->user->getID() == $id) {
            throw new SystemException('ERR_CANT_DELETE_YOURSELF', SystemException::ERR_CRITICAL);
        }
        parent::deleteData($id);
    }


    /**
     * @copydoc Grid::saveData
     */
    protected function saveData() {
        //При сохранении данных из формы редактирования
        //Если не пришел пароль - не трогаем его

        if ($this->getPreviousState() == 'edit' && $_POST[$this->getTableName()]['u_password'] === '') {
            unset($_POST[$this->getTableName()]['u_password']);
        } else {
            $_POST[$this->getTableName()]['u_password'] = sha1($_POST[$this->getTableName()]['u_password']);
        }
        if($this->getPreviousState() == 'add' && $this->dbh->getScalar('SELECT COUNT(*) FROM ' . $this->getTableName() . ' WHERE u_name=%s', $_POST[$this->getTableName()]['u_name'])){
            throw new SystemException('ERR_USER_EXISTS', SystemException::ERR_CRITICAL);
        }
        $result = parent::saveData();

        $UID = (is_int($result)) ? $result : current($this->getFilter());

        $this->dbh->modify(QAL::DELETE, 'user_user_groups', null, array('u_id' => $UID));

        if (isset($_POST['group_id']) && is_array($_POST['group_id']))
            foreach ($_POST['group_id'] as $groupID) {
                $this->dbh->modify(QAL::INSERT, 'user_user_groups', array('u_id' => $UID, 'group_id' => $groupID));
            }

        return $result;
    }

    /**
     * Toggle user activity status
     */
    protected function activate() {
        $transactionStarted = $this->dbh->beginTransaction();
        $b = new JSONCustomBuilder();
        $this->setBuilder($b);
        try {
            list($id) = $this->getStateParams();
            if (!$this->recordExists($id)) {
                throw new SystemException('ERR_404', SystemException::ERR_404);
            }

            if ($this->document->user->getID() == $id) {
                throw new SystemException('ERR_CANT_ACTIVATE_YOURSELF', SystemException::ERR_CRITICAL);
            }

            $this->dbh->modifyRequest('UPDATE ' . $this->getTableName() . ' SET u_is_active = NOT u_is_active WHERE u_id = %s', $id);


            $b->setProperties(array(
                'result' => true,
            ));


            $this->dbh->commit();
        } catch (SystemException $e) {
            if ($transactionStarted) {
                $this->dbh->rollback();
            }
            $b->setProperties(array(
                'result' => false,
                'error' => $e->getMessage()
            ));
        }
    }


    /**
     * @copydoc Grid::loadData
     */
    // Для метода редактирования убирается пароль
    protected function loadData() {
        $result = parent::loadData();

        /*if ($this->getState() == 'save') {
            $result[0]['u_password'] = sha1($result[0]['u_password']);
        }
        else*/
        if ($this->getState() == 'getRawData' && $result) {
            $result = array_map(array($this, 'printUserGroups'), $result);
        } elseif ($this->getState() == 'edit') {
            $result[0]['u_password'] = '';
        } elseif ($this->getState() == 'view') {
            $result[0]['u_password'] = '';
        }
        return $result;
    }

    /**
     * Callback method that called by data loading.
     * It adds a string to the array with all user's groups.
     *
     * @param array $row Row.
     * @return array
     */
    private function printUserGroups($row) {
        $userGroup = E()->UserGroup;
        $userGroupIDs = $userGroup->getUserGroups($row['u_id']);
        $userGroupName = array();
        foreach ($userGroupIDs as $UGID) {
            $groupInfo = $userGroup->getInfo($UGID);
            $userGroupName[] = $groupInfo['group_name'];
        }
        $row['u_group'] = implode(', ', $userGroupName);
        return $row;
    }

    /**
     * @copydoc Grid::createDataDescription
     */
    // Для методов add и edit добавляется поле роли
    protected function createDataDescription() {
        $result = parent::createDataDescription();

        if (in_array($this->getState(), array('add', 'edit'))) {
            foreach ($result as $fieldDescription) {
                $fieldDescription->setProperty('tabName', $this->translate('TXT_USER_EDITOR'));
            }
            $result->getFieldDescriptionByName('u_name')->setType(FieldDescription::FIELD_TYPE_EMAIL);
            if ($fd = $result->getFieldDescriptionByName('u_is_active')) {
                $result->removeFieldDescription($fd);
            }
            $fd = new FieldDescription('group_id');
            $fd->setSystemType(FieldDescription::FIELD_TYPE_INT);
            $fd->setType(FieldDescription::FIELD_TYPE_MULTI);
            $fd->setProperty('tabName', $this->translate('TXT_USER_GROUPS'));
            $fd->setProperty('customField', true);

            $data = $this->dbh->select('user_groups', array('group_id', 'group_name'), 'group_id IN(select group_id from user_groups where group_default=0)');

            $fd->loadAvailableValues($data, 'group_id', 'group_name');
            $result->addFieldDescription($fd);
        }

        if (
            ($this->getType() == self::COMPONENT_TYPE_FORM_ALTER)
            &&
            ($f = $result->getFieldDescriptionByName('u_password'))
        ) {
            $f->removeProperty('pattern');
            $f->removeProperty('message');
            $f->setProperty('nullable', true);
        }

        return $result;
    }

    /**
     * @copydoc Grid::loadDataDescription
     */
    protected function loadDataDescription() {
        $result = parent::loadDataDescription();
        if ($this->getState() == 'save' && isset($result['u_password'])) {
            $result['u_password']['nullable'] = true;
        }

        return $result;
    }

    /**
     * @copydoc Grid::createData
     */
    // Для методов add и edit добавляется инфо о роли
    protected function createData() {
        $result = parent::createData();
        $id = $this->getFilter();
        $id = (!empty($id) && is_array($id)) ? current($id) : false;
        if ($this->getType() != self::COMPONENT_TYPE_LIST && $id) {
            //создаем переменную содержащую идентификторы групп в которые входит пользователь
            $f = new Field('group_id');
            $result->addField($f);

            $data = $this->dbh->select('user_user_groups', array('group_id'), array('u_id' => $id));
            if (is_array($data)) {
                $f->addRowData(array_keys(convertDBResult($data, 'group_id', true)));
            } else {
                $f->setData(array());
            }
        }
        return $result;
    }
}
