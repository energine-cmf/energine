<?php
/**
 * @file
 * SitemapTree
 *
 * It contains the definition to:
 * @code
class SitemapTree;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\components;

use Energine\share\gears\TagManager;
use Energine\share\gears\TreeBuilder;

/**
 * Site map.
 *
 * @code
class SitemapTree;
 * @endcode
 */
class SitemapTree extends DataSet
{
    /**
     * @copydoc Component::defineParams
     */
    protected function defineParams()
    {
        return array_merge(
            parent::defineParams(),
            [
                'tags' => false
            ]
        );
    }

    protected function main()
    {
        $this->setType(self::COMPONENT_TYPE_LIST);
        parent::main();
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
    // Загружает данные о дереве разделов
    protected function loadData()
    {
        $sitemap = E()->getMap();
        $res = $sitemap->getInfo();
        $filteredIDs = [];

        if ($this->getParam('tags'))
            $filteredIDs =
                TagManager::getFilter($this->getParam('tags'), 'share_sitemap');

        foreach ($res as $id => $info) {
            if (in_array($id, $filteredIDs) || !$info['Pid']) {
                $result [] = array(
                    'Id' => $id,
                    'Pid' => $info['Pid'],
                    'Name' => $info['Name'],
                    'Segment' => $sitemap->getURLByID($id)
                );
            }
        }

        return $result;
    }

    /**
     * @copydoc DataSet::createBuilder
     */
    protected function createBuilder()
    {
        $builder = new TreeBuilder();
        $builder->setTree(E()->getMap()->getTree());
        return $builder;
    }
}