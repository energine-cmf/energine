<?php
/**
 * Содержит класс PageList
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */


/**
 * Класс выводит список подразделов
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class PageList extends DataSet {
    const CURRENT_PAGE = 'current';
    const PARENT_PAGE = 'parent';
    const ALL_PAGES = 'all';
    /**
     * Идентификатор раздела для которого мы выводим чайлдов
     *
     * @access private
     * @var int
     */
    private $pid;

    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module,  array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->addTranslation('TXT_HOME');
    }

    protected function createBuilder() {
        if ($this->getParam('recursive')) {
            $builder = new TreeBuilder();
        }
        else {
            $builder = new SimpleBuilder();
        }

        return $builder;
    }

    /**
     * Добавлены параметр tags - теги
     * id - идентификатор страницы или CURRENT_PAGE | PARENT_PAGE | ALL_PAGES
     * site - идентфиикатор сайта
     * recursive - рекурсивно
     *
     * @return array
     * @access protected
     */

    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
            array(
                'tags' => '',
                'id' => false,
                'site' => SiteManager::getInstance()->getCurrentSite()->id,
                'recursive' => false
            ));
        return $result;
    }

    /**
     * Добавляем информацию о присоединенных файлах
     *
     * @return void
     * @access protected
     */
    protected function main() {
        parent::main();
        $siteFD = new FieldDescription('Site');
        $siteFD->setType(FieldDescription::FIELD_TYPE_STRING);
        $this->getDataDescription()->addFieldDescription($siteFD);
        if ($this->getDataDescription()->getFieldDescriptionByName('attachments')) {
            $this->getDataDescription()->addFieldDescription(E()->AttachmentManager->createFieldDescription());
            if (!$this->getData()->isEmpty()) {
                $this->getData()->addField(
                    E()->AttachmentManager->createField(
                        $this->getData()->getFieldByName('Id')->getData(), 'smap_id', 'share_sitemap_uploads')
                );
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
        $sitemap = Sitemap::getInstance($this->getParam('site'));

        $methodName = 'getChilds';
        if ($this->getParam('recursive')) {
            $methodName = 'getDescendants';
        }
        //Выводим siblin
        if ($this->getParam('id') == self::PARENT_PAGE) {
            $param = $sitemap->getParent($this->document->getID());
        }
            //выводим child текуще
        elseif ($this->getParam('id') == self::CURRENT_PAGE) {
            $param = $this->document->getID();
        }
            //выводим все разделы
        elseif ($this->getParam('id') == self::ALL_PAGES) {
            $methodName = 'getInfo';
            $param = null;
        }
            //если пустой
            //выводим главное меню
        elseif (!$this->getParam('id')) {
            $param = $sitemap->getDefault();
        }
            //выводим child переданной в параметре
        else {
            $param = (int) $this->getParam('id');
        }

        $data = call_user_func(array($sitemap, $methodName), $param);

        if (!empty($data)) {
            if ($this->getParam('recursive')) {
                $this->getBuilder()->setTree($sitemap->getChilds($param, true));
            }
            $hasDescriptionRtf =
                    (bool) $this->getDataDescription()->getFieldDescriptionByName('DescriptionRtf');

            //По умолчанию - фильтрация отсутствует
            $filteredIDs = true;
            if ($this->getParam('tags'))
                $filteredIDs =
                        E()->TagManager->getFilter($this->getParam('tags'), 'share_sitemap_tags');

            reset($data);
            while (list($key, $value) = each($data)) {
                if (($filteredIDs !== true) && is_array($filteredIDs) && !in_array($key, $filteredIDs)) {
                    unset($data[$key]);
                    continue;
                }
                if ($key == $sitemap->getDefault()) {
                    unset($data[$key]);
                }
                else {
                    $data[$key]['Id'] = $key;
                    $data[$key]['Segment'] = $value['Segment'];
                    $data[$key]['Name'] = $value['Name'];
                    $data[$key]['Site'] =
                            SiteManager::getInstance()->getSiteByID($data[$key]['site'])->base;
                    if ($hasDescriptionRtf)$data[$key]['DescriptionRtf'] =
                            $value['DescriptionRtf'];
                }

            }
        }
        else {
            $this->setBuilder(new SimpleBuilder());
        }
        return $data;
    }
}
