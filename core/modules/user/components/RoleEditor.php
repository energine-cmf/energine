<?php
/**
 * @file
 * RoleEditor
 *
 * It contains the definition to:
 * @code
class RoleEditor;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */

namespace Energine\user\components;
use Energine\share\components\Grid, Energine\share\gears\FieldDescription, Energine\share\gears\QAL, Energine\share\gears\TreeBuilder, Energine\share\gears\TreeConverter, Energine\share\gears\SystemException, Energine\share\gears\Data, Energine\share\gears\DataDescription;
/**
 * Role editor.
 *
 * @code
class RoleEditor;
@endcode
 */
class RoleEditor extends Grid {
    /**
     * Unique fields.
     * This fields can exist only for one user.
     * @var array $uniqueFields
     */
    private $uniqueFields = array('group_default', 'group_user_default');

    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->setTableName('user_groups');
        $this->setTitle($this->translate('TXT_ROLE_EDITOR'));
    }

    /**
     * @copydoc Grid::build
     */
    // Для формы редактирования, если чекбоксы ролей по умолчанию отмечены делает их неактивными
    public function build() {
        if ($this->getType() == self::COMPONENT_TYPE_FORM_ALTER ) {
            foreach ($this->uniqueFields as $fieldName) {
                if ($this->getData()->getFieldByName($fieldName)->getRowData(0) === true) {
                    $this->getDataDescription()->getFieldDescriptionByName($fieldName)->setMode(FieldDescription::FIELD_MODE_READ);
                }
            }
        }

        return parent::build();
    }

    /**
      * @copydoc Grid::loadData
      */
    protected function loadData() {
        $result = parent::loadData();
        if ($this->getState() == 'save') {
            foreach ($this->uniqueFields as $fieldName) {
                if (isset($result[0][$fieldName]) && $result[0][$fieldName]) {
                    $this->dbh->modify(QAL::UPDATE, $this->getTableName(), array($fieldName=>null));
                }
            }
        }
        return $result;
    }

    /**
     * @copydoc Grid::createDataDescription
     */
    //  Добавляется fake поле user_div_rights в котором находятся данные
    protected function createDataDescription() {
        $result = parent::createDataDescription();
        if ($this->getType() != self::COMPONENT_TYPE_LIST) {
            foreach ($result as $fieldDescription) {
                $fieldDescription->setProperty('tabName', $this->translate('TXT_ROLE_EDITOR'));
            }
            $fd = new FieldDescription('group_div_rights');
            $fd->setType(FieldDescription::FIELD_TYPE_CUSTOM);
            $fd->setProperty('tabName', $this->translate('TXT_ROLE_DIV_RIGHTS'));
            $fd->setProperty('customField', true);
            $result->addFieldDescription($fd);
        }
        return $result;
    }

    /**
     * Build tab with division rights.
     *
     * @return DOMNode
     */
    private function buildDivRightsData() {
        $builder  = new TreeBuilder();
        $builder->setTree(
            TreeConverter::convert(
                $this->dbh->select(
                    'share_sitemap', 
                    array('smap_id', 'smap_pid'), 
                    null, 
                    array('smap_order_num'=>QAL::ASC)), 'smap_id', 'smap_pid'));

        $id = $this->getFilter();
        $id = (!empty($id))?current($id):'';

        $data = convertDBResult(
            $this->dbh->selectRequest(
                'select s.smap_id as Id, smap_pid as Pid, site_id as Site, smap_name as Name '.
                'from share_sitemap s '.
                'left join share_sitemap_translation st on st.smap_id = s.smap_id '.
                'where lang_id='.E()->getLanguage()->getCurrent()), 'Id');

        foreach ($data as $smapID => $smapInfo) {
            $data[$smapID]['RightsId'] = E()->getMap($smapInfo['Site'])->getDocumentRights($smapID, $id);
            $data[$smapID]['Site'] = E()->getSiteManager()->getSiteByID($smapInfo['Site'])->name;
        }

        $dataObject = new Data();
        $dataObject->load($data);
        $builder->setData($dataObject);

        $dataDescriptionObject = new DataDescription();

        $f = new FieldDescription('Id');
        $f->setType(FieldDescription::FIELD_TYPE_INT);
        $f->setProperty('key', true);
        $dataDescriptionObject->addFieldDescription($f);

        $f = new FieldDescription('Pid');
        $f->setType(FieldDescription::FIELD_TYPE_INT);
        $dataDescriptionObject->addFieldDescription($f);

        $f = new FieldDescription('Name');
        $f->setType(FieldDescription::FIELD_TYPE_STRING);
        $dataDescriptionObject->addFieldDescription($f);

        $f = new FieldDescription('Site');
        $f->setType(FieldDescription::FIELD_TYPE_STRING);
        $dataDescriptionObject->addFieldDescription($f);
        
        $f = new FieldDescription('RightsId');
        $f->setType(FieldDescription::FIELD_TYPE_SELECT);
        if ($this->getState() == 'view') {
            $f->setMode(FieldDescription::FIELD_MODE_READ);
        }
        $rights = $this->dbh->select('user_group_rights', array('right_id', 'right_const'));
        $rights = array_merge(array(array('right_id'=>0, 'right_const'=>'NO_RIGHTS')), $rights);
        foreach ($rights as $key => $value) {
            $rights[$key]['right_const'] = $this->translate('TXT_'.$value['right_const']);
        }
        $f->loadAvailableValues($rights, 'right_id', 'right_const');
        $dataDescriptionObject->addFieldDescription($f);


        $builder->setData($dataObject);
        $builder->setDataDescription($dataDescriptionObject);
        $builder->build();

        return $builder->getResult();
    }

    /**
      * @copydoc Grid::createData
      */
    // Для методов add и edit добавляется инфо о роли
    protected function createData() {
        $result = parent::createData();
        if ($this->getType() != self::COMPONENT_TYPE_LIST) {
            $f = new Field('group_div_rights');
            $f->setData($this->buildDivRightsData());
            $result->addField($f);
        }

        return $result;
    }

    /**
     * @copydoc Grid::saveData
     */
    // Сохранение данных о уровне прав на разделы
    protected function saveData() {
        $result = parent::saveData();

        $roleID = (is_int($result))?$result:current($this->getFilter());

        $this->dbh->modify(QAL::DELETE, 'share_access_level', null, array('group_id'=>$roleID));

        if(isset($_POST['div_right']) && is_array($_POST['div_right']))
        foreach ($_POST['div_right'] as $smapID=>$rightID) {
            if(!empty($rightID))
            $this->dbh->modify(QAL::INSERT, 'share_access_level',array('group_id'=>$roleID, 'smap_id'=>$smapID, 'right_id'=>$rightID));
        }

        return $result;
    }

    /**
     * @copydoc Grid::deleteData
     */
    // При удалении происходит проверка не удаляется ли дефолтная группа
    protected function deleteData($id) {
        if ($this->dbh->select($this->getTableName(), 'group_id', array('group_id'=>$id, 'group_default'=>true)) !== true) {
            throw new SystemException('ERR_DEFAULT_GROUP', SystemException::ERR_NOTICE);
        }
        parent::deleteData($id);
    }
}
