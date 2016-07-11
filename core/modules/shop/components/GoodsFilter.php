<?php

namespace Energine\shop\components;

use Energine\share\components\DataSet;
use Energine\share\gears\Button;
use Energine\share\gears\Data;
use Energine\share\gears\DataDescription;
use Energine\share\gears\Field;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\JSONBuilder;
use Energine\share\gears\JSONCustomBuilder;
use Energine\share\gears\QAL;
use Energine\share\gears\Request;
use Energine\share\gears\SimplestBuilder;
use Energine\share\gears\Toolbar;
use Energine\shop\gears\EmptySimpleFormBuilder;
use Energine\shop\gears\FeatureFieldAbstract;
use Energine\shop\gears\FeatureFieldFactory;


class GoodsFilter extends DataSet
{
    const FILTER_GET = 'filter';
    public $whereFilter;
    protected $filter_data = [];
    /**
     * @var GoodsList
     */
    protected $boundComponent;

    protected $categoryIds = [];

    public function __construct($name, array $params = NULL)
    {
        parent::__construct($name, $params);

        $this->setTitle($this->translate('TXT_FILTER'));
    }

    protected function showParams()
    {
        $this->setBuilder(new SimplestBuilder());
        $this->setTitle($this->translate('TXT_FILTER_LIB'));
        $dd = new DataDescription();
        $dd->load([
                      'sf_id' => [
                          'type' => FieldDescription::FIELD_TYPE_INT,
                          'key' => true,
                          'index' => 'PRI'
                      ],
                      'sf_name' => [
                          'type' => FieldDescription::FIELD_TYPE_STRING
                      ],
                      'sf_url' => [
                          'type' => FieldDescription::FIELD_TYPE_STRING
                      ]
                  ]);

        $this->setDataDescription($dd);
        $d = new Data();
        $d->load(array_map(function($row){
            $row['sf_url'] = E()->getMap()->getURLByID($row['smap_id']).'?'.http_build_query([self::FILTER_GET => $row['sf_data']]);
            unset($row['sf_data']);
            return $row;
        }, $this->dbh->select(
            'shop_saved_filters', ['sf_id', 'sf_name', 'smap_id','sf_data'], ['u_id' => E()->getUser()->getID(), 'site_id' => E()->getSiteManager()->getCurrentSite()->id]
        )));
        $this->setData($d);
        E()->getController()->getTransformer()->setFileName('single_products.xslt');
    }

    protected function saveFilterForm()
    {
        $this->setBuilder($b = new JSONCustomBuilder());
        try {
            if(!E()->getUser()->isAuthenticated()){
                throw new \InvalidArgumentException(E()->Utils->translate('ERR_BAD_USER'));
            }
            if(!isset($_POST['name']) || !isset($_GET[self::FILTER_GET]) || empty($_POST['name']) || empty($_GET[self::FILTER_GET])){
                throw new \InvalidArgumentException(E()->Utils->translate('ERR_NO_FILTER_NAME'));
            }
            $name= $_POST['name'];
            if($this->dbh->getScalar('shop_saved_filters', 'COUNT(*)', ['sf_name' => $name, 'u_id' => E()->getUser()->getID(), 'site_id' => E()->getSiteManager()->getCurrentSite()->id])){
                throw new \InvalidArgumentException(E()->Utils->translate('ERR_DUPLICATE_FILTER_NAME'));
            }
            if($this->dbh->getScalar('shop_saved_filters', 'COUNT(*)', ['sf_data' => $_GET[self::FILTER_GET], 'u_id' => E()->getUser()->getID(), 'smap_id' => $this->document->getID(), 'site_id' => E()->getSiteManager()->getCurrentSite()->id])){
                throw new \InvalidArgumentException(E()->Utils->translate('ERR_DUPLICATE_FILTER_DATA'));
            }
            $this->dbh->modify(QAL::INSERT, 'shop_saved_filters', ['sf_name' => $name, 'sf_data' => $_GET[self::FILTER_GET], 'smap_id' => $this->document->getID(), 'u_id' => E()->getUser()->getID(), 'site_id' => E()->getSiteManager()->getCurrentSite()->id]);
        }
        catch(\Exception $e){
            $b->setProperty('result', false);
            $b->setProperty('message', $e->getMessage());
        }
    }

    protected function showSaveFilterForm()
    {
        $this->setAction((string)$this->config->getStateConfig('saveFilterForm')->uri_patterns->pattern, true);
        $this->setTitle($this->translate('TXT_TITLE_SAVE_FILTER_FORM'));
        $dd = new DataDescription();
        $dd->load([
                      'message' => [
                          'type' => FieldDescription::FIELD_TYPE_HTML_BLOCK,
                          'mode' => FieldDescription::FIELD_MODE_READ
                      ],
                      'sf_name' => [
                          'type' => FieldDescription::FIELD_TYPE_STRING
                      ]
                  ]);

        $this->setDataDescription($dd);
        $this->setBuilder(new EmptySimpleFormBuilder());
        $tb = new Toolbar('main');
        $tb->attachControl($b = new Button('save'));
        $b->setTitle(E()->Utils->translate('BTN_SAVE_FILTER'));

        $this->addToolbar($tb);
        $this->setData($d = new Data());
        $f = new Field('message');
        $f->setData(E()->Utils->translate('TXT_SAVE_FILTER_FORM'));
        $d->addField($f);
        E()->getController()->getTransformer()->setFileName('single_products.xslt');
    }

    protected function createBuilder()
    {
        return new EmptySimpleFormBuilder();
    }

    protected function defineParams()
    {
        return array_merge(
            parent::defineParams(),
            [
                'bind' => false,
                'tableName' => 'shop_goods',
                'active' => true,
                'showForProduct' => false,
                'removeEmptyPriceFilter' => true,
            ]
        );
    }

    protected function buildPriceFilter()
    {
        if ($fd = $this->getDataDescription()->getFieldDescriptionByName('price')) {
            $fd->setType(FieldDescription::FIELD_TYPE_CUSTOM);
            $fd->setProperty('title', $this->translate('FILTER_PRICE'));
            $fd->setProperty('subtype', FeatureFieldAbstract::FEATURE_FILTER_TYPE_RANGE);
            $min = ceil($this->dbh->getScalar(
                'select min(goods_price) from ' .
                $this->getParam('tableName') .
                ' where smap_id IN( %s) AND goods_is_active', $this->boundComponent->getCategories()
            ));
            $max = ceil($this->dbh->getScalar(
                'select max(goods_price) from ' .
                $this->getParam('tableName') .
                ' where smap_id IN (%s) AND goods_is_active', $this->boundComponent->getCategories()
            ));
            $begin = (isset($this->filter_data['price']['begin'])) ? (float)$this->filter_data['price']['begin'] : $min;
            $end = (isset($this->filter_data['price']['end'])) ? (float)$this->filter_data['price']['end'] : $max;
            if ($begin < $min) $begin = $min;
            if ($end > $max) $end = $max;
            if (($min && $max && $begin && $end)) {
                $fd->setProperty('text-from', $this->translate('TXT_FROM'));
                $fd->setProperty('text-to', $this->translate('TXT_TO'));

                foreach (['min', 'max', 'begin', 'end'] as $var) {
                    $fd->setProperty('range-' . $var, number_format($$var, 2, '.', ''));
                }

                $fd->setProperty('range-step', 1);
            } elseif ($this->getParam('removeEmptyPriceFilter')) {
                $this->getDataDescription()->removeFieldDescription($fd);
            }

        }
    }

    protected function buildDivisionFilter()
    {
        if ($fd = $this->getDataDescription()->getFieldDescriptionByName('divisions')) {
            // todo: ничего не делаем, фильтр по разделам на совести xslt
            //$this -> getDataDescription() -> removeFieldDescription($fd);
        }
    }

    protected function buildFeatureFilter()
    {
        if ($fd = $this->getDataDescription()->getFieldDescriptionByName('features')) {

            // убираем field description, ибо это фейковое поле
            $this->getDataDescription()->removeFieldDescription($fd);

            // feature_ids текущего раздела
            $documentId = $this->document->getId();
            $div_feature_ids = $this->dbh->getColumn(
                'shop_sitemap2features',
                'feature_id',
                [
                    'smap_id' => $documentId
                ]
            );
            $this->getCategoriesRecursively($documentId);
            $filteredProductIds = $this->getFilterProducts();
            if (!empty($filteredProductIds)) {
                $relatedProducts = $filteredProductIds;
            } else {
                $relatedProducts = $this->dbh->getColumn(
                    'SELECT goods_id FROM shop_goods WHERE smap_id IN (%s)',
                    $this->categoryIds
                );
            }

            // добавляем в форму только активные фичи, предназначенные для фильтра
            if ($div_feature_ids) {
                $result = $this->getFilteredProducts($div_feature_ids);
                foreach ($div_feature_ids as $feature_id) {
                    $feature = FeatureFieldFactory::getField($feature_id, null, $relatedProducts);

                    if ($feature->isActive() and $feature->isFilter()) {
                        $filter_data = isset($this->filter_data['features'][$feature->getFilterFieldName()]) ? $this->filter_data['features'][$feature->getFilterFieldName()] : false;
                        $this->getDataDescription()->addFieldDescription($feature->getFilterFieldDescription($filter_data));
                        $this->getData()->addField($feature->getFilterField($filter_data));
                    }
                }
            }
        }
    }

    public function main()
    {
        $this->boundComponent = E()->getDocument()->componentManager->getBlockByName($this->getParam('bind'));
        if (!$this->getParam('showForProduct') && ($this->boundComponent->getState() == 'view')) {
            $this->disable();
            return;
        }

        $this->prepare();
        /**
         * @var GoodsList
         */
        $this->setProperty('action', substr(array_reduce($this->boundComponent->getSortData(), function ($p, $c) {
                                       return $p . $c . '-';
                                   }, 'sort-'), 0, -1) . '/');

        $this->filter_data = $this->boundComponent->getFilterData();

        $this->setProperty('applied', ($this->filter_data ? '1' : '0'));

        // если в конфиге задан фильтр по цене
        $this->buildPriceFilter();

        // если в конфиге задан фильтр по подразделам
        $this->buildDivisionFilter();

        // если в конфиге задан вывод фильтра по характеристикам
        $this->buildFeatureFilter();

        $this->buildProducersFilter();

    }

    protected function buildProducersFilter()
    {
        if ($fd = $this->getDataDescription()->getFieldDescriptionByName('producers')) {

            $producers = $this->dbh->getColumn('SELECT DISTINCT producer_id  FROM shop_goods WHERE smap_id IN (%s)', $this->boundComponent->getCategories());
            $fd->setType(FieldDescription::FIELD_TYPE_MULTI);
            $fd->setProperty('title', 'FILTER_PRODUCERS');
            if ($values = $this->dbh->select('SELECT p.producer_id, producer_name FROM shop_producers p LEFT JOIN shop_producers_translation pt ON(p.producer_id=pt.producer_id) AND (lang_id=%s) WHERE p.producer_id IN (%s)', $this->document->getLang(), $producers)) {
                $fd->loadAvailableValues($values, 'producer_id', 'producer_name');
                if (isset($this->filter_data['producers']) && !empty($this->filter_data['producers'])) {
                    $f = new Field('producers');
                    $f->setData([$this->filter_data['producers']], true);
                    $this->getData()->addField($f);

                }
            } else {
                $this->getDataDescription()->removeFieldDescription($fd);
            }
        }
    }

    public function build()
    {
        if ($this->getState() == 'main') {
            $this->setProperty('filter-name', self::FILTER_GET);
            foreach ($this->getDataDescription() as $fd) {
                $fd->setProperty('tableName', self::FILTER_GET);
            }
        }

        $result = parent::build();

        if ($this->getState() == 'showParams' && $result->documentElement->childNodes->item(0)->hasAttributes()) {
            $result->documentElement->childNodes->item(0)->setAttribute('empty', E()->Utils->translate('TXT_EMPTY_SAVED_FILTER'));
        }


        return $result;
    }

    private function getCategoriesRecursively($smap_id)
    {
        if (!in_array($smap_id, $this->categoryIds)){
            $this->categoryIds[] = (int) $smap_id;
            $categories = $this->dbh->getColumn(
                'SELECT s.smap_id FROM share_sitemap s WHERE s.smap_pid = %s', $smap_id
            );
            foreach ($categories as $category) {
                if(!in_array($category, $this->categoryIds)){
                    $this->getCategoriesRecursively($category);
                }
            }
        }
    }

    private function getFilterProducts()
    {
        $whereCondition = $this->boundComponent->getFilterWhereConditions();
        $conditions = array('(' => '', ')' => '', 'IN' => '', 'AND' => '', 'in' => '', ' ' => '');
        $clearString = strtr($whereCondition, $conditions);

        preg_match('/(?<=goods_id)[\d,]*/', $clearString, $productIds);

        if (count($productIds) > 0) {
            return explode(',', $productIds[0]);
        } else {
            return false;
        }

    }

}