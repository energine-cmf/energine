<?php
/**
 * Содержит класс XMLBuilder
 *
 * @package energine
 * @author dr.Pavka
 * @copyright Energine 2015
 */
namespace Energine\share\gears;
use Energine\share\components\IBuilder;

/**
 * XML builder
 *
 * @package energine
 * @author dr.Pavka
 */
abstract class XMLBuilder extends  Object implements IBuilder{
    /**
     * Result document.
     * @var \DOMDocument $result
     */
    public $document;
    /**
     * @var \DOMElement | \DOMNodeList
     */
    protected $result = null;

    /**
     * Get build result.
     * @return \DOMElement | \DOMNodeList
     */
    public function getResult() {
        return $this->result;
    }

    abstract protected function run();

    /**
     * Run building.
     * @return mixed
     */
    final public function build() {
        $this->document = new \DOMDocument('1.0', 'UTF-8');
        $this->result = $this->document->createElement('recordset');

        $this->run();
        return true;
    }

}