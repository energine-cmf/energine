<?php

/**
 * Содержит класс GoodsLookup
 * @package energine
 * @author dr.Pavka
 * @copyright Energine 2015
 */
namespace Energine\shop\components;

use Energine\share\components\Grid;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\Filter;
use Energine\share\gears\FilterExpression;
use Energine\share\gears\FilterField;

/**
 * Test
 * @package energine
 * @author dr.Pavka
 */
class GoodsLookup extends Grid {
	public function __construct($name, array $params = null) {
		parent::__construct($name, $params);
		$this->setTableName('shop_goods');
	}

	/**
	 * Apply user filter.
	 */
	protected function applyUserFilter() {
		//получили данные о текущем фильтре
		if ($f = FilterExpression::createFromPOST()) {
			//Добавили к фильтру новое условие
			$f->add(
			//Значение фильтра взяли из того что пришло
				(new FilterField('goods_code'))->setOperator('OR')->setValue($f->current()->getValue())
					->setCondition('like')
					->setAttribute('tableName', $this->getTableName()
					)
			);
			//и применили
			//в результате получилось что то типа
			//(shop_goods_translation.goods_name LIKE '%условие%' ) OR (shop_goods.goods_code LIKE '%условие%' )"
			//inspect((string)$f);
			(new Filter($f))->apply($this);
		}
	}

	protected function loadData() {
		$result = parent::loadData();
		if (in_array($this->getType(), [self::COMPONENT_TYPE_LIST]) && is_array($result)) {
			$result = array_map(function ($row) {
				$row['goods_real_name'] = $row['goods_name'];
				$row['goods_name'] = $row['goods_real_name'] . ' ' . $row['goods_code'];

				return $row;
			}, $result);
		}

		return $result;
	}

	protected function createDataDescrition() {
		$result = parent::createDataDescription();
		if (in_array($this->getType(), [self::COMPONENT_TYPE_LIST])) {
			$f = new FieldDescription('goods_real_name');
			$f->setType(FieldDescription::FIELD_TYPE_STRING);
			$result->addFieldDescription($f);

			$result->getFieldDescriptionByName('goods_name')->setType(FieldDescription::FIELD_TYPE_HIDDEN);
		}

		return $result;
	}
}