<?php

/**
 * Содержит класс Sitemap
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/framework/DBWorker.class.php');
//require_once('core/framework/TreeConverter.class.php');


/**
 * Класс - синглтон
 * Содержит методы по работе со структурой сайта
 *
 * @todo проблема с конечными страницами
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @final
 */
final class Sitemap extends DBWorker {
    /**
     * @var TreeNodeList Экземпляр класса реализующего работу с древовидными структурами
     * @access private
     */
    private $tree;

    /**
     * @var Sitemap Instance объекта Sitemap
     * @access private
     * @static
     */
    private static $instance;

    /**
     * Информация о тех разделах, на которіе у юзера есть права
     * @var array
     * @access private
     */
    private $info = array();

    /**
     * Информация обо всех разделах(включая те на которые у текущего юзера нет прав)
     *
     * @var array
     * @access private
     */
    private $allInfo = array();

    /**
     * Идентификатор дефолтной страницы
     * Вынесено в переменную чтоб не дергать запрос постоянно
     *
     * @var int
     * @access private
     */
    private $defaultID = false;

    /**
     * Дефолтные meta keywords
     * Используется для всех страниц у которых не указано
     * Вынесено в отдельную переменную, чтобы не дергать каждый раз запрос
     *
     * @var string
     * @access private
     */
    private $defaultMetaKeywords;

    /**
     * Дефолтное meta description
     *
     * @var string
     * @access private
     * @see Sitemap::defaultMetaKeywords
     */
    private $defaultMetaDescription;

    /**
     * Идентификатор текущего языка
     *
     * @var int
     * @access private
     */
    private $langID;

    /**
     * Кеширование уровней доступа
     *
     * @var array
     * @access private
     */
    private $cacheAccessLevels = array();


    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct() {
        //$this->startTimer();
        parent::__construct();
        $this->langID = Language::getInstance()->getCurrent();
        $userGroups = array_keys(UserGroup::getInstance()->asArray());

        //Кешируем уровни доступа к страницам сайта
        //Формируем матрицу вида
        //[идентификатор раздела][идентификатор роли] = идентификатор уровня доступа
        foreach ($this->dbh->select('share_access_level') as $data) {
            foreach ($userGroups as $groupID) {
                //todo проверить вариант с пересечением array_diff
                if (!isset($this->cacheAccessLevels[$data['smap_id']][$groupID]))
                    $this->cacheAccessLevels[$data['smap_id']][$groupID] = ACCESS_NONE;
            }
            $this->cacheAccessLevels[$data['smap_id']][$data['group_id']] = (int)$data['right_id'];
        }

        //Загружаем идентификаторы для последующего формирования древовидной стркутуры
        //Получаем только идентификаторы разделов

        $res = $this->dbh->selectRequest('
            SELECT s.smap_id, s.smap_pid FROM share_sitemap s
            LEFT JOIN share_sitemap_translation st ON st.smap_id = s.smap_id
            WHERE st.smap_is_disabled = 0 AND st.lang_id = %s 
            ORDER BY smap_order_num
        ', $this->langID);
        if ($res === true) {
            throw new SystemException('ERR_NO_TRANSLATION', SystemException::ERR_CRITICAL);
        }


        //inspect($this->resetTimer());
        //Фильтруем перечень идентификаторов отсекая те разделы на которые нет прав
        $res = array_filter($res, array($this, 'checkPageRights'));
        //inspect($this->resetTimer());
        //Загружаем перечень идентификаторов в объект дерева
        $this->tree = TreeConverter::convert($res, 'smap_id', 'smap_pid');
        //inspect($this->resetTimer());
        //Получаем дефолтные meta заголовки
        $res = $this->dbh->select('share_sitemap_translation', array('smap_meta_keywords', 'smap_meta_description'), array('smap_id' => $this->getDefault(), 'lang_id' => $this->langID));
        list($res) = $res;
        $this->defaultMetaKeywords = $res['smap_meta_keywords'];
        $this->defaultMetaDescription = $res['smap_meta_description'];
        $this->getSitemapData(array_keys($this->tree->asList()));

    }

    /**
     * Возвращает экземпляр  объекта Sitemap
     *
     * @access public
     * @return Sitemap
     * @static
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Sitemap();
        }
        return self::$instance;
    }

    /**
     * Метод возвращающий информацию о разделах
     *
     * @param mixed идентификатор раздела или массив идентификаторов
     * @return array
     * @access private
     */

    private function getSitemapData($id) {
        if (!is_array($id)) {
        	$id = array($id);
        }
        $diff = array();
        
        if($diff = array_diff($id, array_keys($this->info))){
	        $ids = implode(',', $diff);
	        $result = convertDBResult(
	            $this->dbh->selectRequest(
	                'SELECT s.smap_id, s.smap_pid, s.tmpl_id as templateID, s.smap_segment as Segment, s.smap_is_final as isFinal, st.smap_name, smap_redirect_url, smap_description_rtf, smap_html_title, smap_meta_keywords, smap_meta_description '.
	                'FROM share_sitemap s '.
	                'LEFT JOIN share_sitemap_translation st ON s.smap_id = st.smap_id '.
	                'WHERE st.lang_id = '.$this->langID.' AND s.smap_id IN ('.$ids.')'),
	            'smap_id', true);
	        $result = array_map(array($this, 'preparePageInfo'), $result);
	        $this->info += $result; 
        }
        else{
        	$result = array();
            foreach ($this->info as $key=>$value){
            	if(in_array($key, $diff))
            	   $result[$key] = $value;
            }
        }
                
        return $result;
    }


    /**
     * Внутренний метод по преобразования информации о документе. Сводит все ключи к camel notation и для линка изменяет значение идентификатора шаблона
     *
     * @param array
     * @return array
     * @access private
     */

    private function preparePageInfo($current) {
        $result = convertFieldNames($current,'smap');
        if(is_null($result['MetaKeywords'])) $result['MetaKeywords'] = $this->defaultMetaKeywords;
        if(is_null($result['MetaDescription'])) $result['MetaDescription'] = $this->defaultMetaDescription;
        if($result['RedirectUrl']) $result['RedirectUrl'] = (URI::validate($result['RedirectUrl']))?$result['RedirectUrl']:Request::getInstance()->getBasePath().$result['RedirectUrl'];
        
        return $result;
    }


    /**
     * Возвращает идентификатор страницы по умолчанию
     *
     * @return int
     * @access public
     */

    public function getDefault() {
        if (!$this->defaultID) {
            $result = simplifyDBResult($this->dbh->select('share_sitemap', 'smap_id', array('smap_default' => true)), 'smap_id', true);
            if ($result === false) {
                throw new SystemException('ERR_DEV_NO_DEFAULT_PAGE', SystemException::ERR_CRITICAL);
            }

            $this->defaultID = $result;
        }
        return $this->defaultID;
    }

    /**
     * Возвращает часть строки УРЛ по идентификатору
     *
     * @todo Ошибка с вычислением URL для конечных разделов
     *
     * @param int
     * @return string
     * @access public
     */

    public function getURLByID($smapID) {
        $result = array();
        $node = $this->tree->getNodeById($smapID);
        if (!is_null($node)) {
            $parents = array_reverse(array_keys($node->getParents()->asList(false)));
            foreach ($parents as $id) {
                if (isset($this->info[$id])) {
                    $result[] = $this->info[$id]['Segment'];
                }
                else {
                	$res = $this->getDocumentInfo($id, false);
                	$result[] = $res['Segment'];
                }
            }
        }

        $currentSegment = $this->getDocumentInfo($smapID);
        $currentSegment = $currentSegment['Segment'];
        $result[] = $currentSegment;
        $result = implode('/', $result).'/';
        return $result;
    }

    /**
     * Возвращает идентификатор страницы по его URL
     *
     * @param array
     * @return int
     * @access public
     */

    public function getIDByURI(array $segments, $useDefaultIfEmpty = false) {
        $id = null;
        $i = 1;
        $request = Request::getInstance();

        if (empty($segments) && $useDefaultIfEmpty) {
            return $this->getDefault();
        }

        foreach ($segments as $segment) {
            $res = $this->dbh->select('share_sitemap', array('smap_id'), array('smap_segment' => $segment, 'smap_pid' => $id));
            if (!is_array($res)) {
                break;
            }

            $request->setPathOffset($i);
            list($res) = $res;
            $id = $res['smap_id'];
            $i++;
        }

        return $id;
    }

    /**
     * Определение прав набора групп на страницу
     *
     * @param int идентификатор документа
     * @param mixed группа/набор групп, если не указан, берется группа/группы текущего пользователя
     * @return int
     * @access public
     */
    public function getDocumentRights($docID, $groups = false) {
        if (!$groups) {
            $groups = AuthUser::getInstance()->getGroups();
        }
        elseif (!is_array($groups)) {
            $groups = array($groups);
        }

        $groups = array_combine($groups, $groups);

        return max(array_intersect_key($this->cacheAccessLevels[$docID], $groups));
    }

    /**
     * Возвращает меню первого уровня
     *
     * @return array
     * @access public
     */

    public function getMainLevel() {
        return $this->buildPagesMap(array_keys($this->tree->asList(false)));
    }


    /**
     * Возвращает все дочерние разделы
     *
     * @param int идентификатор раздела
     * @return array
     * @access public
     */

    public function getChilds($smapID) {
        $result = array();
        if ($node = $this->tree->getNodeById($smapID)) {
            $result = $this->buildPagesMap(array_keys($node->getChildren()->asList(false)));
        }
        return $result;
    }

    /**
     * Возвращает родителя
     *
     * @return int
     * @access public
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
     * Возвращает массив родителей
     *
     * @param int Идентфикатор раздела
     * @return array
     * @access public
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
     * По переданному массиву идентификаторов разделов и массиву перечня полей формирует  cтруктуру array('$идентификатор_раздела'=>array())
     *
     * @param array идентификаторы разделов
     * @return array
     * @access private
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
     * Возвращает информацию о документе
     * Ищем документ с нужным идентификатором в $this->info
     *
     * @param int Идентификатор раздела
     * @return array
     * @access public
     */

    public function getDocumentInfo($id) {
      	$result = $this->info[$id];
        return $result;
    }


    /**
     * Возвращает объект Tree
     *
     * @return TreeNodeList
     * @access public
     */

    public function getTree() {
        return $this->tree;
    }

    /**
     * Возвращает всю информацию о раздеах в не структурированном виде
     *
     * @return array
     * @access public
     */

    public function getInfo() {
        return $this->info;
    }

    /**
     * Внутренний метод для фильтрации разделов, на которые нет прав
     * Вызывется как callback для array_filter
     *
     * @param array
     * @return boolean
     * @access private
     */

    private function checkPageRights($smapInfo) {
        return ($this->getDocumentRights($smapInfo['smap_id']) != ACCESS_NONE);
    }
}

