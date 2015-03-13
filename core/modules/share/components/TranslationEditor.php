<?php
/**
 * @file
 * TranslationEditor
 *
 * It contains the definition to:
 * @code
class TranslationEditor;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */

namespace Energine\share\components;
use Energine\share\gears\QAL, Energine\share\gears\FieldDescription, Energine\share\gears\Cache;
/**
 * Translation editor.
 *
 * @code
class TranslationEditor;
@endcode
 */
class TranslationEditor extends Grid {
    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('share_lang_tags');
        $this->setOrder(array('ltag_name' => QAL::ASC));
    }

    /**
     * @copydoc Grid::prepare
     */
    protected function prepare() {
        parent::prepare();
        if (in_array($this->getState(), array('add', 'edit'))) {
            $this->getDataDescription()->getFieldDescriptionByName('ltag_value_rtf')->setType(FieldDescription::FIELD_TYPE_TEXT);
        }
    }

    /**
     * @copydoc Grid::saveData
     */
    protected function saveData() {
        //обрезаем лишние незначащие пробелы и прочее в самих тегах и в переводах
        //в переводах - сделано на случай вывода в джаваскрипт
        $_POST[$this->getTableName()]['ltag_name'] = strtoupper(trim($_POST[$this->getTableName()]['ltag_name']));
        foreach (array_keys(E()->getLanguage()->getLanguages()) as $langID) {
            if (isset($_POST[$this->getTranslationTableName()][$langID]['ltag_value_rtf'])) {
                $_POST[$this->getTranslationTableName()][$langID]['ltag_value_rtf'] = trim($_POST[$this->getTranslationTableName()][$langID]['ltag_value_rtf']);
            }
        }
        $result = parent::saveData();

        $c = E()->getCache();
        if ($c->isEnabled()) {
            $c->dispose(Cache::TRANSLATIONS_KEY);
        }
        return $result;
    }
}