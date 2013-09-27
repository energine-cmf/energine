<?php
/**
 * Содержит класс TagEditor
 *
 * @package energine
 * @subpackage share
 * @author andy.karpov
 * @copyright Energine 2013
 */

/**
 * Редактор тегов
 *
 * @package energine
 * @subpackage share
 * @author andy.karpov
 */
class TagEditor extends Grid {
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

        $this->setTableName(TagManager::TAG_TABLENAME);

        $tags = (!empty($_REQUEST['tags'])) ? $_REQUEST['tags'] : '-';
        $tags = array_filter(
            array_map(
                create_function(
                    '$tag',
                    'return mb_convert_case(trim($tag), MB_CASE_LOWER, "UTF-8");'
                ),
                explode(TagManager::TAG_SEPARATOR, $tags)
            )
        );

        $tag_ids = TagManager::getID($tags);

        if ($this->getState() != 'save' && !empty($tag_ids)) {
            $this->addFilterCondition(array('tag_id' => array_keys($tag_ids)));
        }
    }
}