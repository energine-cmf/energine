<?php
/**
 * Содержит класс LoginForm
 *
 * @package energine
 * @subpackage user
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/DataSet.class.php');


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
    public function __construct($name, $module, Document $document,  array $params = null) {
        $params['action'] = $document->user->isAuthenticated()?'showLogoutForm':'showLoginForm';
        parent::__construct($name, $module, $document,  $params);
        if ($this->document->user->isAuthenticated() && $this->document->user->isNowAuthenticated()) {
            $this->response->redirectToCurrentSection($this->getParam('successAction'));
        }
		$this->setTitle($this->translate('TXT_LOGIN_FORM'));
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
        'successAction' => ''
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
        if (isset($_POST['user']['login'])) {
            $messageField = new FieldDescription('message');
            $messageField->setType(FieldDescription::FIELD_TYPE_STRING);
        	$this->getDataDescription()->addFieldDescription($messageField);
        	$messageField->setRights(FieldDescription::FIELD_MODE_READ);

        	$messageField = new Field('message');
        	$messageField->addRowData($this->translate('ERR_BAD_LOGIN'));
        	$this->setData(new Data());
        	$this->getData()->addField($messageField);
        }
    }


    /**
	  * Вывод формы logout
	  *
	  * @return type
	  * @access public
	  */

    public function showLogoutForm() {
        $request = Request::getInstance();
        //$this->setTitle($this->translate('TXT_LOGOUT'));
        $this->addTranslation('TXT_USER_GREETING');
        $this->addTranslation('TXT_USER_NAME');
        $this->addTranslation('TXT_ROLE_TEXT');
        $this->setDataSetAction($request->getBasePath(), true);
        $this->prepare();
        foreach (UserGroup::getInstance()->getUserGroups($this->document->user->getID()) as $roleID) {
            $tmp = UserGroup::getInstance()->getInfo($roleID);
            $data[] = $tmp['group_name'];
        }

        $this->getData()->getFieldByName('role_name')->setData(implode(',', $data));
    }

    protected function loadData() {
        $result = false;
        switch ($this->getAction()) {
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
