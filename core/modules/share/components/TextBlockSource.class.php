<?php
/**
 * Класс TextBlockSource.
 *
 * @package energine
 * @subpackage share
 * @author 1m.dm
 * @copyright ColoCall 2007
 * @version $Id$
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

    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $translations = array(
				'BTN_ITALIC',
	        	'BTN_HREF',
	        	'BTN_UL',
	        	'BTN_OL',
	        	'BTN_ALIGN_LEFT',
	        	'TXT_PREVIEW',
	        	'BTN_FILE_LIBRARY',
	        	'BTN_INSERT_IMAGE',
	        	'BTN_VIEWSOURCE',
	        	'TXT_PREVIEW',
	        	'TXT_RESET',
	        	'TXT_H1',
	        	'TXT_H2',
	        	'TXT_H3',
	        	'TXT_H4',
	        	'TXT_H5',
	        	'TXT_H6',
	        	'TXT_ADDRESS',
	        	'BTN_SAVE',
	        	'BTN_BOLD',
	        	'BTN_ALIGN_CENTER',
	        	'BTN_ALIGN_RIGHT',
	        	'BTN_ALIGN_JUSTIFY',
	        	'FIELD_TEXTBLOCK_SOURCE'
	        );
        	array_walk(
	        	$translations,
	        	array($this->document, 'addTranslation')
        	);
    }
}

