<?php

/**
 * @file GoodsEditor
 *
 * It contains the definition to:
 * @code class GoodsEditor; @endcode
 *
 * @author andy.karpov
 * @copyright Energine 2015
 *
 * @version 1.0.0
 */

namespace Energine\shop\components;

use Energine\share\components\Grid,
    Energine\share\gears\FieldDescription,
    Energine\share\gears\Field,
    Energine\share\gears\ComponentManager,
    Energine\share\gears\Sitemap;
use Energine\share\gears\Data;
use Energine\share\gears\DataDescription;
use Energine\share\gears\QAL;
use Energine\share\gears\SiteManager;
use Energine\share\gears\Translit;
use Energine\share\gears\TreeBuilder;
use Energine\share\gears\TreeNode;
use Energine\share\gears\TreeNodeList;

/**
 * Goods editor.
 *
 * @code
 * class GoodsEditor;
 * @endcode
 */
class GoodsEditor extends Grid implements SampleGoodsEditor {

    /**
     * Division editor.
     * @var DivisionEditor $divisionEditor
     */
    protected $divisionEditor;

    /**
     * Relations editor.
     * @var GoodsRelationEditor $relationEditor
     */
    protected $relationEditor;

    /**
     * Feature editor.
     * @var GoodsFeatureEditor $featureEditor
     */
    protected $featureEditor;

    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $this->setTableName('shop_goods');
        $this->addFilterCondition('smap_id IN (SELECT smap_id FROM share_sitemap WHERE site_id IN (' . implode(',', $this->getSites()) . '))');
        $this->setOrder(['goods_date' => QAL::DESC]);
    }

    private function getSites() {
        $result = [];
        if ($siteID = $this->getParam('site')) {
            $result = [$siteID];
        }
        elseif ($this->document->getRights() < ACCESS_FULL) {
            $result = $this->document->getUser()->getSites();
            if (empty($result)) {
                $result = [0];
            }
        }
        else {
            foreach(E()->getSiteManager() as $site){
                $result[] = $site->id;
            }
        }

        return $result;
    }

    /**
     * Define additional parameter "selector"
     *
     * @return array
     */
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'selector' => false,
                'site' => false
            ]
        );
    }

    /**
     * Added "relations" and "features" data description to the forms
     *
     * @throws \Energine\share\gears\SystemException
     */
    protected function prepare() {

        parent::prepare();

        if (in_array($this->getState(), ['add', 'edit'])) {

            // relations
            $fd = new FieldDescription('relations');
            $fd->setType(FieldDescription::FIELD_TYPE_TAB);
            $fd->setProperty('title', $this->translate('TAB_GOODS_RELATIONS'));
            $this->getDataDescription()->addFieldDescription($fd);

            $field = new Field('relations');
            $state = $this->getState();
            $tab_url = (($state != 'add') ? $this->getData()->getFieldByName($this->getPK())->getRowData(0) : '') . '/relation/';

            $field->setData($tab_url, true);
            $this->getData()->addField($field);

            // features
            $fd = new FieldDescription('features');
            $fd->setType(FieldDescription::FIELD_TYPE_TAB);
            $fd->setProperty('title', $this->translate('TAB_GOODS_FEATURES'));
            $this->getDataDescription()->addFieldDescription($fd);

            $field = new Field('features');
            $state = $this->getState();
            $tab_url = (($state != 'add') ? $this->getData()->getFieldByName($this->getPK())->getRowData(0) : '') . '/feature/show/';

            $field->setData($tab_url, true);
            $this->getData()->addField($field);
        }
    }

    protected function getFKData($fkTableName, $fkKey) {
        $result = false;
        if (($fkKey != 'producer_id')) {
            $result = parent::getFKData($fkTableName, $fkKey);
        } else {
            if ($this->getState() !== self::DEFAULT_STATE_NAME) {
                $result =
                    $this->dbh->getForeignKeyData($fkTableName, $fkKey, $this->document->getLang(), [$fkTableName . '.producer_id' => $this->dbh->getColumn('shop_producers2sites', 'producer_id', ['site_id' => $this->getSites()])]);
            }
        }


        return $result;
    }

    /**
     * Removed key from smap_id field
     *
     * @return array|mixed
     * @throws \Energine\share\gears\SystemException
     */
    protected function loadDataDescription() {
        $result = parent::loadDataDescription();
        if (in_array($this->getState(), ['add', 'edit'])) {
            $result['smap_id']['key'] = false;
        }
        return $result;
    }

    protected function setDataDescription(DataDescription $dd) {
        if ($this->getState() == 'save') {
            if (!$dd->getFieldDescriptionByName('currency_id')) {
                $fd = new FieldDescription('currency_id');
                $fd->setProperty('tableName', $this->getTableName());
                $dd->addFieldDescription($fd);
            }
        }
        parent::setDataDescription($dd);
    }

    protected function createDataDescription() {
        $result = parent::createDataDescription();
        if (in_array($this->getState(), ['add', 'edit'])) {
            if (!$fd = $result->getFieldDescriptionByName('currency_id')) {
                $fd = new FieldDescription('currency_id');
                $fd->setType(FieldDescription::FIELD_TYPE_SELECT);
                $this->setProperty('tableName', $this->getTableName());
                $fd->setMode(FieldDescription::FIELD_MODE_READ);
                $fd->loadAvailableValues($this->dbh->select('SELECT c.currency_id, ct.currency_name, currency_shortname, currency_shortname_order  FROM shop_currencies c LEFT JOIN shop_currencies_translation ct ON (c.currency_id = ct.currency_id) AND (lang_id=%s) WHERE currency_is_active', $this->document->getLang()), 'currency_id', 'currency_name');
                $result->addFieldDescription($fd);

            }
        }

        if(!empty($fd = $result->getFieldDescriptionByName('goods_full_path'))){
            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        }

        return $result;
    }

    /**
     * @return Data
     * @throws \Energine\share\gears\SystemException
     */
    protected function createData() {
        $result = parent::createData();

        if (in_array($this->getState(), ['add', 'edit'])) {
            $currentSmapID = NULL;
            if ($this->getState() == 'edit') {
                $currentSmapID = $result->getFieldByName('smap_id')->getRowData(0);
            }
            $this->getDataDescription()->getFieldDescriptionByName('smap_id')->setType(FieldDescription::FIELD_TYPE_CUSTOM);
            $site_id = E()->getSiteManager()->getSitesByTag('shop', true);

            $site_id = array_intersect($site_id, $this->getSites());

            $root = new TreeNodeList();
            $da = [];

            foreach ($site_id as $siteID) {

                $map = E()->getMap($siteID);
                if (sizeof($site_id) > 1) {
                    $siteRoot = $root->add(new TreeNode($siteID . '-0'));
                    array_push($da, [
                        'id' => $siteID . '-0',
                        'name' => E()->getSiteManager()->getSiteByID($siteID)->name,
                        'isLabel' => true,
                        'selected' => false
                    ]);
                } else {
                    $siteRoot = $root;
                }

                $ids = $map->getPagesByTag('catalogue');
                foreach ($map->getInfo() as $id => $nodeData) {
                    $tmp = [
                        'id' => $id,
                        'name' => $nodeData['Name'],
                        'isLabel' => (in_array($id, $ids)) ? true : false,
                        'selected' => false
                    ];

                    if ($currentSmapID == $id) {
                        $tmp['selected'] = true;
                    }
                    array_push($da, $tmp);

                }

                foreach ($ids as $id) {
                    $siteRoot->add($map->getTree()->getNodeById($id));
                }

            }

            $dd = new DataDescription();
            $dd->load(
                [
                    'id' => [
                        'key' => true,
                        'nullable' => false,
                        'type' => FieldDescription::FIELD_TYPE_INT,
                        'length' => 10,
                        'index' => 'PRI'
                    ],
                    'name' => [
                        'nullable' => false,
                        'type' => FieldDescription::FIELD_TYPE_STRING,
                        'length' => 255,
                        'index' => false
                    ],
                    'isLabel' => [
                        'type' => FieldDescription::FIELD_TYPE_BOOL,
                    ],
                    'selected' => [
                        'type' => FieldDescription::FIELD_TYPE_BOOL,
                    ]

                ]
            );
            $d = new Data();
            $d->load($da);
            //inspect($d);
            $b = new TreeBuilder();
            $b->setTree($root);
            $b->setDataDescription($dd);
            $b->setData($d);
            $b->build();
            $f = $result->getFieldByName('smap_id')->setData($b->getResult(), true);


            if (!$f = $result->getFieldByName('currency_id')) {
                $f = new Field('currency_id');
                $f->setData($this->dbh->getScalar('shop_currencies', 'currency_id', ['currency_is_default' => true]), true);
                $result->addField($f);
            }
        }

        return $result;
    }

    /**
     * Added field for View button
     *
     * @return array|bool|false|mixed
     * @throws \Energine\share\gears\SystemException
     */
    protected function loadData(){
        $result = parent::loadData();

        if(!empty($result) && $this->getDataDescription()->getFieldDescriptionByName('goods_full_path')){
            $result = array_map(function($row) {
                /**
                 * @var $site Site
                 */
                $site = E()->getSiteManager()->getSiteByPage($row['smap_id']);
                /**
                 * @var $map Sitemap
                 */
                $map = E()->getMap($site->id);
                $row['goods_full_path'] = $site->base.$map->getURLByID($row['smap_id']).'view/'.$this->dbh->getScalar($this->getTableName(), 'goods_segment', ['goods_id' => $row['goods_id']]).'/';

                return $row;
            }, $result);

        }

        return $result;
    }

    /**
     * Create component for editing relations to the goods.
     */
    protected function relationEditor() {
        $sp = $this->getStateParams(true);
        $params = ['config' => 'core/modules/shop/config/GoodsRelationEditor.component.xml'];

        if (isset($sp['goods_id'])) {
            $this->request->shiftPath(2);
            $params['goodsID'] = $sp['goods_id'];

        } else {
            $this->request->shiftPath(1);
        }
        $this->relationEditor = $this->document->componentManager->createComponent('relationEditor', 'Energine\shop\components\GoodsRelationEditor', $params);
        $this->relationEditor->run();
    }

    /**
     * Create component for editing features of the goods.
     */
    protected function featureEditor() {
        $sp = $this->getStateParams(true);
        $params = ['config' => 'core/modules/shop/config/GoodsFeatureEditor.component.xml'];

        if (isset($sp['goods_id'])) {
            $this->request->shiftPath(2);
            $params['goodsID'] = $sp['goods_id'];

        } else {
            $this->request->shiftPath(1);
        }
        $this->featureEditor = $this->document->componentManager->createComponent('featureEditor', 'Energine\shop\components\GoodsFeatureEditor', $params);
        $this->featureEditor->run();
    }

    /**
     * @copydoc GoodsEditor::build
     */
    public function build() {
        if ($this->getState() == 'showSmapSelector') {
            $result = $this->divisionEditor->build();
        } elseif ($this->getState() == 'relationEditor') {
            $result = $this->relationEditor->build();
        } elseif ($this->getState() == 'featureEditor') {
            $result = $this->featureEditor->build();
        } else {
            $result = parent::build();
        }
        return $result;
    }

    /**
     * @copydoc Grid::saveData
     */
    protected function saveData() {
        if (empty($_POST[$this->getTableName()]['goods_segment'])) {
            $_POST[$this->getTableName()]['goods_segment'] = Translit::asURLSegment($_POST[$this->getTranslationTableName()][E()->getLanguage()->getDefault()]['goods_name']);
        }

        if (empty($_POST[$this->getTableName()]['currency_id'])) {
            $_POST[$this->getTableName()]['currency_id'] = $this->dbh->getScalar('shop_currencies', 'currency_id', ['currency_is_default' => true]);
        }
        $goodsID = parent::saveData();
        $this->saveRelations($goodsID);
        $this->saveFeatureValues($goodsID);
        return $goodsID;
    }

    /**
     * Link relations to the current goods_id (after save)
     *
     * @param int $goodsID
     * @throws \Energine\share\gears\SystemException
     */
    protected function saveRelations($goodsID) {
        $this->dbh->modify(
            'UPDATE shop_goods_relations
			SET session_id = NULL, goods_from_id=%s
			WHERE (goods_from_id IS NULL AND session_id=%s) or (goods_from_id = %1$s)',
            $goodsID, session_id()
        );
    }

    /**
     * Link feature values to the current goods_id (after save)
     * Also remove all incorrect values, not related to the selected goods category
     *
     * @param int $goodsID
     * @throws \Energine\share\gears\SystemException
     */
    protected function saveFeatureValues($goodsID) {
        $this->dbh->modify(
            'UPDATE shop_feature2good_values
			SET session_id = NULL, goods_id=%s
			WHERE (goods_id IS NULL AND session_id=%s) or (goods_id = %1$s)',
            $goodsID, session_id()
        );
        $smapID = $this->dbh->getScalar('shop_goods', 'smap_id', ['goods_id' => $goodsID]);
        // remove all incorrect feature values
        $this->dbh->modify(
            'DELETE FROM shop_feature2good_values
			WHERE goods_id=%s and feature_id NOT IN (
				SELECT feature_id from shop_sitemap2features where smap_id=%s)',
            $goodsID, $smapID
        );
    }
}

interface SampleGoodsEditor {

}