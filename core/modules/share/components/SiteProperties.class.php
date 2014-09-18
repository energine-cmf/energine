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
 * @subpackage trku
 * @author dr.Pavka
 */
class SiteProperties extends Component {
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                'id' => false
            )
        );
    }

    public function build() {
        $result = parent::build();
        try {
            if (!$this->getParam('id')) {
                throw new InvalidArgumentException();
            }
            $code = E()->getSiteManager()->getCurrentSite()->{$this->getParam('id')};
            if (!$code) {
                throw new InvalidArgumentException();
            }
            $result->documentElement->appendChild(new DOMText($code));
        }
        catch(InvalidArgumentException $e){
            $result = false;
        }
        return $result;
    }
}