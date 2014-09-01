<?php
/**
 * @file
 * Language.
 *
 * It contains the definition to:
 * @code
final class Language;
@endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;

/**
 * Language system control.
 *
 * @code
final class Language;
@endcode
 *
 * @final
 */
final class Language extends DBWorker{
    /**
     * Current language ID.
     * @var int $current
     */
    private $current = false;

    /**
     * Set of system defined languages.
     * @var array $languages
     */
    private $languages;

    /**
     * @copydoc DBWorker::__construct
     *
     * @throws SystemException 'ERR_NO_LANG_INFO'
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
     * Get current language ID.
	 *
	 * @return int
	 */
    public function getCurrent() {
        return $this->current;
    }

    //todo VZ: This never returns 'false', then what is the reason to return true?
    /**
     * Set current language ID.
     *
     * @param int $currentLangID Language ID.
     * @return true
     *
     * @throws SystemException 'ERR_404'
     */
    public function setCurrent($currentLangID) {
        $result = false;

        foreach ($this->languages as $langID => $langInfo) {
        	if ($langID == $currentLangID) {
        		setlocale(LC_ALL, $langInfo['lang_locale']);
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

    //todo VZ: false == 0 == "0" == NULL == array() == "" --> true: The $result is not safe. Use strict comparison.
    /**
     * Get default language ID.
     *
     * @return int
     *
     * @throws SystemException 'ERR_NO_DEFAULT_LANG'
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

    //todo VZ: Strange using of flag $useDefaultIfEmpty.
    /**
     * Get language ID by his abbreviation,
	 *
	 * @param string $abbr Language abbreviation.
     * @param boolean $useDefaultIfEmpty Use default language if the abbreviation is empty.
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

    //todo VZ: false == 0 == "0" == NULL == array() == "" --> true: The $result is not safe. Use strict comparison.
    /**
     * Get abbreviation language by his ID.
     *
     * @param int $id Language ID.
     * @return string
     *
     * @throws SystemException 'ERR_BAD_LANG_ID'
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

    //todo VZ: false == 0 == "0" == NULL == array() == "" --> true: The $result is not safe. Use strict comparison.
    /**
     * Get language name by his ID.
     *
     * @param int $id Language ID.
     * @return string
     *
     * @throws SystemException 'ERR_BAD_LANG_ID'
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
            throw new SystemException('ERR_BAD_LANG_ID', SystemException::ERR_LANG, $id);
        }
        return $result;
    }

    /**
     * Get all system defined languages.
     *
     * @return array
     */
    public function getLanguages() {
        return $this->languages;
    }

    /**
     * Check if the language ID already exist.
     *
     * @param int $id Language ID.
     * @return bool
     */
    public function isValidLangID($id) {
        return in_array($id, array_keys($this->languages));
    }

    /**
     * Check if the language abbreviation already exist.
     * @param string $abbr language abbreviation.
     * @return bool
     */
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