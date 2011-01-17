<?php
/**
 * Содержит класс LoginForm
 *
 * @package energine
 * @subpackage user
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */


/**
 * Вывод формы авторизации
 *
 * @package energine
 * @subpackage user
 * @author dr.Pavka
 */
class LoginForm extends DataSet {
    /**
	 * Конструктор
	 *
	 * @param string $name
	 * @param string $module
	 */
    public function __construct($name, $module,   array $params = null) {
        $params['state'] = E()->getDocument()->user->isAuthenticated()?'showLogoutForm':'showLoginForm';
        parent::__construct($name, $module,  $params);
 		$this->setTitle($this->translate('TXT_LOGIN_FORM'));
        $this->setDataSetAction(SiteManager::getInstance()->getCurrentSite()->base.'auth.php', true);
    }

    /**
     * Добавлены:
     * Параметр successAction - УРЛ на который происходит переадресация в случае успеха
     *
     * @return array
     * @access protected
     */
    protected function defineParams() {
        return array_merge(
        parent::defineParams(),
        array(
        'successAction' => false
        )
        );
    }

    /**
     * Вывод формы авторизации
     *
     * @return type
     * @access public
     */

    public function showLoginForm() {
        $this->prepare();
        if (isset($_COOKIE[UserSession::FAILED_LOGIN_COOKIE_NAME])) {
            $messageField = new FieldDescription('message');
            $messageField->setType(FieldDescription::FIELD_TYPE_STRING);
        	$this->getDataDescription()->addFieldDescription($messageField);
        	$messageField->setRights(FieldDescription::FIELD_MODE_READ);

        	$messageField = new Field('message');
        	$messageField->addRowData($this->translate('ERR_BAD_LOGIN'));
        	$this->getData()->addField($messageField);
            E()->getResponse()->deleteCookie(UserSession::FAILED_LOGIN_COOKIE_NAME);
        }
    }


    /**
	  * Вывод формы logout
	  *
	  * @return type
	  * @access public
	  */

    public function showLogoutForm() {
        //$request = E()->getRequest();
        //$this->setTitle($this->translate('TXT_LOGOUT'));
        $this->addTranslation('TXT_USER_GREETING','TXT_USER_NAME','TXT_ROLE_TEXT');
        //$this->setDataSetAction(SiteManager::getInstance()->getCurrentSite()->base, true);
        $this->prepare();
        /*foreach (E()->UserGroup->getUserGroups($this->document->user->getID()) as $roleID) {
            $tmp = E()->UserGroup->getInfo($roleID);
            $data[] = $tmp['group_name'];
        }

        $this->getData()->getFieldByName('role_name')->setData(implode(', ', $data));*/
    }

    protected function loadData() {
        $result = false;
        switch ($this->getState()) {
            case 'showLogoutForm':
                foreach ($this->getDataDescription()->getFieldDescriptionList() as $fieldName) {
                    
                    $result[] = array($fieldName=>$this->document->user->getValue($fieldName));
                }
                break;
            default:
                $result = parent::loadData();
        }
        return $result;
    }
}
