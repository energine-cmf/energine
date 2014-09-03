<?php
/**
 * @file
 * Pager.
 *
 * It contains the definition to:
 * @code
final class Pager;
@endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;

/**
 * List of pages for navigation.
 *
 * @code
final class Pager;
@endcode
 *
 * @final
 */
final class Pager extends Object {
    //todo VZ: In PageList.js VISIBLE_PAGES_COUNT is always 2. For what is this?
    /**
     * Amount of visible pages by both side of current page.
     *
     * Example:
     * @code
VISIBLE_PAGES_COUNT = 3 --> 1 2 ... 5  6  7 _8_ 9 10 11 ... 455 456
@endcode
     *
     * @var int VISIBLE_PAGES_COUNT
     */
    const VISIBLE_PAGES_COUNT = 2;

    /**
     * Amount of records per page.
     * @var int $recordsPerPage
     */
    private $recordsPerPage;

    /**
     * Total amount of pages.
     * @var int $numPages
     */
    private $numPages = 0;

    /**
     * Total amount of records.
     * @var int $recordsCount
     */
    private $recordsCount;

    /**
     * Current page number.
     * @var int $currentPage
     */
    private $currentPage;

    /**
     * Additional properties.
     * @var array $properties
     */
    private $properties = array();

    /**
     * @param int $recordsPerPage Records per page.
     * @param int $currentPage Current page.
     */
    public function __construct($recordsPerPage = 0, $currentPage = 1) {
        $this->setRecordsPerPage($recordsPerPage);
        $this->setCurrentPage($currentPage);
    }

    /**
     * Set amount of records per page.
     *
     * @param int $recordsPerPage Amount of records per page.
     *
     * @throws SystemException 'ERR_DEV_BAD_RECORDS_PER_PAGE'
     */
    public function setRecordsPerPage($recordsPerPage) {
        $recordsPerPage = intval($recordsPerPage);
        if ($recordsPerPage < 1) {
            throw new SystemException('ERR_DEV_BAD_RECORDS_PER_PAGE', SystemException::ERR_DEVELOPER);
        }
        $this->recordsPerPage = $recordsPerPage;
    }

    /**
     * Get amount of records per page.
     *
     * @return int
     */
    public function getRecordsPerPage() {
        return $this->recordsPerPage;
    }

    /**
     * Get total amount of pages.
     *
     * @return int
     */
    public function getNumPages() {
        return $this->numPages;
    }

    /**
     * Set current page.
     *
     * @param int $currentPage Current page number.
     *
     * @throws SystemException 'ERR_DEV_BAD_PAGE_NUMBER'
     */
    public function setCurrentPage($currentPage) {
        $currentPage = intval($currentPage);
        if ($currentPage < 1) {
            throw new SystemException('ERR_DEV_BAD_PAGE_NUMBER', SystemException::ERR_DEVELOPER);
        }
        $this->currentPage = $currentPage;
    }

    /**
     * Set total amount of records.
     *
     * @param int $count Amount of records.
     *
     * @throws SystemException 'ERR_DEV_BAD_RECORDS_COUNT'
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
     * Get total amount of records.
     *
     * @return int
     */
    public function getRecordsCount() {
        return $this->recordsCount;
    }

    /**
     * Get current page number.
     *
     * @return int
     */
    public function getCurrentPage() {
        return $this->currentPage;
    }

    /**
     * Set property.
     *
     * @param string $name Property name.
     * @param mixed $value Property value.
     */
    public function setProperty($name, $value) {
        $this->properties[$name] = $value;
    }

    /**
     * Get fetching limit for SELECT-request.
     *
     * @return array
     *
     * @see QAL::select()
     */
    public function getLimit() {
        return array(($this->getCurrentPage() - 1) * $this->getRecordsPerPage(), $this->getRecordsPerPage());
    }

    /**
     * Build page list.
     * The root node is returned.
     *
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
        //$pager->setProperty('total', DBWorker::_translate('TXT_TOTAL'));
        $pager->setProperty('records', $this->recordsCount);
        $total = $this->numPages;
        $current = $this->currentPage;
        $visible = self::VISIBLE_PAGES_COUNT;

        $startSeparator = $endSeparator = false;

        for($i = 1; $i<= $total; $i++){
            if(
                ($i>$visible) &&
                ($i < ($current - $visible))
            ){
                if(!$startSeparator) $pager->attachControl(new Separator('sep_start'));
                $startSeparator = true;
                continue;
            }
            elseif(
                ($i> $current+$visible) &&
                ($i<= ($total - $visible))
            ){
                if(!$endSeparator) $pager->attachControl(new Separator('sep_end'));
                $endSeparator = true;
                continue;
            }
            else {
                $control = new Link("page".$i, $i, $i);
                if($i == $current) $control->disable();
                $pager->attachControl($control);
            }
        }
        return $pager->build();
    }
}
