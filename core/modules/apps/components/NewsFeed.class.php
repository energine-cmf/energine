<?php
/**
 * Содержит класс NewsFeed
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id$
 */

//require_once('core/modules/share/components/DBDataSet.class.php');

/**
 * Лента новостей
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class NewsFeed extends Feed {
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
        $this->setTableName('apps_news');
        $this->setOrder('news_date', QAL::DESC);
    }


    /**
	 * View
	 *
	 * @return type
	 * @access protected
	 */

    protected function view() {
        $params = array_reverse(array_map(function($int){ return intval($int);}, $this->getActionParams()));
        array_unshift($params, '%s-%s-%s');
        $this->addFilterCondition(
        	array('news_date'=>call_user_func_array('sprintf', $params))
        );
        
        $this->addFilterCondition(array('smap_id' => $this->document->getID()));

        $this->setType(self::COMPONENT_TYPE_FORM);
        $this->setDataDescription($this->createDataDescription());
        $this->setBuilder($this->createBuilder());
        $this->createPager();
        $data = $this->createData();
        if ($data instanceof Data) {
            $this->setData($data);
            list($newsTitle) = $data->getFieldByName('news_title')->getData();
            $this->document->componentManager->getComponentByName('breadCrumbs')->addCrumb('', $newsTitle);
        }
        else {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }
        
        $this->addToolbar($this->createToolbar());
        
        foreach ($this->getDataDescription() as $fieldDescription) {
            $fieldDescription->setMode(FieldDescription::FIELD_MODE_READ);
        }

    }
}