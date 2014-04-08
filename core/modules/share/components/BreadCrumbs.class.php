<?php
/**
 * Содержит класс BreadCrumbs
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 */


/**
 * "Хлебные крошки"
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @final
 */
final class BreadCrumbs extends DataSet {
    /**
     * Список дополнительных элементов
     * Необходим для того чтобы другие компоненты могли добавлять хлебные крошки
     * @var array
     * @access private
     */
    private $additionalCrumbs = array();

    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->setProperty('site', E()->getSiteManager()->getCurrentSite()->name);
    }

    /**
     * Поскольку изменение перечня полей невозможно, принудительно выставляем необходимые значения
     *
     * @return DataDescription
     * @access protected
     */

    protected function createDataDescription() {
        $result = new DataDescription();
        $field = new FieldDescription('Id');
        $field->setType(FieldDescription::FIELD_TYPE_INT);
        $field->setProperty('key', true);
        $result->addFieldDescription($field);

        $field = new FieldDescription('Name');
        $field->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($field);

        $field = new FieldDescription('Segment');
        $field->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($field);

        $field = new FieldDescription('Title');
        $field->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($field);

        return $result;
    }

    protected function prepare() {
        $this->setBuilder(new SimpleBuilder());
        $this->setDataDescription($this->createDataDescription());
        if (!$this->getData()) {
            $data = $this->createData();
            if ($data instanceof Data) {
                $this->setData($data);
            }
        }
    }

    /**
     * Переопределенный метод загрузки данных
     *
     * @return mixed
     * @access protected
     */
    protected function loadData() {
        $sitemap = E()->getMap();
        $result = array();
        $parents = $sitemap->getParents($this->document->getID());
        foreach ($parents as $id => $current) {
            $result[] = array(
                'Id' => $id,
                'Name' => $current['Name'],
                'Segment' => $current['Segment'],
                'Title' => $current['HtmlTitle'],
            );
        }
        $docInfo = $sitemap->getDocumentInfo($this->document->getID());
        $result[] = array(
            'Id' => $this->document->getID(),
            'Name' => $docInfo['Name'],
            'Segment' => $sitemap->getURLByID($this->document->getID()),
            'Title' => $docInfo['HtmlTitle']
        );
        if (!empty($this->additionalCrumbs)) {
            $result = array_merge($result, $this->additionalCrumbs);
        }


        // добавляем информацию о главной странице в начало
        $defaultID = $sitemap->getDefault();
        if (($this->document->getID() != $defaultID) && (isset($result[0]) && ($result[0]['Id'] != $defaultID))) {
            $docInfo = $sitemap->getDocumentInfo($defaultID);
            $result = array_push_before(
                $result,
                array(
                    array(
                        'Id' => $defaultID,
                        'Name' => $docInfo['Name'],
                        'Segment' => '',
                        'Title' => $docInfo['HtmlTitle']
                    )
                ),
                0
            );
        }

        return $result;
    }

    /**
     * Метод добавляющий хлебную крошку
     * Если приходят пустые параметры, то эта крошка не выводится, а предыдущая хлебная крошка будет ссылкой
     *
     * @param int
     * @param string
     * @param segment
     * @return void
     * @access public
     */

    public function addCrumb($smapID = '', $smapName = '', $smapSegment = '') {
        $this->additionalCrumbs[] = array(
            'Id' => $smapID,
            'Name' => $smapName,
            'Segment' => $smapSegment
        );
    }

    /**
     * Замещает текущие данными - новыми
     * @param $data array(array('Id'=>'', 'Name'=>'', 'Segment'=>''))
     */
    public function replaceData($data) {
        $d = new Data();
        $d->load($data);
        $this->setData($d);
    }

    /**
     * Remove portion from additioan crumbs
     * @param int $indexFromEnd Индекс
     */
    public function removeCrumb($indexFromEnd){
        $this->additionalCrumbs = array_slice($this->additionalCrumbs, 0 , -$indexFromEnd);
    }
}
