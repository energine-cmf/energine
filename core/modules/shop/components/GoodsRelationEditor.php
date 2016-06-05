<?php
/**
 * @file
 * GoodsRelationEditor
 *
 * It contains the definition to:
 * @code
 * class GoodsRelationEditor;
 * @endcode
 *
 * @author andy.karpov
 * @copyright Energine 2015
 *
 * @version 1.0.0
 */
namespace Energine\shop\components;
use Energine\share\components\Grid;
use Energine\share\gears\FieldDescription;

/**
 * Feature option editor.
 *
 * @code
 * class GoodsRelationEditor;
 * @endcode
 */
class GoodsRelationEditor extends Grid {

	/**
	 * @copydoc Grid::__construct
	 */
	public function __construct($name,  array $params = null) {
		parent::__construct($name, $params);
		$this->setTableName('shop_goods_relations');

		if ($this->getParam('goodsID')) {
			$filter = sprintf(' (goods_from_id = %s) ', $this->getParam('goodsID'));
		} else {
			$filter = sprintf(' (goods_from_id IS NULL and session_id="%s") ', session_id());
		}

		$this->setFilter($filter);
	}

	/**
	 * @copydoc Grid::defineParams
	 */
	protected function defineParams() {
		return array_merge(
			parent::defineParams(),
			array(
				'goodsID' => false,
			)
		);
	}

	public function add() {
		parent::add();
		$data = $this->getData();
		if ($goods_id = $this -> getParam('goodsID')) {
			$f = $data->getFieldByName('goods_from_id');
			$f->setRowData(0, $goods_id);
		}
		$f = $data->getFieldByName('session_id');
		$f->setRowData(0, session_id());
	}

	public function edit() {
		parent::edit();
		if ($goods_id = $this -> getParam('goodsID')) {
			$data = $this->getData();
			$f = $data->getFieldByName('goods_from_id');
			$f->setRowData(0, $goods_id);
		}
	}

	protected function createDataDescription()
	{
		$result = parent::createDataDescription();

		if (in_array($this->getState(), array('add', 'edit'))) {
			$fd = $result->getFieldDescriptionByName('goods_from_id');
			$fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
			$fd = $result->getFieldDescriptionByName('session_id');
			$fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
		}

		return $result;
	}
}