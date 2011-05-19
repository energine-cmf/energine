<?php
/**
 * Содержит класс Form
 *
 * @package energine
 * @subpackage forms
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Форма
 *
 * @package energine
 * @subpackage forms
 * @author d.pavka@gmail.com
 */
class Form extends DBDataSet
{
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null){
        parent::__construct($name, $module, $params);
        if(!($formID = $this->getParam('id'))){
            $formID = simplifyDBResult(
                $this->dbh->selectRequest('SELECT form_id FROM frm_forms WHERE form_is_active = 1 ORDER BY RAND() LIMIT 1'),
                'form_id',
                true
            );
        }

        if(!$formID || !$this->dbh->tableExists($tableName = $this->getConfigValue('forms.database').'.'.FormConstructor::TABLE_PREFIX.$formID)){
            throw new SystemException('ERR_NO_FORM', SystemException::ERR_404, $this->getParam('id'));
        }
        $this->setTableName($tableName);
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->setAction('send');
        $this->addTranslation('TXT_ENTER_CAPTCHA');
    }
    protected function defineParams(){
        return array_merge(
            parent::defineParams(),
            array(
                'id' => false,
                'active' => true
            )
        );
    }
    
    protected function prepare() {
        parent::prepare();
        if (
            $this->document->getUser()->isAuthenticated()
            &&
            ($captcha =
                    $this->getDataDescription()->getFieldDescriptionByName('captcha'))
        ) {
            $this->getDataDescription()->removeFieldDescription($captcha);
        }
    }
}