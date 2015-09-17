<?php
/**
 * @file
 * SimilarNews
 *
 * It contains the definition to:
 * @code
class SimilarNews;
 * @endcode
 *
 * @author Andrii A
 * @copyright eggmengroup.com
 *
 * @version 1.0.0
 */
namespace Energine\apps\components;

use Energine\share\components\DataSet;
use Energine\share\gears\ComponentProxyBuilder;

/**
 * Similar news.
 *
 * @code
class SimilarNews;
 * @endcode
 */
class SimilarNews extends DataSet {
    /**
     * Default component name for binding.
     */
    const DEFAULT_LINK_TO = 'news';
    /**
     * Separator of tag names.
     */
    const TAG_SEPARATOR = ',';

    /**
     * News ID.
     * @var int $newsID
     */
    private $newsID;

    /**
     * Component to which "Similar news" will be bounded.
     * @var NewsFeed $cp
     */
    private $cp;

    /**
     * @copydoc DBDataSet::__construct
     */
    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $bindComponentName = ($this->getParam('bind')) ? $this->getParam('bind') : self::DEFAULT_LINK_TO;
        $this->cp =
            E()->Document->componentManager->getBlockByName($bindComponentName);
        if (!$this->cp || ($this->cp && $this->cp->getState() != 'view')) {
            $this->disable();
        }
        $this->setParam('recordsPerPage', false);
    }

    /**
     * @copydoc DBDataSet::defineParams
     */
    // Определяет допустимые параметры компонента и их значения по-умолчанию в виде массива array(paramName => defaultValue).
    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
            [
                'limit' => 5,
                'bind' => ''
            ]);
        return $result;
    }

    /**
     * @copydoc DBDataSet::main
     */
    protected function main() {
        $ap = $this->cp->getStateParams(true);
        /**
         * @var $tag Array of tag ids
         */
        $tags = $this->dbh->getColumn($this->dbh->getTagsTablename($this->cp->getTableName()), 'tag_id', ['news_id' => (int)$ap['id']]);

        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->setBuilder($b = new ComponentProxyBuilder());
        $params = [
            'active' => false,
            'state' => 'main',
            'tags' => $tags,
            'tableName' => $this->cp->getTableName(),
            'exclude' => (int)$ap['id'],
            'limit' => $this->getParam('limit')
        ];
        $b->setComponent(
            'simNews',
            'Energine\\apps\\components\\NewsFeed',
            $params
        );
    }
}