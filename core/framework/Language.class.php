<?php
/**
 * Класс Language.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
 * @version $Id$
 */


/**
 * Управляет языками системы.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @final
 */
final class Language extends Singleton{

    /**
     * @access public
     * @var int текущий язык системы
     */
    private $current = false;

    /**
     * @access private
     * @var array набор языков, определённых в системе
     */
    private $languages;

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();

        // получаем все языки, определённые в системе
        $res = $this->dbh->select('share_languages', true, null, array('lang_order_num'=>QAL::ASC));
        if (!is_array($res)) {
            throw new SystemException('ERR_NO_LANG_INFO', SystemException::ERR_CRITICAL, $this->dbh->getLastRequest());
        }
        // формируем набор языков вида array(langID => langInfo)
        foreach ($res as $langInfo) {
            $this->languages[$langInfo['lang_id']] = $langInfo;
            unset($this->languages[$langInfo['lang_id']]['lang_id']);
        }
    }

    /**
	 * Возвращает идентификатор текущего языка.
	 *
	 * @access public
	 * @return int
	 */
    public function getCurrent() {
        return $this->current;
    }

    /**
	 * Устанавливает идентификатор текущнго языка.
	 *
	 * @access public
	 * @param int $currentLangID
	 * @return void
	 */
    public function setCurrent($currentLangID) {
        $result = false;

        foreach ($this->languages as $langID => $langInfo) {
        	if ($langID == $currentLangID) {
        	    $result = true;
        		break;
        	}
        }

        if (!$result) {
            throw new SystemException('ERR_404', SystemException::ERR_LANG, $currentLangID);
        }

        $this->current = $currentLangID;

        return $result;
    }

    /**
	 * Возвращает идентификатор языка по-умолчанию.
	 *
	 * @access public
	 * @return int
	 */
    public function getDefault() {
        $result = false;
        foreach ($this->languages as $langID => $langInfo) {
            if ($langInfo['lang_default'] == 1) {
                $result = $langID;
                break;
            }
        }
        if ($result == false) {
            throw new SystemException('ERR_NO_DEFAULT_LANG', SystemException::ERR_CRITICAL );
        }
        return $result;
    }

    /**
	 * Возвращает идентификатор языка по аббревиатуре азыка.
	 *
	 * @access public
	 * @param string $abbr аббревиатура языка
	 * @return int
	 */
    public function getIDByAbbr($abbr, $useDefaultIfEmpty = false) {
        $result = false;
        if (empty($abbr) && $useDefaultIfEmpty) {
            return $this->getDefault();
        }
        foreach ($this->languages as $langID => $langInfo) {
            if ($langInfo['lang_abbr'] == $abbr) {
                $result = $langID;
                break;
            }
        }
        return $result;

    }

    /**
	 * Возвращает аббревиатуру языка по идентификатору языка.
	 *
	 * @access public
	 * @param int $id идентификатор языка
	 * @return string
	 */
    public function getAbbrByID($id) {
        $result = false;
        foreach ($this->languages as $langID => $langInfo) {
            if ($langID == $id) {
                $result = $langInfo['lang_abbr'];
                break;
            }
        }
        if ($result == false) {
            throw new SystemException('ERR_BAD_LANG_ID', SystemException::ERR_LANG, $abbr);
        }
        return $result;
    }

    /**
	 * Возвращает название языка по идентификатору языка.
	 *
	 * @access public
	 * @param int $id
	 * @return string
	 */
    public function getNameByID($id) {
        $result = false;
        foreach ($this->languages as $langID => $langInfo) {
            if ($langID == $id) {
                $result = $langInfo['lang_name'];
                break;
            }
        }
        if ($result == false) {
            throw new SystemException('ERR_BAD_LANG_ID', SystemException::ERR_LANG, $abbr);
        }
        return $result;
    }

    /**
     * Возвращает все языки, определённые в системе.
     *
     * @access public
     * @return array
     */
    public function getLanguages() {
        return $this->languages;
    }

    /**
     * Проверяет, существует ли язык с указанным идентификатором.
     *
     * @access public
     * @param int $id
     * @return bool
     */
    public function isValidLangID($id) {
        return in_array($id, array_keys($this->languages));
    }

    public function isValidLangAbbr($abbr) {
        $result = false;
        foreach ($this->languages as $langID => $langInfo) {
            if ($langInfo['lang_abbr'] == $abbr) {
                $result = true;
                break;
            }
        }
        return $result;
    }
}