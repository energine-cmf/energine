<?php
/**
 * @file
 * NewsRepository
 *
 * It contains the definition to:
 * @code
class NewsRepository;
 * @endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\apps\components;

use Energine\apps\gears\NewsEditorSaver, Energine\share\gears\ComponentManager, Energine\share\gears\FieldDescription, Energine\share\components\LinkingEditor, Energine\share\components\Grid;
use Energine\share\gears\DataDescription;

/**
 * News repository.
 *
 * @code
class NewsRepository;
 * @endcode
 */
class NewsRepository extends NewsEditor {
    /**
     * Division editor.
     * @var DivisionEditor $divisionEditor
     */
    private $divisionEditor;

    /**
     * @copydoc NewsEditor::__construct
     */
    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $this->enable();
        $this->setProperty('exttype', 'grid');
        $this->addFilterCondition([$this->getTableName().'.smap_id' => $this->dbh->getColumn('share_sitemap', 'smap_id', ['site_id' => $this->getSites()])]);
        //inspect($this->getFilter());
        $this->setSaver(new NewsEditorSaver());
    }

    /**
     * @copydoc NewsEditor::defineParams
     */
    // Добавляем параметр  - имя связанного компонента
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'bind' => false,
                'site' => false
            ]
        );
    }
    private function getSites() {
        $result = [];
        if ($siteID = $this->getParam('site')) {
            $result = [$siteID];
        } elseif ($this->document->getRights() < ACCESS_FULL) {
            $result = $this->document->getUser()->getSites();
            if (empty($result)) {
                $result = [0];
            }
        } else {
            foreach (E()->getSiteManager() as $site) {
                $result[] = $site->id;
            }
        }

        return $result;
    }

    /**
     * Show division tree for form of adding/editing.
     */
    protected function showSmapSelector() {
        $n = 1;
        $params = [];
        $sp = $this->getStateParams(true);

        if(isset($sp['smap_id'])){
            $n++;
            $siteID = E()->getSiteManager()->getSiteByPage($sp['smap_id'])->id;
            if($siteID){
                //$params['smap_id'] = $sp['smap_id'];
                $params['site'] = $siteID;
            }
        }

        $this->request->shiftPath($n);
        $this->divisionEditor = ComponentManager::createBlockFromDescription(
            ComponentManager::getDescriptionFromFile(CORE_DIR . '/modules/apps/templates/content/site_div_selector.container.xml'),
            $params
        );
        $this->divisionEditor->run();
    }

    /**
     * @copydoc NewsEditor::createDataDescription
     * @return DataDescription
     */
    // Переписываем чтобы вернуть smap_id
    protected function createDataDescription() {
        $dd = LinkingEditor::createDataDescription();
        if (in_array($this->getState(), ['add', 'edit'])) {
            $dd->getFieldDescriptionByName('smap_id')->setType(FieldDescription::FIELD_TYPE_SMAP_SELECTOR);

            $fd = new FieldDescription('news_is_top');
            $fd->setType(FieldDescription::FIELD_TYPE_BOOL);
            $fd->setProperty('tag', 'top');
            $dd->addFieldDescription($fd, DataDescription::FIELD_POSITION_AFTER, $this->getPK());

        }
        return $dd;
    }

    /**
     * @copydoc NewsEditor::edit
     */
    // Вернули выбор раздела
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
     * @copydoc NewsEditor::createData
     */
    // Переписываем чтобы вернуть smap_id
    protected function createData() {
        return LinkingEditor::createData();
    }

    /**
     * @copydoc Grid::main
     */
    protected function main() {
        Grid::main();
    }

    /**
     * @copydoc Grid::changeOrder
     */
    protected function changeOrder($direction) {
        Grid::changeOrder($direction);
    }

    /**
     * @copydoc LinkingEditor::saveData
     */
    protected function saveData() {
        return LinkingEditor::saveData();
    }

    /**
     * @copydoc NewsEditor::build
     */
    public function build() {
        if ($this->getState() == 'showSmapSelector') {
            $result = $this->divisionEditor->build();
        } else {
            $result = Grid::build();
        }
        return $result;
    }

}