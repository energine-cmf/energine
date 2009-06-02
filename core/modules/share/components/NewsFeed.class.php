<?php
/**
 * Содержит класс NewsFeed
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright ColoCall 2007
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
        $this->setTableName('share_news');
        $this->setOrder(array('news_date'=>QAL::DESC));
    }

    /**
     * Добавляем поле - флаг указывающий на то - существует ли текст новости
     *
     * @return void
     * @access protected
     */

     protected function main() {
        parent::main();
		if($this->getData() && $newsID = $this->getData()->getFieldByName('news_id')){
			$hasTextField = new FieldDescription('has_text');
			$hasTextField->setType(FieldDescription::FIELD_TYPE_BOOL);
			$this->getDataDescription()->addFieldDescription($hasTextField);

			$hasTextField = new Field('has_text');
			foreach ($newsID as $id) {
				$hasTextField->addRowData(simplifyDBResult($this->dbh->select($this->getTranslationTableName(), array('news_text_rtf is not null as has_text'), array('news_id'=>$id)), 'has_text', true));
			}
			$this->getData()->addField($hasTextField);
		}
     }

    /**
	 * View
	 *
	 * @return type
	 * @access protected
	 */

    protected function view() {
        $params = $this->getActionParams();

        list($day, $month, $year) = $params;
        $this->addFilterCondition(
        	array('news_date'=>sprintf('%s-%s-%s', $year, $month, $day))
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
        if ($toolbar = $this->createToolbar()) {
            $this->setToolbar($toolbar);
        }
        foreach ($this->getDataDescription()->getFieldDescriptions() as $fieldDescription) {
            $fieldDescription->setMode(FieldDescription::FIELD_MODE_READ);
        }

    }
}