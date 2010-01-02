<?php

/**
 * Содержит класс UserEditor
 *
 * @package energine
 * @subpackage user
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * Класс - редактор пользователей
 *
 * @package energine
 * @subpackage user
 * @author dr.Pavka
 */
class UserEditor extends Grid {


    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setTableName('user_users');
        $this->setTitle($this->translate('TXT_USER_EDITOR'));
    }

    /**
	 * Переопределенный родительский метод
	 * проверяет а не пытаемся ли мы удалить текущего пользователя
	 *
	 * @return boolean
	 * @access public
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
	 * Сохранение данных о ролях пользователя
	 *
	 * @return boolean
	 * @access protected
	 */

    protected function saveData() {
        //При сохранении данных из формы редактирования
        //Если не пришел пароль - не трогаем его

        if ($this->getPreviousAction() == 'edit' && $_POST[$this->getTableName()]['u_password'] === '') {
            unset($_POST[$this->getTableName()]['u_password']);
        }
        else {
        	$_POST[$this->getTableName()]['u_password'] = sha1($_POST[$this->getTableName()]['u_password']);
        }

        $result = parent::saveData();

        $UID = (is_int($result))?$result:current($this->getFilter());
        
        //если задана аватарка
        if(
            isset($_POST[$this->getTableName()]['u_avatar_prfile'])
            &&
            file_exists($_POST[$this->getTableName()]['u_avatar_prfile'])
        ){
        	//и ее размеры отличаются от необходимых
        	list($realWidth, $realHeight) = getimagesize($_POST[$this->getTableName()]['u_avatar_prfile']);
        	list($neededWidth, $neededHeight) = $this->getParam('thumbnail');
        	 
        	if(
        	   ($realWidth != $neededWidth) 
        	   || 
        	   ($realHeight != $neededHeight)
        	){
        		$fileName = $_POST[$this->getTableName()]['u_avatar_prfile'];
                $image = new Image();
                $image->loadFromFile($fileName);
                $image->resize($neededWidth,$neededHeight);
                $image->saveToFile($fileName);      	
        	}
        }
        
        $this->dbh->modify(QAL::DELETE, 'user_user_groups', null, array('u_id'=>$UID));
        
        if(isset($_POST['group_id']) && is_array($_POST['group_id']))
        foreach ($_POST['group_id'] as $groupID) {
            $this->dbh->modify(QAL::INSERT, 'user_user_groups',array('u_id'=>$UID, 'group_id'=>$groupID));
        }

        return $result;
    }
    /**
     * toggles user activity status
     * 
     * @return void
     * @access protected
     */
    protected function activate(){
    	$transactionStarted = $this->dbh->beginTransaction();
        try {
            list($id) = $this->getActionParams();
            if (!$this->recordExists($id)) {
                throw new SystemException('ERR_404', SystemException::ERR_404);
            }
            
	        if ($this->document->user->getID() == $id) {
	           throw new SystemException('ERR_CANT_ACTIVATE_YOURSELF', SystemException::ERR_CRITICAL);
	        }
	        
            $this->dbh->modifyRequest('UPDATE '.$this->getTableName().' SET u_is_active = NOT u_is_active WHERE u_id = %s', $id);

            $JSONResponse = array(
            'result'=>true
            );
            $this->dbh->commit();
        }
        catch (SystemException $e){
            if ($transactionStarted) {
                $this->dbh->rollback();
            }
            $JSONResponse = $this->generateError($e->getCode(), $e->getMessage());

        }
        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write(json_encode($JSONResponse));
        $this->response->commit();        
    }

    /**
     * Для метода редактирования убирается пароль
     *
     * @return array
     * @access protected
     */

    protected function loadData() {
        $result = parent::loadData();

        /*if ($this->getAction() == 'save') {
            $result[0]['u_password'] = sha1($result[0]['u_password']);
        }
        else*/if ($this->getAction() == 'getRawData' && $result) {
            $result = array_map(array($this, 'printUserGroups'), $result);
        }
        elseif ($this->getAction() == 'edit') {
            $result[0]['u_password'] = '';
        }
        elseif ($this->getAction() == 'view') {
            $result[0]['u_password'] = '';
        }
        return $result;
    }

    /**
     * Callback метод вызывающийся при загрузке данных
     * Добавляет к массиву строку с перечнем групп в которіе входит пользователь
     *
     * @return array
     * @access private
     */

    private function printUserGroups($row) {
        $userGroup = UserGroup::getInstance();
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
     * Для методов add и edit добавляется поле роли
     *
     * @return DataDescription
     * @access protected
     */

    protected function createDataDescription() {
        $result = parent::createDataDescription();

        if (in_array($this->getAction(), array('add', 'edit'))) {
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
     * Load
     *
     * @return array
     * @access protected
     */

     protected function loadDataDescription() {
        $result = parent::loadDataDescription();
        if ($this->getAction() == 'save' && isset($result['u_password'])) {
           	$result['u_password']['nullable'] = true;
        }

        return $result;
     }

    /**
      * Для методов add и edit добавляется инфо о роли
      *
      * @return Data
      * @access protected
      */

    protected function createData() {
        $result = parent::createData();
        $id = $this->getFilter();
        $id = (!empty($id) && is_array($id))?current($id):false;
        if ($this->getType() != self::COMPONENT_TYPE_LIST && $id) {
            //создаем переменную содержащую идентификторы групп в которые входит пользователь
            $f = new Field('group_id');
            $result->addField($f);

            $data = $this->dbh->select('user_user_groups', array('group_id'), array('u_id'=>$id));
            if(is_array($data)) {
                $f->addRowData(array_keys(convertDBResult($data, 'group_id', true)));
            }
            else {
                $f->setData(array());
            }
        }
        return $result;
    }

    /**
     * Добавляем таб
     *
     * @return DOMNode
     * @access public
     */

    public function build() {
        if ($this->getType() != self::COMPONENT_TYPE_LIST) {
            $this->addTab($this->buildTab($this->translate('TXT_USER_GROUPS')));
        }

        $result = parent::build();
        return $result;
    }
}
