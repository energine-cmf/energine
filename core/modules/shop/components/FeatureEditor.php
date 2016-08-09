<?php
/**
 * @file
 * FeatureEditor
 *
 * It contains the definition to:
 * @code
class FeatureEditor;
 * @endcode
 *
 * @author andy.karpov
 * @copyright Energine 2015
 *
 * @version 1.0.0
 */
namespace Energine\shop\components;

use Energine\share\components\Grid, Energine\share\gears\FieldDescription, Energine\share\gears\Field;
use Energine\share\gears\QAL;
use Energine\share\gears\JSONCustomBuilder;

/**
 * Feature editor.
 *
 * @code
 * class FeatureEditor;
 * @endcode
 */
class FeatureEditor extends Grid implements SampleFeatureEditor{

    /**
     * Options editor.
     * @var FeatureOptionEditor $oEditor
     */
    protected $oEditor;

    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $this->setTableName('shop_features');
        $this->setOrder(['group_id' => QAL::ASC, 'feature_name' => QAL::ASC]);
        //$this->setOrder(['group_id' => QAL::DESC,'feature_id'=> QAL::DESC ,'feature_name' => QAL::ASC]);
        }



    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'site' => false
            ]
        );
    }

    private function getSites() {
        $result = [];
        if ($siteID = $this->getParam('site')) {
            $result = [$siteID];
        } elseif ($this->document->getRights() < ACCESS_FULL) {
            $result = $this->document->getUser()->getSites();
            if (empty($result)) {
                $result = [0];
            }
        } else {
            foreach (E()->getSiteManager() as $site) {
                $result[] = $site->id;
            }
        }

        return $result;
    }

    protected function createDataDescription() {    
        $r = parent::createDataDescription();
        if (in_array($this->getState(), ['add', 'edit'])) {
            $r->getFieldDescriptionByName('feature_smap_multi')->setProperty('tabName', $this->translate('TXT_CATEGORIES'));
            if (($this->document->getRights() < ACCESS_FULL) && ($fd = $r->getFieldDescriptionByName('feature_site_multi'))) {
                $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
            }
	    if ($f = $r->getFieldDescriptionByName('feature_name')) {
                $f->setProperty('pattern', '/^.+$/m');
		$f->setProperty('message', $this->translate('MSG_BAD_CHECK_FEATURE_NAME'));
            }
	    if ($f = $r->getFieldDescriptionByName('feature_title')) {
                $f->setProperty('pattern', '/^.+$/m');
		$f->setProperty('message', $this->translate('MSG_BAD_CHECK_FEATURE_TITLE'));
            }
	    if ($f = $r->getFieldDescriptionByName('group_id')) {
                $f->setProperty('pattern', '/^.+$/m');
		$f->setProperty('message',  $this->translate('MSG_BAD_CHECK_GROUP_ID'));
            }
// 	    if ($f = $r->getFieldDescriptionByName('feature_site_multi')) {
//                 $f->setProperty('pattern', '/^.+$/m');
// 		$f->setProperty('message',  $this->translate('MSG_BAD_CHECK_SITE_MULTI'));
//             }
        }
        return $r;
    }

    /**
     * Отбираем только те сайты которые являются магазинами
     *
     * @param string $fkTableName
     * @param string $fkKeyName
     * @return array
     */
    protected function getFKData($fkTableName, $fkKeyName) {
        $filter = $result = [];
        if ($fkKeyName == 'site_id') {
            //оставляем только те сайты где есть магазины
            if ($sites = E()->getSiteManager()->getSitesByTag('shop')) {
                $filter['share_sites.site_id'] = array_map(function ($site) {
                    return (string)$site;
                }, $sites);
            }
        }
        if ($fkKeyName == 'smap_id') {
            //оставляем только те сайты где есть магазины
            if ($sites = E()->getSiteManager()->getSitesByTag('shop')) {
                $filter['share_sitemap.site_id'] = array_map(function ($site) {
                    return (string)$site;
                }, $sites);
            }
        }

        if ($this->getState() !== self::DEFAULT_STATE_NAME) {
            $result = $this->dbh->getForeignKeyData($fkTableName, $fkKeyName, $this->document->getLang(), $filter);
        }

        if (isset($result[0]) && ($fkKeyName == 'smap_id')) {
            $pages = $rootPages = [];
            foreach ($filter['share_sitemap.site_id'] as $siteID) {
                $map = E()->getMap($siteID);
                foreach ($map->getPagesByTag('catalogue') as $pageID) {
                    $pages[] = $pageID;
                    $pages = array_merge($pages, array_keys($map->getTree()->getNodeById($pageID)->asList()));
                    $rootPages[] = $pageID;
                }
            }

            $result[0] = array_filter($result[0], function ($row) use ($pages) {
                return in_array($row['smap_id'], $pages);
            });
            $result[0] = array_map(function ($row) use ($rootPages) {
                if (in_array($row['smap_id'], $rootPages)) $row['root'] = E()->getSiteManager()->getSiteByID($row['site_id'])->name;
                return $row;
            }, $result[0]);
        }
        return $result;
    }

    protected function prepare() {

        parent::prepare();

        if (in_array($this->getState(), ['add', 'edit'])) {

            $fd = new FieldDescription('options');
            $fd->setType(FieldDescription::FIELD_TYPE_TAB);
            $fd->setProperty('title', $this->translate('TAB_FEATURE_OPTIONS'));
            $this->getDataDescription()->addFieldDescription($fd);

            $field = new Field('options');
            $state = $this->getState();
            $tab_url = (($state != 'add') ? $this->getData()->getFieldByName($this->getPK())->getRowData(0) : '') . '/option/';

            $field->setData($tab_url, true);
            $this->getData()->addField($field);
        }
    }

    /**
     * Create component for editing options to the feature (type = OPTION / VARIANT).
     */
    protected function optionEditor() {
        $sp = $this->getStateParams(true);
        $params = ['config' => 'core/modules/shop/config/FeatureOptionEditor.component.xml'];

        if (isset($sp['feature_id'])) {
            $this->request->shiftPath(2);
            $params['featureID'] = $sp['feature_id'];

        } else {
            $this->request->shiftPath(1);
        }
        $this->oEditor = $this->document->componentManager->createComponent('oEditor', 'Energine\shop\components\FeatureOptionEditor', $params);
        $this->oEditor->run();
    }

    /**
     * @copydoc Grid::build
     */
    public function build() {
        if ($this->getState() == 'optionEditor') {
            $result = $this->oEditor->build();
        } else {
            $result = parent::build();
        }

        return $result;
    }

    /**
     * @copydoc Grid::saveData
     */
    // Привязывем все option с feature_id = NULL к текущей характеристике.
    protected function saveData() {
        //Для всех с не админскими правами принудительно выставляем в те сайты на которые у юзера есть права

        if (($this->document->getRights() < ACCESS_FULL)) {
            $_POST[$this->getTableName()]['feature_site_multi'] = $this->document->getUser()->getSites();
        } else {
	  if(!isset($_POST[$this->getTableName()]['feature_site_multi'])) $_POST[$this->getTableName()]['feature_site_multi'] = $this->document->getUser()->getSites();	      
	}
        $featureID = parent::saveData();
        $this->dbh->modify(
            'UPDATE shop_feature_options
			SET session_id = NULL, feature_id=%s
			WHERE (feature_id IS NULL and session_id = %s) or (feature_id = %1$s)',
            $featureID, session_id()
        );
	//modbysd change feature_order_num according to feature_group number
        $this->dbh->modify(
            'UPDATE `shop_features`,`shop_feature_groups`
			SET `feature_order_num` =`shop_feature_groups`.`group_order_num`
			WHERE (`shop_features`.`feature_id`=%s and `shop_feature_groups`.`group_id`=`shop_features`.`group_id`)',
            $featureID,$featureID
        );
        return $featureID;
    }

    protected function getRawData() {
            //отбираем те фичи права на которые есть у текущего пользователя
            $this->addFilterCondition([$this->getTableName() . '.feature_id' => $this->dbh->getColumn('shop_features2sites', 'feature_id', ['site_id' => $this->getSites()])]);
            $this->setOrder(['feature_order_num' => QAL::ASC,'feature_id'=> QAL::DESC ,'feature_name' => QAL::DESC]);



        parent::getRawData();
    }
    /**
     * Copy.  modBySD SINGLE
     * @return mixed
     * @see GoodsEditor::copy()
     */
    protected function copy() {      
	list($id) = $this->getStateParams();   
//feature
      $sql     = sprintf( '
      INSERT INTO `shop_features` (
	`group_id`,`feature_type`,`feature_site_multi`,`feature_smap_multi`,`feature_is_active`,`feature_is_filter`,`feature_is_order_param`,`feature_is_main`,`feature_sysname`,`feature_filter_type`,`feature_order_num`
      ) 
	SELECT  
	    f.`group_id`,f.`feature_type`,f.`feature_site_multi`,f.`feature_smap_multi`,f.`feature_is_active`,f.`feature_is_filter`,f.`feature_is_order_param`,f.`feature_is_main`,f.`feature_sysname`,f.`feature_filter_type`,f.feature_order_num
	FROM 
	    (select 
	      `group_id`,`feature_type`,`feature_site_multi`,`feature_smap_multi`,`feature_is_active`,`feature_is_filter`,`feature_is_order_param`,`feature_is_main`,`feature_sysname`,`feature_filter_type`,feature_order_num
	    FROM `shop_features`
	      WHERE `feature_id` = %s) as f
      ;', $id);
      $new_id = $this->dbh->modify( $sql );      
//feature sites
      $this->dbh->modify('
      INSERT INTO `shop_features2sites` (
      `feature_id`,`site_id`
      )	
      SELECT %s,`site_id`
	FROM `shop_features2sites`
	WHERE `feature_id` = %s LIMIT 1
      ', $new_id,$id);
//feature translation
      $this->dbh->modify('
      INSERT INTO `shop_features_translation` (
      `feature_id`,`lang_id`,`feature_name`,`feature_title`,`feature_description`,`feature_unit`
      )	
      SELECT %s,`lang_id`,`feature_name`,`feature_title`,`feature_description`,`feature_unit`
	FROM `shop_features_translation`
	WHERE `feature_id` = %s
      ', $new_id,$id);
//feature options
      $this->dbh->modify('
      INSERT INTO `shop_feature_options` (
	`feature_id`,`option_order_num`,`session_id`,`option_img`
      )	
      SELECT %s,`option_order_num`,`session_id`,`option_img`
	FROM `shop_feature_options`
	WHERE `feature_id` = %s ORDER BY `option_id` ASC
      ', $new_id,$id);
// shop_feature_options_translation
      $this->dbh->modify('
SET @o_c=0;
SET @c_c=0;
INSERT INTO 
`shop_feature_options_translation`
( `OPTION_ID`,`LANG_ID`,`OPTION_VALUE` )	
SELECT OID.COID,SFOT.`LANG_ID`,SFOT.`OPTION_VALUE` 
FROM 
      (SELECT O.`OPTION_ID` AS OOID,C.`OPTION_ID` AS COID
     	FROM
     (SELECT `OPTION_ID`,@o_c:=@o_c+1 AS cntr  FROM `shop_feature_options` WHERE `FEATURE_ID` = %s ORDER BY `OPTION_ID` ASC ) AS O,
     (SELECT `OPTION_ID`,@c_c:=@c_c+1 AS cntr FROM `shop_feature_options` WHERE `FEATURE_ID` = %s ORDER BY `OPTION_ID` ASC ) AS C
     WHERE O.cntr=C.cntr
    ) AS OID,
	`shop_feature_options_translation` AS SFOT
	WHERE SFOT.`OPTION_ID`=OID.OOID;
      ', $id,$new_id);
//shop_sitemap2features (cat's to show)
      $this->dbh->modify('
      INSERT INTO `shop_sitemap2features` (
	`smap_id`,`feature_id`
      )	
      SELECT `smap_id`,%s
	FROM `shop_sitemap2features`
	WHERE `feature_id` = %s 
      ', $new_id,$id);

         if ($this->logClass) {
                /**
                 * @var ActionLog $logger
                 */
                $logger = new $this->logClass(get_class($this), $this->getName());
                $logger->write('COPY FEATURE', $id);
         }
            $b = new JSONCustomBuilder();
            $this->setBuilder($b);
    }
}

interface SampleFeatureEditor {

}