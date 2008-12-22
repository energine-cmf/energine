<?php
/**
 * Содержит класс TranslationEditor
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * Редактор переводов
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class TranslationEditor extends Grid {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
	public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setTableName('share_lang_tags');
        $this->setOrder(array('ltag_name' =>QAL::ASC ));
	}
}