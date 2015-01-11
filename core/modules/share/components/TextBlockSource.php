<?php
/**
 * @file
 * TextBlockSource
 *
 * It contains the definition to:
 * @code
class TextBlockSource;
@endcode
 *
 * @author 1m.dm
 * @copyright Energine 2007
 *
 * @version 1.0.0
 */

namespace Energine\share\components;

/**
 * Source of the text block.
 *
 * @code
class TextBlockSource;
@endcode
 */
class TextBlockSource extends DataSet {
    /**
     * @copydoc DataSet::__construct
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->addWYSIWYGTranslations();
        $this->setProperty('exttype', 'grid');
    }
}

