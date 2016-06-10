<?php
/**
 * @file
 * DivisionEditor
 *
 * It contains the definition to:
 * @code
 * class DivisionEditor;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\shop\components;

use  Energine\share\gears\QAL;

/**
 * Shop division editor.
 *
 * @code
 * class DivisionEditor;
 * @endcode
 *
 * @final
 */
class DivisionEditor extends \Energine\share\components\DivisionEditor {

    protected function getFKData($tableName, $keyName) {
        $result = [];
        if (($tableName == 'shop_features') && ($keyName == 'feature_id')) {
            // Для main убираем список значений в селекте, ни к чему он там
            if ($this->getState() !== self::DEFAULT_STATE_NAME) {
                $params = $this->getStateParams(true);
                if (isset($params['site_id'])) {
                    $siteID = $params['site_id'];
                } else {
                    $siteID = E()->getSiteManager()->getCurrentSite()->id;
                }                
//modbysd return title ( name)(site_view (subgroupname)                 $result = $this->dbh->getForeignKeyData($tableName, $keyName, $this->document->getLang(), ['shop_features.feature_is_active' => true, "shop_features.feature_id IN (SELECT feature_id FROM shop_features2sites where site_id=$siteID)"], ['shop_features.group_id' => QAL::ASC, 'shop_features_translation.feature_name' => QAL::ASC]);
                $result=$this->dbh->select('SELECT feature_id,group_id,feature_type,feature_site_multi,feature_smap_multi,feature_is_active,feature_is_filter,feature_is_order_param,feature_is_main,feature_sysname,feature_filter_type,feature_order_num,
                CONCAT(IFNULL(sft.feature_title,"")," (",IFNULL(sft.feature_name,""),")") as feature_name
                FROM shop_features sf LEFT JOIN shop_features_translation sft USING (feature_id) WHERE feature_is_active and lang_id = %s and
                sf.feature_id IN (SELECT feature_id FROM shop_features2sites where site_id='.$siteID.')
		   ORDER BY sf.group_id ASC,feature_name ASC', $this->document->getLang());
                 $result=[$result,"feature_id","feature_name"];

            }
        } else {
            $result = parent::getFKData($tableName, $keyName);
        }

	//return true;
        return $result;
    }

    protected function createDataDescription() {	
        $result = parent::createDataDescription();
        if (in_array($this->getState(), ['add', 'edit'])) {
            if ($fd = $result->getFieldDescriptionByName('smap_features_multi')) {
                $groupsData = E()->Utils->reindex($this->dbh->select('SELECT g.group_id, group_name FROM shop_feature_groups g LEFT JOIN shop_feature_groups_translation USING (group_id) WHERE group_is_active and lang_id = %s', $this->document->getLang()), 'group_id', true);                
                foreach ($fd->getAvailableValues() as &$data) {		     
                    if(isset($groupsData[$data['attributes']['group_id']])) {
                        $data['attributes']['group_name'] = $groupsData[$data['attributes']['group_id']]['group_name'];
                        }
                }
            }

        }
        return $result;
    }


    protected function showUserEditor() {
        $this->request->shiftPath(1);
        $this->userEditor =
            $this->document->componentManager->createComponent('userEditor', 'Energine\user\components\UserEditor', ['config' => 'core/modules/shop/config/UserEditor.component.xml']);
        $this->userEditor->run();
    }

    protected function showRoleEditor() {
        $this->request->shiftPath(1);
        $this->roleEditor =
            $this->document->componentManager->createComponent('roleEditor', 'Energine\shop\components\RoleEditor', ['config' => 'core/modules/user/config/RoleEditor.component.xml']);
        $this->roleEditor->run();
    }


}
