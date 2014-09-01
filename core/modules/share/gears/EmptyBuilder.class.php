<?php
/**
 * @file
 * EmptyBuilder.
 *
 * It contains the definition to:
 * @code
class EmptyBuilder;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2012
 *
 * @version 1.0.0
 */

namespace Energine\share\gears;
/**
 * Empty builder.
 *
 * @code
class EmptyBuilder;
@endcode
 *
 * It actually nothing builds. this class is required for the cases when the data (recordset) are not necessary.
 * Often it's used when <tt>main state</tt> serves only to load JavaScript class, that loads data over AJAX.
 */
class EmptyBuilder implements IBuilder {
    /**
     * There is nothing to build.
     *
     * @return true
     */
    public function build() {
        return true;
    }

    /**
     * Create empty recordset, that require the linking to JavaScript.
     *
     * @return bool
     */
    public function getResult() {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $dom_recordSet = $doc->createElement('recordset');
        return $dom_recordSet;
    }
}
