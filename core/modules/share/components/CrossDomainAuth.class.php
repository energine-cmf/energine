<?php
/**
 * Содержит класс CrossDomainAuth
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2012
 */

/**
 * Компонент для кроссдоменной авторизации
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class CrossDomainAuth extends Component {
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setProperty('authURL', 'http://'.$this->getConfigValue('site.domain').'/a.php');
        $this->setProperty('returnURL', E()->getSiteManager()->getCurrentSite()->base.'a.php');
    }
}