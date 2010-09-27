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
     * Значения параметров выставлены
     *
     * @return array
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
        if (is_array($result) && !empty($result)) {
            $smapIDs = array_keys($result);
            $categoryInfo = convertDBResult($this->dbh->selectRequest('
                SELECT smap_id, COUNT(ft.theme_id) as theme_count, SUM(comment_num) as comment_count,' .
                    ' CASE WHEN ft.comment_id IS NULL THEN ut.u_nick ELSE uc.u_nick END as nick,' .
                    ' CASE WHEN ft.comment_id IS NULL THEN ft.theme_created ELSE ftc.comment_created END as comment_created' .
                    ' FROM forum_theme ft' .
                    ' LEFT JOIN forum_theme_comment ftc USING(comment_id) ' .
                    ' LEFT JOIN user_users uc ON uc.u_id = ftc.u_id ' .
                    ' LEFT JOIN user_users ut ON ut.u_id = ft.u_id ' .
                    ' WHERE smap_id IN (' . implode(',', $smapIDs) . ')' .
                    ' GROUP BY smap_id '.
                    ' ORDER BY comment_created DESC'), 'smap_id', true);
            foreach($smapIDs as $smapID){
                if(!isset($categoryInfo[$smapID])){
                    $categoryInfo[$smapID]['theme_count'] = $categoryInfo[$smapID]['comment_count'] = 0;
                    $categoryInfo[$smapID]['comment_created'] = $categoryInfo[$smapID]['nick'] = '';
                }
                $result[$smapID]['ThemeCount'] = $categoryInfo[$smapID]['theme_count'];
                $result[$smapID]['CommentCount'] = $categoryInfo[$smapID]['comment_count'];
                $result[$smapID]['CommentCreated'] = $categoryInfo[$smapID]['comment_created'];
                $result[$smapID]['Nick'] = $categoryInfo[$smapID]['nick'];
            }
        }
        return $result;
    }
}