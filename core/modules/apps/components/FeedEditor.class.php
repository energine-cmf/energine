<?php
/**
 * Содержит класс FeedEditor
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id$
 */

/**
 * Класс для построения редакторов управляющихся из панели управления
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class FeedEditor extends LinkingEditor {

	/**
	 * Конструктор класса
	 *
	 * @param string $name
	 * @param string $module
	 * @param Document $document
	 * @param array $params
	 * @access public
	 */
	public function __construct($name, $module,  array $params = null) {
		parent::__construct($name, $module, $params);
	}

	/**
	 * Для форм поле smap_id віводим как string
	 *
	 * @return DataDescription
	 * @access protected
	 */

	protected function createDataDescription() {
		$result = parent::createDataDescription();
		if (in_array($this->getType(), array(self::COMPONENT_TYPE_FORM_ADD, self::COMPONENT_TYPE_FORM_ALTER))) {
			$field = $result->getFieldDescriptionByName('smap_id');
			$field->setType(FieldDescription::FIELD_TYPE_STRING);
			$field->setMode(FieldDescription::FIELD_MODE_READ);

		}
		return $result;
	}

	/**
	 * Определяем данные для smap_id
	 *
	 * @return Data
	 * @access protected
	 */

	protected function createData() {

		$result = parent::createData();
		if (in_array($this->getType(), array(self::COMPONENT_TYPE_FORM_ADD, self::COMPONENT_TYPE_FORM_ALTER))) {
			$info = E()->getMap()->getDocumentInfo($this->document->getID());
			$field = $result->getFieldByName('smap_id');
			for($i=0; $i<sizeof(E()->getLanguage()->getLanguages()); $i++) {
				$field->setRowProperty($i, 'segment', E()->getMap()->getURLByID($this->document->getID()));
				$field->setRowData($i, $info['Name']);
			}
		}
		return $result;
	}

	/**
	 * Выставляем smap_id в текущее значение
	 *
	 * @return mixed
	 * @access protected
	 */

	protected function saveData() {
		$_POST[$this->getTableName()]['smap_id'] = $this->document->getID();
		$result = parent::saveData();
		return $result;
	}

}
