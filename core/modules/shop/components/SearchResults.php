<?php

namespace Energine\shop\components;

use Energine\share\components\DataSet;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\ComponentProxyBuilder;

class SearchResults extends DataSet {

    /**
     * Bounded component.
     *
     * @var SearchForm $bindComponent
     */
    protected $bindComponent = NULL;

    protected $keyword = '';

    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $this->bindComponent = $this->document->componentManager->getBlockByName($this->getParam('bind'));
        $curr = E()['Energine\\shop\\gears\\Currency'];
        $this->setProperty('currency', $curr->getInfo()['currency_shortname']);
        $this->setProperty('currency-order', $curr->getInfo()['currency_shortname_order']);
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'bind' => false,
                'active' => false,
            ]
        );
    }

    protected function main() {
        parent::main();
        $this->setType(self::COMPONENT_TYPE_LIST);
        $products = ($this->keyword) ? $this->dbh->getColumn(
            'shop_goods_translation',
            'goods_id',
            [
                'goods_name LIKE "%' . $this->keyword . '%"',
                'lang_id' => $this->document->getLang()
            ]
        ) : [];

        $this->setBuilder($b = new ComponentProxyBuilder());
        $params = [
            'active' => true,
            'state' => 'main',
            'id' => $products,
            'list_features' => 'any' // вывод всех фич товаров в списке
        ];
        $b->setComponent('products', '\\Energine\\shop\\components\\GoodsList', $params);
        $this->addToolbar($this->loadToolbar());
        $this->js = $this->buildJS();
        // todo: pager
    }

    protected function prepare() {
        if ($this->bindComponent and $this->getState() == 'main') {
            $this->keyword = $this->bindComponent->getKeyword();
            parent::prepare();
        } elseif ($this->document->getProperty('single') and $this->getState() == 'main') {
            $this->keyword = (isset($_REQUEST[SearchForm::KEYWORD_FIELD_NAME])) ? $_REQUEST[SearchForm::KEYWORD_FIELD_NAME] : '';
            $this->setProperty('keyword', $this->keyword);
            $this->setProperty('keyword_name', SearchForm::KEYWORD_FIELD_NAME);
            $this->addTranslation('BTN_VIEW', 'TXT_ALL_SEARCH_RESULTS');
            E()->getController()->getTransformer()->setFileName('../../../../core/modules/shop/transformers/single_search.xslt');
        } else {
            $this->disable();
        }
    }
}
