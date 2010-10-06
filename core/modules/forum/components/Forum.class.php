<?php
/**
 * Содержит класс Forum
 *
 * @package energine
 * @subpackage forum
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Форум, объединяет категории и темы
 *
 * @package energine
 * @subpackage forum
 * @author d.pavka@gmail.com
 */
class Forum extends DataSet {
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
    }

    protected function createBuilder() {
        return new TreeBuilder();
    }

    protected function loadData() {
        $tree = $result = array();
        $this->extendWithForumData(null, $tree, $result);
        if(!empty($tree))
            $this->getBuilder()->setTree(TreeConverter::convert($tree, 'Id', 'Pid'));
        else{
            $this->setBuilder(new SimpleBuilder());
        }


        return $result;
    }

    private function extendWithForumData($groupID, &$tree, &$result) {
        if(is_null($groupID)){
            $groupID = $this->document->getID();
        }
                
        $categories = Sitemap::getInstance()->getChilds($groupID);

        if (!empty($categories)) {
                $categoryInfo = convertDBResult($this->dbh->selectRequest('
                SELECT smap_id, COUNT(ft.theme_id) as theme_count, SUM(comment_num) as comment_count,
                    IF(ft.comment_id, uc.u_nick, ut.u_nick) as nick,
                    IF(max(ftc.comment_created), max(ftc.comment_created), ft.theme_created) as comment_created
                FROM forum_theme ft
                    LEFT JOIN forum_theme_comment ftc USING(comment_id)
                    LEFT JOIN user_users uc ON uc.u_id = ftc.u_id
                    LEFT JOIN user_users ut ON ut.u_id = ft.u_id
                WHERE smap_id IN ('. implode(',', array_keys($categories)) . ')
                GROUP BY smap_id '), 'smap_id', true);
                foreach ($categories as $subID => $subInfo) {
                    $additionalInfo = array(
                        'ThemeCount' => 0,
                        'CommentCount' => 0,
                        'CommentCreated' => '',
                        'Nick' => ''
                    );
                    if (isset($categoryInfo[$subID])) {
                        $additionalInfo = array(
                            'ThemeCount' => $categoryInfo[$subID]['theme_count'],
                            'CommentCount' => $categoryInfo[$subID]['comment_count'],
                            'CommentCreated' => $categoryInfo[$subID]['comment_created'],
                            'Nick' => $categoryInfo[$subID]['nick']
                        );
                    }
                    $sortedSubcategories[$subID] =
                            array_merge(array('Id' => $subID), $subInfo, $additionalInfo);
                }
                if($groupID != $this->document->getID()){
                    uasort($sortedSubcategories, function($a, $b) {
                        return $a['CommentCreated'] < $b['CommentCreated'];
                    });
                    $PID = $groupID;
                }
                else {
                    $PID = null;
                }


                foreach ($sortedSubcategories as $subID => $subInfo) {
                    $tree[] = array('Id' => $subID, 'Pid' => $PID);
                    $result[] = $subInfo;
                    if(is_null($PID)){
                        $this->extendWithForumData($subID, $tree, $result);
                    }
                }
            }
    }

}