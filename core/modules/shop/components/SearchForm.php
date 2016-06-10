<?php

namespace Energine\shop\components;

use Energine\share\components\DataSet;
use Energine\share\gears\DataDescription;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\Field;
use Energine\share\gears\Data;
use Energine\share\gears\SimpleBuilder;

class SearchForm extends DataSet {

    protected $keyword = '';

    const KEYWORD_FIELD_NAME = 'keyword';

    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $this->setAction('search/', true);
        $this->setBuilder(new SimpleBuilder());
    }

    protected function main() {
        $this->setKeyword(isset($_REQUEST[self::KEYWORD_FIELD_NAME]) ? $_REQUEST[self::KEYWORD_FIELD_NAME] : '');
        parent::main();
        $this->setType(self::COMPONENT_TYPE_FORM);
    }

    public function setKeyword($keyword) {
        $this->keyword = $keyword;
        return $this;
    }

    public function getKeyword() {
        return $this->keyword;
    }

    protected function loadData() {
        return [
            [self::KEYWORD_FIELD_NAME => $this->keyword]
        ];
    }

    protected function createDataDescription() {
        $dd = new DataDescription();
        $fd = new FieldDescription(self::KEYWORD_FIELD_NAME);
        $fd->setType(FieldDescription::FIELD_TYPE_TEXT);
        $dd->addFieldDescription($fd);

        return $dd;
    }

}
