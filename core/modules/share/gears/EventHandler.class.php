<?php
/**
 * @file
 * EventHandler.
 *
 * It contains the definition to:
 * @code
trait EventHandler;
@endcode
 */

/**
 * @class EventHandler
 * @brief Event handler.
 *
 * @code
trait EventHandler;
@endcode
 */
trait EventHandler {
    /**
     * Define parameters.
     *
     * @return array
     */
    protected function defineParams() {
        if (!($newParams = $this->eDefineParams())) {
            $newParams = array();
        }
        return array_merge(
            parent::defineParams(),
            $newParams);
    }

    /**
     * Load data description.
     *
     * @return mixed
     */
    protected function loadDataDescription() {
        $this->eBeforeLoadMetaData();
        $result = parent::loadDataDescription();
        $this->eLoadMetaData($result);

        return $result;
    }

    /**
     * Create data description.
     *
     * @return mixed
     */
    protected function createDataDescription() {
        $this->eBeforeCreateDataDescription();
        $dataDescription = parent::createDataDescription();
        $this->eCreateDataDescription($dataDescription);

        return $dataDescription;
    }

    /**
     * Create data.
     *
     * @return mixed
     */
    protected function createData() {
        $this->eBeforeCreateData();
        $data = parent::createData();
        $this->eCreateData($data);

        return $data;
    }

    /**
     * Load data.
     *
     * @return mixed
     */
    protected function loadData() {
        $this->eBeforeLoadData();
        $result = parent::loadData();
        $this->eLoadData($result);

        return $result;
    }

    /**
     * Prepare.
     */
    protected function prepare() {
        $this->eBeforePrepare();
        parent::prepare();
        $this->ePrepare();
    }

    /**
     * Run.
     */
    public function run() {
        $this->{'eBefore' . ucfirst($this->getState()) . 'State'}();
        parent::run();
        $this->{'e' . ucfirst($this->getState()) . 'State'}();
    }

    /**
     * Magic @c call method.
     *
     * @throws SystemException 'ERR_NO_METHOD'
     *
     * @param string $name Name
     * @param array $args Arguments.
     * @return mixed|null
     */
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