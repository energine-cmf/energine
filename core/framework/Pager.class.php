<?php

/**
 * Класс Pager
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/framework/Object.class.php');
//require_once('core/modules/share/components/Toolbar.class.php');

/**
 * Список страниц для навигации при постраничном выводе.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @final
 */
final class Pager extends Object {

    /**
     * Количество отображаемых номеров страниц с каждой стороны от текущей
     *
     * Пример для VISIBLE_PAGES_COUNT = 3
     * 1 2 ... 5  6  7 _8_ 9 10 11 ... 455 456
     */
    const VISIBLE_PAGES_COUNT = 2;

    /**
     * @access private
     * @var int количество записей на странице
     */
    private $recordsPerPage;

    /**
     * @access private
     * @var int количество страниц
     */
    private $numPages = 0;

    /**
     * @access private
     * @var int общее количество записей
     */
    private $recordsCount;

    /**
     * @access private
     * @var int номер текущей страницы
     */
    private $currentPage;

    /**
     * @access private
     * @var array дополнительные свойства списка страниц
     */
    private $properties = array();

    /**
     * Конструктор класса.
     *
     * @access public
     * @param int $recordsPerPage
     * @param int $currentPage
     * @return void
     */
    public function __construct($recordsPerPage = 0, $currentPage = 1) {
        parent::__construct();

        $this->setRecordsPerPage($recordsPerPage);
        $this->setCurrentPage($currentPage);
    }

    /**
     * Устанавливает количество записей на странице.
     *
     * @access public
     * @param int $recordsPerPage
     * @return void
     */
    public function setRecordsPerPage($recordsPerPage) {
        $recordsPerPage = intval($recordsPerPage);
        if ($recordsPerPage < 1) {
        	throw new SystemException('ERR_DEV_BAD_RECORDS_PER_PAGE', SystemException::ERR_DEVELOPER);
        }
        $this->recordsPerPage = $recordsPerPage;
    }

    /**
     * Возвращает количество записей на странице.
     *
     * @access public
     * @return int
     */
    public function getRecordsPerPage() {
        return $this->recordsPerPage;
    }

    /**
     * Возвращает количество страниц.
     *
     * @access public
     * @return int
     */
    public function getNumPages() {
        return $this->numPages;
    }

    /**
     * Устанавливает номер текущей страницы.
     *
     * @access public
     * @param int $currentPage
     */
    public function setCurrentPage($currentPage) {
        $currentPage = intval($currentPage);
        if ($currentPage < 1) {
            throw new SystemException('ERR_DEV_BAD_PAGE_NUMBER', SystemException::ERR_DEVELOPER);
        }
        $this->currentPage = $currentPage;
    }

    /**
     * Устанавливает общее количество записей.
     *
     * @access public
     * @param int $count
     * @return void
     */
    public function setRecordsCount($count) {
        $recordsCount = intval($count);
        if ($recordsCount < 0) {
            throw new SystemException('ERR_DEV_BAD_RECORDS_COUNT', SystemException::ERR_DEVELOPER);
        }
        $this->recordsCount = $recordsCount;
        // пересчитываем количество страниц
        $this->numPages = ceil($this->recordsCount / $this->recordsPerPage);
    }

    /**
     * Возвращает общее количество записей.
     *
     * @access public
     * @return int
     */
    public function getRecordsCount() {
        return $this->recordsCount;
    }

    /**
     * Возвращает номер текущей страницы.
     *
     * @access public
     * @return int
     */
    public function getCurrentPage() {
        return $this->currentPage;
    }

    /**
     * Устанавливает свойство списка страниц.
     *
     * @access public
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setProperty($name, $value) {
        $this->properties[$name] = $value;
    }

    /**
     * Возвращает лимит выборки для SELECT-запроса.
     *
     * @access public
     * @return array
     * @see QAL::select()
     */
    public function getLimit() {
        return array(($this->getCurrentPage() - 1) * $this->getRecordsPerPage(), $this->getRecordsPerPage());
    }

    /**
     * Строит документ списка страниц и возвращает ссылку на корневой узел документа.
     *
     * @access public
     * @return DOMNode
     */
    public function build() {

        $pager = new Toolbar('pager');
        if (!empty($_GET)) {
        	$this->setProperty('get_string', http_build_query($_GET));
        }
        if (!empty($this->properties)) {
            foreach ($this->properties as $propName => $propValue) {
                $pager->setProperty($propName, $propValue);
            }
        }

        $pager->setProperty('from', DBWorker::_translate('TXT_FROM'));
        $pager->setProperty('to', DBWorker::_translate('TXT_TO'));


        $startPage = (($page = $this->currentPage - self::VISIBLE_PAGES_COUNT) < 1)?1:$page;
        $endPage  = (($page = $this->currentPage + self::VISIBLE_PAGES_COUNT) > $this->numPages)?$this->numPages:$page;

        if ($startPage > 1) {
            $control = new Link("page1", 1, false, 1);
            $pager->attachControl($control);
        }
        if ($startPage > 2) {
            $control = new Link("page2", 2, false, 2);
            $pager->attachControl($control);
            if ($startPage != 2 + 1) {
            	$control->setAttribute('start_break', 'start_break');
            }
        }

        for ($i = $startPage; $i <= $endPage; $i++) {
            $isCurrent = ($i == $this->currentPage);

            $control = new Link("page$i", $i, false, $i);
            if ($isCurrent) {
            	$control->disable();
            }
            $pager->attachControl($control);
        }

        if ($endPage < $this->numPages - 1) {
            $control = new Link("page".($this->numPages-1), $this->numPages-1, false, $this->numPages-1);
            $pager->attachControl($control);
            if ($endPage != $this->numPages - 2) {
            	$control->setAttribute('end_break', 'end_break');
            }
        }
        if ($endPage < $this->numPages) {
        	$control = new Link("page$this->numPages", $this->numPages, false, $this->numPages);
            $pager->attachControl($control);
        }

        return $pager->build();
    }
}
