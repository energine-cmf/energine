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
namespace Energine\share\components;
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
    public function __construct($name,  array $params = null) {
        parent::__construct($name, $params);
        $this->setProperty('authURL', 'http://'.$this->getConfigValue('site.domain').'/a.php');
        $this->setProperty('returnURL', E()->SiteManager->getCurrentSite()->base.'a.php');
    }
}