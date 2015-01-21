<?php
/**
 * @file
 * Tag
 *
 * Contains the definition to:
 * @code
class Tag;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2015
 *
 * @version 1.0.0
 */

namespace Energine\share\gears;

/**
 * Tag.php
 *
 * @code
class Tag;
 * @endcode
 */
class Tag extends Object implements IBlock
{
    private $description;

    function __construct(\SimpleXMLElement $xmlDescription)
    {
        $this->description = $xmlDescription;
    }

    /**
     * Run execution.
     * @return void
     */
    public function run(){

    }

    /**
     * Is enabled?
     * @return bool
     */
    public function enabled()
    {
        return true;
    }

    /**
     * Get current rights level of the user.
     * This is needed for running current action.
     * @return mixed
     */
    public function getCurrentStateRights()
    {
        return 0;
    }

    /**
     * Build block.
     * @return \DOMDocument
     */
    public function build()
    {
        $dom_sxe = dom_import_simplexml($this->description);
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->appendChild($doc->importNode($dom_sxe, true));

        return $doc;
    }

    /**
     * Get name.
     * @return string
     */
    public function getName()
    {
        return md5((string)$this->description).'_name';
    }

}  