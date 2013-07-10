<?php
/**
 * Содержит класс NewsRepository
 *
 * @package energine
 * @subpackage apps
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Редактор
 *
 * @package energine
 * @subpackage apps
 * @author d.pavka@gmail.com
 */
class NewsRepository extends NewsEditor {
    /**
     * @var DivisionEditor
     */
    private $divisionEditor;

    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->enable();
        $this->setProperty('exttype', 'grid');
        $this->setSaver(new NewsEditorSaver());
    }

    /**
     * Добавляем параметр  - имя связанного компонента
     *
     * @return array
     * @access protected
     */

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'bind' => false
            )
        );
    }

    /**
     * Вывод дерева разделов для форм добавления/редактирования
     */
    protected function showSmapSelector() {
        $this->request->shiftPath(1);
        $this->divisionEditor = ComponentManager::createBlockFromDescription(
            ComponentManager::getDescriptionFromFile('core/modules/apps/templates/content/site_div_selector.container.xml'));
        $this->divisionEditor->run();
    }

    /**
     * Переписываем чтобы вернуть smap_id
     * @return DataDescription
     */
    protected function createDataDescription() {
        $dd = LinkingEditor::createDataDescription();
        if (in_array($this->getState(), array('add', 'edit'))) {
            $dd->getFieldDescriptionByName('smap_id')->setType(FieldDescription::FIELD_TYPE_SMAP_SELECTOR);
        }
        return $dd;
    }

    /**
     * Вернули выбор раздела
     */
    protected function edit() {
        parent::edit();
        $smapField = $this->getData()->getFieldByName('smap_id');
        for ($i = 0; $i < sizeof(E()->getLanguage()->getLanguages()); $i++) {
            $smapField->setRowProperty($i, 'smap_name', $this->dbh->getScalar(
                'SELECT CONCAT(site_name, ":", smap_name) as smap_name FROM share_sitemap sm LEFT JOIN share_sitemap_translation smt USING(smap_id) LEFT JOIN share_sites_translation s ON (s.site_id = sm.site_id) AND (s.lang_id = %s) WHERE sm.smap_id = %s AND smt.lang_id= %1$s', $this->document->getLang(), $smapField->getRowData(0)
            ));
        }
    }

    /**
     * Переписываем чтобы вернуть smap_id
     * @return Data
     */
    protected function createData() {
        return LinkingEditor::createData();
    }

    /**
     * Убираем все лишнее
     *
     * @return void
     * @access protected
     */

    protected function main() {
        Grid::main();
    }

    protected function changeOrder($direction) {
        Grid::changeOrder($direction);
    }

    protected function saveData() {
        return LinkingEditor::saveData();
    }

    public function build() {
        if ($this->getState() == 'showSmapSelector') {
            $result = $this->divisionEditor->build();
        } else {
            $result = Grid::build();
        }
        return $result;
    }

}