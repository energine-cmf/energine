<?php
/**
 * @file
 * PageList
 *
 * It contains the definition to:
 * @code
class PageList;
 * @endcode
 *
 * @author dr.Pavka
 *
 * @version 1.0.0
 */
namespace Energine\share\components;

use Energine\share\gears\TreeBuilder, Energine\share\gears\SimpleBuilder, Energine\share\gears\FieldDescription, Energine\share\gears\Response, Energine\share\gears\TagManager, Energine\share\gears\AttachmentManager;
/**
 * Show the list of subsections.
 *
 * @code
class PageList;
 * @endcode
 */
class PageList extends DataSet
{
    /**
     * Current page.
     * @var string CURRENT_PAGE
     */
    const CURRENT_PAGE = 'current';

    /**
     * Parent page.
     * @var string PARENT_PAGE
     */
    const PARENT_PAGE = 'parent';
    /**
     * All pages.
     * @var string ALL_PAGES
     */
    const ALL_PAGES = 'all';

    /**
     * @copydoc DataSet::__construct
     */
    public function __construct($name, $module, array $params = null)
    {
        parent::__construct($name, $module, $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->addTranslation('TXT_HOME');
        if ($this->getParam('site') == 'default') {
            $this->setParam('site', E()->getSiteManager()->getDefaultSite()->id);
        } elseif ($this->getParam('site') == 'current') {
            $this->setParam('site', E()->getSiteManager()->getCurrentSite()->id);
        }
    }

    /**
     * @copydoc DataSet::createBuilder
     */
    protected function createBuilder()
    {
        if ($this->getParam('recursive')) {
            $builder = new TreeBuilder();
        } else {
            $builder = new SimpleBuilder();
        }

        return $builder;
    }

    /**
     * @copydoc DataSet::defineParams
     */
    /*
     * Добавлены параметр tags - теги
     * id - идентификатор страницы или CURRENT_PAGE | PARENT_PAGE | ALL_PAGES
     * site - идентфиикатор сайта
     * recursive - рекурсивно
     */
    protected function defineParams()
    {
        $result = array_merge(parent::defineParams(),
            array(
                'tags' => '',
                'id' => false,
                'site' => false,
                'recursive' => false
            ));
        return $result;
    }

    /**
     * @copydoc DataSet::main
     */
    // Добавляем информацию о присоединенных файлах
    protected function main()
    {
        parent::main();
        if ($this->getDataDescription()->isEmpty()) {
            $this->getDataDescription()->loadXML(
                new \SimpleXMLElement('<fields>
                            <field name="Id" type="integer" key="1"/>
                            <field name="Pid" type="integer"/>
                            <field name="Name" type="string"/>
                            <field name="Segment" type="string"/>
                            <field name="DescriptionRtf" type="string"/>
                        </fields>')
            );
        }
        if (!$this->getData()->isEmpty()) {
            foreach (array('Site', 'Redirect') as $fieldName) {
                $FD = new FieldDescription($fieldName);
                $FD->setType(FieldDescription::FIELD_TYPE_STRING);
                $this->getDataDescription()->addFieldDescription($FD);
            }
        }

        if ($this->getDataDescription()->getFieldDescriptionByName('attachments')) {
            $am = new AttachmentManager(
                $this->getDataDescription(),
                $this->getData(),
                'share_sitemap'
            );
            $am->createFieldDescription();
            if ($f = $this->getData()->getFieldByName('Id'))
                $am->createField('smap_id', true, $f->getData());
        }
        if ($this->getDataDescription()->getFieldDescriptionByName('tags')) {
            $m = new TagManager(
                $this->getDataDescription(),
                $this->getData(),
                'share_sitemap'
            );
            $m->createFieldDescription();
            $m->createField();
        }

    }

    /**
     * @copydoc DataSet::loadData
     */
    protected function loadData()
    {
        $sitemap = E()->getMap();

        $methodName = 'getChilds';
        if ($this->getParam('recursive')) {
            $methodName = 'getDescendants';
        }

        //Выводим siblin
        if ($this->getParam('id') == self::PARENT_PAGE) {
            $param = $sitemap->getParent($this->document->getID());
        } //выводим child текуще
        elseif ($this->getParam('id') == self::CURRENT_PAGE) {
            $param = $this->document->getID();
        } //выводим все разделы
        elseif ($this->getParam('id') == self::ALL_PAGES) {
            $methodName = 'getInfo';
            $param = null;
            if (!($siteId = $this->getParam('site'))) {
                $siteId = E()->getSiteManager()->getCurrentSite()->id;
            }
            $sitemap = E()->getMap($siteId);
        } //если пустой id
        elseif (!$this->getParam('id')) {
            if ($this->getParam('site')) {
                $sitemap = E()->getMap($this->getParam('site'));
            }
            $param = $sitemap->getDefault();
        } //выводим child переданной в параметре
        else {
            $param = (int)$this->getParam('id');
            $sitemap = E()->getMap(E()->getSiteManager()->getSiteByPage($param)->id);
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
                } else {
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
        } else {
            $this->setBuilder(new SimpleBuilder());
        }
        return $data;
    }
}
