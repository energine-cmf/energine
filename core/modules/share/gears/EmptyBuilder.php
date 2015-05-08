<?php
/**
 * @file
 * EmptyBuilder.
 *
 * It contains the definition to:
 * @code
class EmptyBuilder;
 * @endcode
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
 * @endcode
 *
 * It actually nothing builds. this class is required for the cases when the data (recordset) are not necessary.
 * Often it's used when <tt>main state</tt> serves only to load JavaScript class, that loads data over AJAX.
 */
class EmptyBuilder extends XMLBuilder {
    function run() {
        //empty body - for this type of builder we dont't need nothing
    }
}
