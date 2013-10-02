<?php
trait EventHandler {
    protected function defineParams() {
        if (!($newParams = $this->eDefineParams())) {
            $newParams = array();
        }
        return array_merge(
            parent::defineParams(),
            $newParams);
    }

    protected function loadDataDescription() {
        $this->eBeforeLoadMetaData();
        $result = parent::loadDataDescription();
        $this->eLoadMetaData($result);

        return $result;
    }

    protected function createDataDescription() {
        $this->eBeforeCreateDataDescription();
        $dataDescription = parent::createDataDescription();
        $this->eCreateDataDescription($dataDescription);

        return $dataDescription;
    }

    protected function createData() {
        $this->eBeforeCreateData();
        $data = parent::createData();
        $this->eCreateData($data);

        return $data;
    }

    protected function loadData() {
        $this->eBeforeLoadData();
        $result = parent::loadData();
        $this->eLoadData($result);

        return $result;
    }

    protected function prepare() {
        $this->eBeforePrepare();
        parent::prepare();
        $this->ePrepare();
    }

    public function run() {
        $this->{'eBefore' . ucfirst($this->getState()) . 'State'}();
        parent::run();
        $this->{'e' . ucfirst($this->getState()) . 'State'}();
    }

    public function __call($name, $args) {
        $result = null;

        if ($name[0] == 'e') {
            if (($eventName = 'on' . ucfirst(substr($name, 1))) && method_exists($this, $eventName)) {
                $result = call_user_func_array(array($this, $eventName), $args);
            }
        } else {
            throw new SystemException('ERR_NO_METHOD', SystemException::ERR_DEVELOPER, $name);
        }

        return $result;
    }
}