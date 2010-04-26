<?php
/**
 * Содержит класс NewsEditor
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id: NewsEditor.class.php,v 1.10 2008/08/27 15:39:16 chyk Exp $
 */

/**
 * Редактор новостей сайта
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class NewsEditor extends FeedEditor {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
	public function __construct($name, $module, Document $document, array $params = null) {
        parent::__construct($name, $module, $document, $params);
        $this->setTableName('share_news');
        $this->setOrder('news_date', QAL::DESC);
	}
}