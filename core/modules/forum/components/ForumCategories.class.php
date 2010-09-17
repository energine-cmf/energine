<?php
/**
 * Содержит класс ForumCategories
 *
 * @package energine
 * @subpackage forum
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Список категорий форума
 *
 * @package energine
 * @subpackage forum
 * @author d.pavka@gmail.com
 */
class ForumCategories extends PageList {
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
    }

    /**
     * Значения параметров выставлены
     *
     * @return int
     * @access protected
     */

    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
            array(
                'id' => self::CURRENT_PAGE,
                'recursive' => true,
                'recordsPerPage' => false
            ));
        return $result;
    }

    protected function loadData() {
        $result = parent::loadData();
        foreach($result as $smapID => $smapInfo){
            $categoryInfo = $this->dbh->selectRequest('
                SELECT COUNT(ft.theme_id) as theme_count, SUM(comment_num) as comment_count,'.
                    ' CASE WHEN ft.comment_id IS NULL THEN ut.u_nick ELSE uc.u_nick END as nick,'.
                    ' CASE WHEN ft.comment_id IS NULL THEN ft.theme_created ELSE ftc.comment_created END as comment_created'.
                    ' FROM forum_theme ft'.
                    ' LEFT JOIN forum_theme_comment ftc ON ft.comment_id=ftc.comment_id '.
                    ' LEFT JOIN user_users uc ON uc.u_id = ftc.u_id '.
                    ' LEFT JOIN user_users ut ON ut.u_id = ft.u_id '.
                    ' WHERE smap_id = %s', $smapID);

            $result[$smapID]['ThemeCount'] = simplifyDBResult($categoryInfo, 'theme_count',true);
            $result[$smapID]['CommentCount'] = simplifyDBResult($categoryInfo, 'comment_count',true);
            $result[$smapID]['CommentCreated'] = simplifyDBResult($categoryInfo, 'comment_created',true);
            $result[$smapID]['Nick'] = simplifyDBResult($categoryInfo, 'nick',true);
        }
//inspect($result);
        return $result;
    }
}