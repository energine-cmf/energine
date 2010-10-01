<?php
/**
 * Содержит класс ForumMessages
 *
 * @package energine
 * @subpackage forum
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Блок віводящий список сообщений с форума
 *
 * @package energine
 * @subpackage forum
 * @author d.pavka@gmail.com
 */
class ForumMessagesBlock extends DataSet {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document, array $params = null) {
        parent::__construct($name, $module, $document, $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->setProperty('exttype', 'feed');
        $this->addTranslation('TXT_READ_ALL_THEMES');
    }

    protected function createBuilder() {
        return new SimpleBuilder();
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'limit' => 10
            )
        );
    }

    protected function loadDataDescription() {
        $result = array(
            'comment_id' => array(
                'type' => QAL::COLTYPE_INTEGER,
                'length' => 10,
                'key' => true,
                'index' => 'PRI',
            ),
            'comment_created' => array(
                'type' => QAL::COLTYPE_DATETIME,
                'outputFormat' => '%E'
            ),
            'comment_name' => array(
                'type' => QAL::COLTYPE_STRING,
            ),
            'theme_name' => array(
                'type' => QAL::COLTYPE_STRING,
            ),
            'theme_url' => array(
                'type' => QAL::COLTYPE_STRING,
            ),
            'comment_url' => array(
                'type' => QAL::COLTYPE_STRING,
            ),
            'user_name' => array(
                'type' => QAL::COLTYPE_STRING,
            )
        );
        return $result;
    }

    protected function loadData() {
        $result = false;
        $result = $this->dbh->selectRequest('
            SELECT c.`comment_id`, SUBSTR(comment_name FROM 1 FOR 70) as comment_name, (SELECT CEIL(COUNT(comment_id)/20) FROM forum_theme_comment c2 WHERE c2.target_id=c.target_id) as comment_page, comment_created, theme_name, theme_id, smap_id as theme_url, u_nick as user_name
            FROM `forum_theme_comment` c
            LEFT JOIN forum_theme t ON c.target_id=t.theme_id
            LEFT JOIN user_users u ON c.u_id=u.u_id
            ORDER BY `comment_created`DESC
            LIMIT 0, '.$this->getParam('limit').'
        ');
        if(!empty($result) && is_array($result)){
            $result = array_map(function($row){
                $row['comment_name'] = strip_tags($row['comment_name']);
                $row['theme_url'] = Sitemap::getInstance(SiteManager::getInstance()->getDefaultSite()->id)->getURLByID($row['theme_url']).$row['theme_id'].'/';
                $row['comment_url'] = $row['theme_url'].'page-'.$row['comment_page'].'/#'.$row['comment_id'];
                unset($row['theme_id'], $row['comment_page']);
                return $row;
            }, $result);
        }
        return $result;

    }

}