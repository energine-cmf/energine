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
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->addTranslation('TXT_HOME');
        if($this->getParam('site') == 'default'){
            $this->setParam('site', E()->getSiteManager()->getDefaultSite()->id);
        }
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
                'site' => E()->getSiteManager()->getCurrentSite()->id,
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
        if($this->getDataDescription()->isEmpty()){
            $this->getDataDescription()->loadXML(
                new SimpleXMLElement('<fields>
                            <field name="Id" type="integer" key="1"/>
                            <field name="Pid" type="integer"/>
                            <field name="Name" type="string"/>
                            <field name="Segment" type="string"/>
                            <field name="DescriptionRtf" type="string"/>
                        </fields>')
            );
        }
        if(!$this->getData()->isEmpty())
            foreach (array('Site', 'Redirect') as $fieldName) {
                $FD = new FieldDescription($fieldName);
                $FD->setType(FieldDescription::FIELD_TYPE_STRING);
                $this->getDataDescription()->addFieldDescription($FD);
            }

        if ($this->getDataDescription()->getFieldDescriptionByName('attachments')) {
            $am = new AttachmentManager(
                $this->getDataDescription(),
                $this->getData(),
                'share_sitemap'
            );
            //$am->createFieldDescription();
            if ($f = $this->getData()->getFieldByName('Id'))
                $am->createField('smap_id', true, $f->getData());
        }


    }

    /**
     * Переопределенный метод загрузки данных
     *
     * @return mixed
     * @access protected
     */

    protected function loadData() {
        $sitemap = E()->getMap($this->getParam('site'));

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
            $param = (int)$this->getParam('id');
        }

        $data = call_user_func(array($sitemap, $methodName), $param);

        if (!empty($data)) {
            if ($this->getParam('recursive')) {
                $this->getBuilder()->setTree($sitemap->getChilds($param, true));
            }
            $hasDescriptionRtf =
                    (bool)$this->getDataDescription()->getFieldDescriptionByName('DescriptionRtf');

            //По умолчанию - фильтрация отсутствует
            $filteredIDs = true;
            if ($this->getParam('tags'))
                $filteredIDs =
                        TagManager::getFilter($this->getParam('tags'), 'share_sitemap_tags');

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
                    $data[$key]['Redirect'] = Response::prepareRedirectURL($value['RedirectUrl']);
                    $data[$key]['Site'] =
                            E()->getSiteManager()->getSiteByID($data[$key]['site'])->base;
                    if ($hasDescriptionRtf) $data[$key]['DescriptionRtf'] =
                            $value['DescriptionRtf'];
                }

            }
            //stop($data);
        }
        else {
            $this->setBuilder(new SimpleBuilder());
        }
        return $data;
    }
}
