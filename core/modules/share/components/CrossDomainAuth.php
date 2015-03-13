<?php
/**
 * @file
 * CrossDomainAuth.
 *
 * It contains the definition to:
 * @code
class CrossDomainAuth;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2012
 *
 * @version 1.0.0
 */

/**
 * Component for cross-domain authentication.
 *
 * @code
class CrossDomainAuth;
@endcode
 */
class CrossDomainAuth extends Component {
    /**
     * @copydoc Component::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setProperty('authURL', 'http://'.$this->getConfigValue('site.domain').'/a.php');
        $this->setProperty('returnURL', E()->getSiteManager()->getCurrentSite()->base.'a.php');
    }
}