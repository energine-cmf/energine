<?php
/**
 * @file
 * FeatureOptionEditor
 *
 * It contains the definition to:
 * @code
class FeatureOptionEditor;
@endcode
 *
 * @author andy.karpov
 * @copyright Energine 2015
 *
 * @version 1.0.0
 */
namespace Energine\shop\components;
use Energine\share\components\Grid;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\QAL;
use Energine\shop\components\GoodsLookup;


class PromotionGoodsEditor extends Grid
{
	/**
	 * @copydoc Grid::__construct
	 */
	public function __construct($name,  array $params = null)
	{
		parent::__construct($name, $params);
		$this->setTableName('shop_goods2promotions');

		if (is_numeric($this->getParam('promotionID'))) {
			$filter = sprintf(' (promotion_id = %s) ', $this->getParam('promotionID'));
		} else {
			$filter = sprintf(' (promotion_id IS NULL and session_id="%s") ', session_id());
		}

		$this->setFilter($filter);

	}

	protected function defineParams()
	{
		return array_merge(
			parent::defineParams(),
			array(
				'promotionID' => false,
			)
		);
	}

	public function add()
	{
		parent::add();
		$data = $this->getData();
		if ($promotion_id = $this->getParam('promotionID')) {
			$f = $data->getFieldByName('promotion_id');
			$f->setRowData(0, $promotion_id);
		}
		$f = $data->getFieldByName('session_id');
		$f->setRowData(0, session_id());
	}

	public function edit()
	{
		parent::edit();
		$data = $this->getData();
		if ($promotion_id = $this->getParam('promotionID')) {
			$f = $data->getFieldByName('promotion_id');
			$f->setRowData(0, $promotion_id);
		}
	}

	protected function createDataDescription()
	{
		$result = parent::createDataDescription();

		if (in_array($this->getState(), array('add', 'edit'))) {

			$fd = $result->getFieldDescriptionByName('promotion_id');
			$fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);

			$fd = $result->getFieldDescriptionByName('session_id');
			$fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);

		}

		return $result;
	}

}