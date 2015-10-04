<?php
/**
 * Содержит класс SiteProperties
 *
 * @package energine
 * @subpackage trku
 * @author dr.Pavka
 * @copyright Energine 2012
 */

/**
 * Информация о текущем сайте.
 *
 * @package energine
 * @author dr.Pavka
 */
namespace Energine\share\components;

class SiteProperties extends Component
{
    protected function defineParams()
    {
        return array_merge(
            parent::defineParams(),
            array(
                'var' => false
            )
        );
    }

    public function build()
    {
        $result = parent::build();
        try {
            if (!$this->getParam('var')) {
                throw new \InvalidArgumentException();
            }
            $code = E()->getSiteManager()->getCurrentSite()->{$this->getParam('var')};
            if (!$code) {
                throw new \InvalidArgumentException();
            }
            $result->documentElement->appendChild(new \DOMText($code));
        } catch (\InvalidArgumentException $e) {
            $result = false;
        }
        return $result;
    }
}