<?php

/**
 * Содержит класс FeatureLookup
 * @package energine
 * @author andy.karpov
 * @copyright Energine 2015
 */
namespace Energine\shop\components;

use Energine\share\components\Grid;

/**
 * Test
 * @package energine
 * @author andy.karpov
 */
class FeatureLookup extends Grid {
	public function __construct($name, array $params = null) {
		parent::__construct($name, $params);
		$this->setTableName('shop_features');
	}
}