<?php
/**
 * @file
 * Sitemap.
 *
 * It contains the definition to:
 * @code
final class Sitemap;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 */

namespace Energine\share\gears;
/**
 * Site map.
 *
 * It contain the methods for work with site structure.
 *
 * @code
final class Sitemap;
 * @endcode
 *
 * @attention This is singleton class.
 * @final
 */
final class Sitemap extends DBWorker {
    /**
     * Class exemplar that works with tree structures.
     * @var TreeNodeList $tree
     */
    private $tree;

    /**
     * Information about sections where the user can access.
     * @var array $info
     */
    private $info = array();

    /**
     * Default page ID.
     * This variable was created to minimize the using of requests.
     * @var int $defaultID
     */
    private $defaultID = false;

    /**
     * Default Meta-Keywords.
     * This is used for all pages that haven't custom Meta-keyword.
     * This variable was created to minimize the using of requests.
     *
     * @var string $defaultMetaKeywords
     */
    private $defaultMetaKeywords;

    /**
     * Default Meta-Description.
     * @var string $defaultMetaDescription
     *
     * @see Sitemap::defaultMetaKeywords
     */
    private $defaultMetaDescription;
    /**
     * Default Meta-Robots.
     * @var string $defaultMetaRobots
     */
    private $defaultMetaRobots;

    /**
     * Current language ID.
     * @var int $langID
     */
    private $langID;

    /**
     * Cache of access levels.
     * @var array $cacheAccessLevels
     */
    private $cacheAccessLevels = array();

    /**
     * Current site ID.
     * @var int $siteID
     */
    private $siteID;

    /**
     * @param int $siteID Site ID.
     *
     * @throws SystemException 'ERR_NO_TRANSLATION'
     * @throws SystemException 'ERR_404'
     */
    public function __construct($siteID) {
        parent::__construct();
        $this->siteID = $siteID;
        $this->langID = E()->getLanguage()->getCurrent();
        $userGroups = array_keys(E()->UserGroup->asArray());

        //Загружаем идентификаторы для последующего формирования древовидной стркутуры

        $res = $this->dbh->select(
            'SELECT s.smap_id, s.smap_pid FROM share_sitemap s ' .
            'LEFT JOIN share_sitemap_translation st ON st.smap_id = s.smap_id ' .
            'WHERE st.smap_is_disabled = 0 AND s.site_id = %s AND st.lang_id = %s ' .
            'AND s.smap_id IN( ' .
            ' SELECT smap_id ' .
            ' FROM share_access_level ' .
            ' WHERE group_id IN (' . implode(',', E()->getAUser()->getGroups()) . ')) ' .
            'ORDER BY smap_order_num',
            $this->siteID,
            $this->langID
        );
        //@todo Нужно бы накладывать ограничение в подзапросе на сайт, не факт правда что это увеличит быстродействие

        /*
        SELECT s.smap_id, s.smap_pid FROM share_sitemap s LEFT JOIN share_sitemap_translation st ON st.smap_id = s.smap_id WHERE st.smap_is_disabled = 0 AND s.site_id = '6' AND st.lang_id = '1' AND s.smap_id IN(
        SELECT a.smap_id
        FROM share_access_level  a
        LEFT JOIN share_sitemap s USING(smap_id)
        WHERE group_id IN (1) AND s.site_id=6
        ) ORDER BY smap_order_num
        */

        if ($res === true) {
            return;
            throw new SystemException('ERR_NO_TRANSLATION', SystemException::ERR_CRITICAL, $this->dbh->getLastRequest());
        }
        //Кешируем уровни доступа к страницам сайта
        //Формируем матрицу вида
        //[идентификатор раздела][идентификатор роли] = идентификатор уровня доступа
        $rightsMatrix = $this->dbh->select('share_access_level', true, array('smap_id' => array_map(create_function('$a', 'return $a["smap_id"];'), $res)));

        if (!is_array($rightsMatrix)) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }

        foreach ($rightsMatrix as $data) {
            foreach ($userGroups as $groupID) {
                //todo проверить вариант с пересечением array_diff
                if (!isset($this->cacheAccessLevels[$data['smap_id']][$groupID]))
                    $this->cacheAccessLevels[$data['smap_id']][$groupID] = ACCESS_NONE;
            }
            $this->cacheAccessLevels[$data['smap_id']][$data['group_id']] = (int)$data['right_id'];
        }

        //Загружаем перечень идентификаторов в объект дерева
        $this->tree = TreeConverter::convert($res, 'smap_id', 'smap_pid');

        $res = $this->dbh->select('
		  SELECT s.smap_id,ss.site_meta_keywords, ss.site_meta_description, sss.site_meta_robots 
            FROM share_sitemap s
            LEFT JOIN share_sites_translation ss ON ss.site_id=s.site_id
            LEFT JOIN share_sites sss ON sss.site_id=s.site_id 
            WHERE ss.site_id = %s AND s.smap_pid IS NULL and ss.lang_id = %s
		', $this->siteID, $this->langID);
        list($res) = $res;
        $this->defaultID = $res['smap_id'];
        $this->defaultMetaKeywords = $res['site_meta_keywords'];
        $this->defaultMetaDescription = $res['site_meta_description'];
        $this->defaultMetaRobots = $res['site_meta_robots'];

        $this->getSitemapData(array_keys($this->tree->asList()));
    }

    /**
     * Get site ID by page ID.
     *
     * @param int $pageID Page ID.
     * @return mixed
     */
    public static function getSiteID($pageID) {
        return simplifyDBResult(
            E()->getDB()->select('share_sitemap', 'site_id', array('smap_id' => (int)$pageID)),
            'site_id',
            true
        );
    }

    /**
     * Get information about sections.
     *
     * @param int|array $id Section ID or array of IDs.
     * @return array
     */
    private function getSitemapData($id) {
        if (!is_array($id)) {
            $id = array($id);
        }

        if ($diff = array_diff($id, array_keys($this->info))) {
            $ids = implode(',', $diff);
            $result = convertDBResult(
                $this->dbh->select(
                    'SELECT s.smap_id,
	                    s.smap_pid,
	                    s.site_id as site,
	                    s.smap_segment as Segment,
	                    s.smap_meta_robots,
	                    st.smap_name,
	                    st.smap_title,
	                    smap_redirect_url,
	                    smap_description_rtf,
	                    smap_html_title,
	                    smap_meta_keywords,
	                    smap_meta_description
	                    FROM share_sitemap s
	                    LEFT JOIN share_sitemap_translation st ON s.smap_id = st.smap_id
	                    WHERE st.lang_id = %s AND s.site_id = %s AND s.smap_id IN (' .
                    $ids . ')',
                    $this->langID,
                    $this->siteID
                ),
                'smap_id', true);
            if (is_array($result)) {
                $result = array_map(array($this, 'preparePageInfo'), $result);
                $this->info += $result;
            }

        } else {
            $result = array();
            foreach ($this->info as $key => $value) {
                if (in_array($key, $diff))
                    $result[$key] = $value;

            }
        }

        return $result;
    }


    /**
     * Prepare page information.
     *
     * This is internal method for transforming information about document.
     * It set all keys to @c camelNotation and change template ID for link.
     *
     * @param array $current Current page.
     * @return array
     */
    private function preparePageInfo($current) {
        //inspect($current);
        //здесь что то лишнее
        //@todo А нужно ли вообще обрабатывать все разделы?
        $result = convertFieldNames($current, 'smap');
        if (is_null($result['MetaKeywords'])) $result['MetaKeywords'] = $this->defaultMetaKeywords;
        if (is_null($result['MetaDescription'])) $result['MetaDescription'] = $this->defaultMetaDescription;
        if (is_null($result['MetaRobots']) || empty($result['MetaRobots'])) $result['MetaRobots'] = $this->defaultMetaRobots;
        //if($result['RedirectUrl']) $result['RedirectUrl'] = (URI::validate($result['RedirectUrl']))?$result['RedirectUrl']:E()->getSiteManager()->getCurrentSite()->base.$result['RedirectUrl'];

        return $result;
    }


    /**
     * Get default page ID.
     *
     * @return int
     */
    public function getDefault() {
        return $this->defaultID;
    }

    /**
     * Get URL section by page ID.
     *
     * @param int $smapID Page ID.
     * @return string
     */
    public function getURLByID($smapID) {
        $result = array();
        $node = $this->tree->getNodeById($smapID);
        if (!is_null($node)) {
            $parents = array_reverse(array_keys($node->getParents()->asList(false)));
            foreach ($parents as $id) {
                if (isset($this->info[$id]) && $this->info[$id]['Segment']) {
                    $result[] = $this->info[$id]['Segment'];
                } else {
                    $res = $this->getDocumentInfo($id, false);
                    $result[] = $res['Segment'];
                }
            }
        }

        $currentSegment = $this->getDocumentInfo($smapID);
        $currentSegment = $currentSegment['Segment'];
        $result[] = $currentSegment;
        $result = array_filter($result);
        if (!empty($result))
            $result = implode('/', $result) . '/';
        else {
            $result = '';
        }
        return $result;
    }

    /**
     * Get page ID by his URL.
     *
     * @param array $segments URL.
     * @return int
     */
    public function getIDByURI(array $segments) {
        $request = E()->getRequest();
        $id = $this->getDefault();
        if (empty($segments)) {
            return $id;
        }

        foreach ($segments as $key => $segment) {
            $found = false;
            foreach ($this->info as $pageID => $pageInfo) {
                if (($segment == $pageInfo['Segment']) && ($id == $pageInfo['Pid'])) {
                    $id = $pageID;
                    $request->setPathOffset($key + 1);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                break;
            }
        }
        //return ($id != $this->getDefault())?$id:false;
        return $id;
    }

    /**
     * Get document rights.
     * It also defines the set of rights for a page.
     *
     * @param int $docID Document ID.
     * @param mixed $groups Group/set of groups. If this is not defined the group of current user will be used.
     * @return int
     */
    public function getDocumentRights($docID, $groups = false) {
        if (!$groups) {
            $groups = E()->getAUser()->getGroups();
        } elseif (!is_array($groups)) {
            $groups = array($groups);
        }

        $groups = array_combine($groups, $groups);

        $result = 0;
        if (isset($this->cacheAccessLevels[$docID])) {
            $result = max(array_intersect_key($this->cacheAccessLevels[$docID], $groups));
        }

        return $result;
    }

    /**
     * Get all child sections.
     *
     * @param int $smapID Section ID.
     * @param bool $returnAsTreeNodeList Return all as TreeNodeList?
     * @return array
     */
    public function getChilds($smapID, $returnAsTreeNodeList = false) {
        $result = array();
        if ($node = $this->tree->getNodeById($smapID)) {
            if (!$returnAsTreeNodeList) {
                $result = $this->buildPagesMap(array_keys($node->getChildren()->asList(false)));
            } else {
                $result = $node->getChildren();
            }
        }
        return $result;
    }

    /**
     * Get all descendants.
     *
     * @param int $smapID Section ID.
     * @return array
     */
    public function getDescendants($smapID) {
        $result = array();
        if ($node = $this->tree->getNodeById($smapID)) {
            $result = $this->buildPagesMap(array_keys($node->getChildren()->asList()));
        }
        return $result;
    }

    /**
     * Get parent.
     *
     * @param int $smapID Section ID.
     * @return int
     */
    public function getParent($smapID) {
        $node = $this->tree->getNodeById($smapID);
        $result = false;
        if (!is_null($node)) {
            $result = key($node->getParents()->asList(false));
        }

        return $result;
    }

    /**
     * Get parents.
     *
     * @param int $smapID Section ID.
     * @return array
     */
    public function getParents($smapID) {
        $node = $this->tree->getNodeById($smapID);
        $result = array();
        if (!is_null($node)) {
            $result = $this->buildPagesMap(array_reverse(array_keys($node->getParents()->asList(false))));
        }
        return $result;
    }

    /**
     * Build page map.
     * The returned array looks as follows:
     * @code array('$section_id'=>array()) @endcode
     *
     * @param array $ids Section IDs.
     * @return array
     */
    private function buildPagesMap($ids) {
        $result = array();
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $info = $this->getDocumentInfo($id);
                $info['Segment'] = $this->getURLByID($id);
                $result[$id] = $info;
            }
        }

        return $result;
    }

    /**
     * Get document information.
     *
     * @param int $id Section ID
     * @return array
     */
    public function getDocumentInfo($id) {
        // Ищем документ с нужным идентификатором в $this->info
        if (isset($this->info[$id]))
            $result = $this->info[$id];
        else {
            $result = $this->getSitemapData($id);
            $result = $result[$id];
        }
        return $result;
    }


    /**
     * Get Tree object.
     *
     * @return TreeNodeList
     */
    public function getTree() {
        return $this->tree;
    }

    /**
     * Get the whole information about sections in unstructured view.
     *
     * @return array
     */
    public function getInfo() {
        return $this->info;
    }
}

