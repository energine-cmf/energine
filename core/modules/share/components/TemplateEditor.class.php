<?php

/**
 * Содержит класс TemplateEditor
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */


/**
 * Редактор шаблонов
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class TemplateEditor extends Grid {
    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setTableName('share_templates');
        $this->setTitle($this->translate('TXT_TEMPLATE_EDITOR'));
        $this->setOrderColumn('tmpl_order_num');
        $this->setOrder(array('tmpl_order_num' =>QAL::ASC));
    }
}
