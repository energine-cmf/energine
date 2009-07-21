<?php
/**
 * Содержит класс ResumeForm.
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @copyright d.pavka@gmal.com
 * @version $Id$
 */

 /**
 * Класс для отправки и хранения резюме, а также просмотра отправленных резюме
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @final
 */
final class Resume extends Grid {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
    }
}