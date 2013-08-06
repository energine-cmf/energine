<?php
/**
 * Класс TextBlockSource.
 *
 * @package energine
 * @subpackage share
 * @author 1m.dm
 * @copyright Energine 2007
 */

//require_once('core/modules/share/components/DataSet.class.php');

/**
 * Исходный код текстового блока.
 *
 * @package energine
 * @subpackage share
 * @author 1m.dm
 */
class TextBlockSource extends DataSet {

    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->addWYSIWYGTranslations();
        $this->setProperty('exttype', 'grid');
    }
}

