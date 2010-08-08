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
 * @final
 */
final class PageList extends DataSet {
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
    public function __construct($name, $module, Document $document, array $params = null) {
        parent::__construct($name, $module, $document, $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->addTranslation('TXT_HOME');
    }

    protected function createBuilder() {
        return new SimpleBuilder();
    }

    /**
     * Добавлен параметр tags
     *
     * @return int
     * @access protected
     */

    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
            array(
                'tags' => '',
                'id' => false,
                'site' => SiteManager::getInstance()->getCurrentSite()->id
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
            $this->getDataDescription()->addFieldDescription(AttachmentManager::getInstance()->createFieldDescription());
            if (!$this->getData()->isEmpty()) {
                $this->getData()->addField(
                    AttachmentManager::getInstance()->createField(
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

        //Выводим siblin
        if ($this->getParam('id') == self::PARENT_PAGE) {
            $data = $sitemap->getChilds(
                $sitemap->getParent(
                    $this->document->getID()
                )
            );
        }
            //выводим child текуще
        elseif ($this->getParam('id') == self::CURRENT_PAGE) {
            $data = $sitemap->getChilds(
                $this->document->getID()
            );
        }
            //выводим все разделы
        elseif ($this->getParam('id') == self::ALL_PAGES) {
            $data = $sitemap->getInfo();
        }
            //если пустой
            //выводим главное меню
        elseif (!$this->getParam('id')) {
            $data = $sitemap->getChilds($sitemap->getDefault());
        }
            //выводим child переданной в параметре
        else {
            $data = $sitemap->getChilds((int) $this->getParam('id'));
        }

        if (!empty($data)) {
            $hasDescriptionRtf =
                    (bool) $this->getDataDescription()->getFieldDescriptionByName('DescriptionRtf');

            //По умолчанию - фильтрация отсутствует
            $filteredIDs = true;
            if ($this->getParam('tags'))
                $filteredIDs =
                        TagManager::getInstance()->getFilter($this->getParam('tags'), 'share_sitemap_tags');

            reset($data);
            while (list($key, $value) = each($data)) {
                if (($filteredIDs !== true) && !in_array($key, $filteredIDs)) {
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
                    $data[$key]['Site'] = SiteManager::getInstance()->getSiteByID($data[$key]['site'])->base;
                    if ($hasDescriptionRtf)$data[$key]['DescriptionRtf'] =
                            $value['DescriptionRtf'];
                }

            }
        }
        return $data;
    }
}
