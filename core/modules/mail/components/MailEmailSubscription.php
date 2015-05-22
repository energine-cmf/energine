<?php
/**
 * Содержит класс MailEmailSubscription
 *
 * @package energine
 * @author dr.Pavka
 * @copyright Energine 2015
 */
namespace Energine\mail\components;

use Energine\share\components\DataSet;
use Energine\share\gears\Builder;
use Energine\share\gears\Data;
use Energine\share\gears\DataDescription;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\JSONCustomBuilder;

/**
 * Email subscription form
 *
 * @package energine
 * @author dr.Pavka
 */
class MailEmailSubscription extends DataSet {
    public function __construct($name, $module, array $params = NULL) {
        parent::__construct($name, $module, $params);
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        /*@todo create smth like StateConfig - descendant of SimpleXML*/
        $this->setAction((string)$this->config->getStateConfig('subscribe')->uri_patterns->pattern);

    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'active' => true
            ]
        );
    }

    protected function main() {
        $this->setBuilder(new Builder());
        $dd = new DataDescription();
        $fd = new FieldDescription('email');
        $fd->setType(FieldDescription::FIELD_TYPE_EMAIL);
        $dd->addFieldDescription($fd);
        $this->setDataDescription($dd);
        $this->setData(new Data());

        $this->js = $this->buildJS();
        $toolbars = $this->createToolbar();
        if (!empty($toolbars)) {
            $this->addToolbar($toolbars);
        }
    }

    protected function subscribe() {
        $this->setBuilder($b = new JSONCustomBuilder());

        $b->setProperties([
            'result' => true,
            'message' => $this->translate('MSG_SUBSCRIBED')
        ]);
    }
}