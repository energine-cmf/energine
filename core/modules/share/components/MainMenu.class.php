<?php

/**
 * Содержит класс MainMenu
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */
//require_once('core/modules/share/components/DataSet.class.php');

/**
 * Класс выводит главное меню(меню первого уровня)
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @final
 */
final class MainMenu extends DataSet {
    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->addTranslation('TXT_HOME');
    }
    /**
     * Принудительно выставляем необходимый перечень полей
     *
     * @return DataDescription
     * @access protected
     */

    protected function createDataDescription() {
        $result = new DataDescription();

        $field = new FieldDescription('Id');
        $field->setType(FieldDescription::FIELD_TYPE_INT);
        $field->addProperty('key', true);
        $result->addFieldDescription($field);

        $field = new FieldDescription('Name');
        $field->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($field);

        $field = new FieldDescription('Segment');
        $field->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($field);

        return $result;
    }
    /**
     * Переопределенный метод загрузки данных
     *
     * @return mixed
     * @access protected
     */

    protected function loadData() {
        $sitemap = Sitemap::getInstance();
        $data = $sitemap->getMainLevel();
        if (empty($data)) {
            $this->generateError(SystemException::ERR_WARNING, 'ERR_NO_DATA');
        }

        foreach ($data as $key => $value) {
            if($key == $sitemap->getDefault()) {
                unset($data[$key]);
            }
            else {
                $data[$key]['Id'] = $key;
                $data[$key]['Segment'] = $value['Segment'];
                $data[$key]['Name'] = $value['Name'];
            }
        }
        return $data;
    }
}
