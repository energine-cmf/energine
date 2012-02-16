<?php
/**
 * Содержит класс FileRepository
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2012
 */

/**
 * Файловый репозиторий
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class FileRepository extends Grid {
    const IMAGE_CACHE = 'uploads/resizer/';
    //const TYPE_FOLDER = 'folder';
    //Фейковый тип для перехода на уровень выше
    const TYPE_FOLDER_UP = 'folderup';

    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('share_uploads_copy');
        $this->setFilter(array('upl_is_active' => 1));
        $this->setOrder(array('upl_childs_count' => QAL::DESC));

    }

    protected function edit() {
        $sp = $this->getStateParams();
        $uplID = $sp[0];
        if (simplifyDBResult(
            $this->dbh->select(
                $this->getTableName(),
                'upl_internal_type',
                array('upl_id' => $uplID)
            ),
            'upl_internal_type',
            true) == FileRepoInfo::META_TYPE_FOLDER
        ) {
            $this->editDir($uplID);
        }
        else {
            parent::edit();
        }
    }


    protected function editDir($uplID) {

        $this->setFilter(array('upl_id' => $uplID));
        $this->setType(self::COMPONENT_TYPE_FORM_ALTER);
        $this->setBuilder($this->createBuilder());
        $dd = new DataDescription();

        $f = new FieldDescription('upl_id');
        $f->setProperty('tableName', $this->getTableName());
        $f->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        $f->setProperty('key', true);
        $dd->addFieldDescription($f);

        $f = new FieldDescription('upl_pid');
        $f->setProperty('tableName', $this->getTableName());
        $f->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        $dd->addFieldDescription($f);

        $f = new FieldDescription('upl_title');
        $f->setProperty('tableName', $this->getTableName());
        $f->setType(FieldDescription::FIELD_TYPE_STRING);
        $dd->addFieldDescription($f);

        $this->setDataDescription($dd);
        $this->setData($this->createData());
        $toolbars = $this->createToolbar();
        if (!empty($toolbars)) {
            $this->addToolbar($toolbars);
        }
        $this->js = $this->buildJS();
        $this->setAction('save-dir/');
    }

    protected function addDir() {
        $sp = $this->getStateParams(true);
        if (isset($sp['pid'])) {
            $uplPID = (int)$sp['pid'];
        }
        else {
            $uplPID = '';
        }

        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->setBuilder($this->createBuilder());
        $this->setDataDescription($this->createDataDescription());
        $this->setData(new Data());
        $f = new Field('upl_pid');
        $f->setData($uplPID);
        $this->getData()->addField($f);

        $toolbars = $this->createToolbar();
        if (!empty($toolbars)) {
            $this->addToolbar($toolbars);
        }
        $this->js = $this->buildJS();
        $this->setAction('save-dir/');
    }

    protected function createTemporaryFile() {

        $b = new JSONCustomBuilder();
        $this->setBuilder($b);

        try {
            if (!isset($_POST['data']) || !isset($_POST['name'])) {
                throw new SystemException('ERR_BAD_DATA');
            }
            $fileData = self::cleanFileData($_POST['data']);
            if (!file_put_contents(($result = FileObject::getTmpFilePath($_POST['name'])), $fileData)) {
                throw new SystemException('ERR_CREATE_FILE');
            }
            $b->setProperties(array(
                'data' => $result,
                'result' => true
            ));
        }
        catch (SystemException $e) {
            stop($e->getMessage());
            $b->setProperties(array(
                'data' => false,
                'result' => false,
                'errors' => array(
                    $e->getMessage()
                )
            ));
        }


    }

    protected function saveDir() {
        $transactionStarted = $this->dbh->beginTransaction();
        try {
            if (!isset($_POST[$this->getTableName()]) || !isset($_POST[$this->getTableName()][$this->getPK()]) || !isset($_POST[$this->getTableName()]['upl_title']) || !isset($_POST[$this->getTableName()]['upl_pid'])) {
                throw new SystemException('ERR_NO_DATA');
            }
            $data = $_POST[$this->getTableName()];
            if (!$data['upl_pid']) {
                throw new SystemException('ERR_BAD_PID');
            }
            $mode = (empty($data[$this->getPK()])) ? QAL::INSERT : QAL::UPDATE;
            if ($mode == QAL::INSERT) {

                $parentData = $this->dbh->select($this->getTableName(), array('upl_path'), array('upl_id' => $data['upl_pid']));
                if (empty($parentData)) {
                    throw new SystemException('ERR_BAD_PID');
                }
                list($parentData) = $parentData;

                unset($data[$this->getPK()]);
                $data['upl_name'] = $data['upl_filename'] = Translit::asURLSegment($data['upl_title']);
                $data['upl_mime_type'] = 'unknown/mime-type';
                $data['upl_internal_type'] = FileRepoInfo::META_TYPE_FOLDER;
                $data['upl_childs_count'] = -1;
                $data['upl_path'] = $parentData['upl_path'] . '/' . $data['upl_filename'] . '/';
                $where = false;
                mkdir($data['upl_path']);
            }
            else {
                $where = array('upl_id' => $data['upl_id']);
                /*$currentUplPath = simplifyDBResult($this->dbh->select($this->getTableName(), array('upl_path'), array('upl_id' => $data['upl_id'])), 'upl_path', true);
                $data['upl_name'] = $data['upl_filename'] = Translit::asURLSegment($data['upl_title']);
                if($currentUplPath != $data['upl_path']){
                    rename($currentUplPath, $data['upl_path']);
                }*/
            }
            $result = $this->dbh->modify($mode, $this->getTableName(), $data, $where);

            $transactionStarted = !($this->dbh->commit());

            $b = new JSONCustomBuilder();
            $b->setProperties(array(
                'data' => (is_int($result)) ? $result
                        : (int)$_POST[$this->getTableName()][$this->getPK()],
                'result' => true,
                'mode' => (is_int($result)) ? 'insert' : 'update'
            ));
            $this->setBuilder($b);
        }
        catch (SystemException $e) {
            if ($transactionStarted) {
                $this->dbh->rollback();
            }
            throw $e;
        }
    }

    private function saveThumbs($thumbsData, $baseFileName) {

        $thumbProps = $this->getConfigValue('thumbnails');
        foreach ($thumbsData as $thumbName => $thumbData) {
            if ($thumbData) {
                $thumbPath = 'w0-h0/';
                if (isset($thumbProps[$thumbName])) {
                    $thumbPath = 'w' . $thumbProps[$thumbName]['width'] . '-h' . $thumbProps[$thumbName]['height'] . '/';
                }
                $dir = self::IMAGE_CACHE . $thumbPath . dirname($baseFileName) . '/';
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                if (!file_put_contents(self::IMAGE_CACHE . $thumbPath . $baseFileName, self::cleanFileData($thumbData))) {
                    stop(self::IMAGE_CACHE . $thumbPath . $baseFileName, $thumbData);
                }
            }
        }
    }

    protected function save() {
        $transactionStarted = $this->dbh->beginTransaction();
        try {
            if (!isset($_POST[$this->getTableName()]) || !isset($_POST[$this->getTableName()][$this->getPK()]) || !isset($_POST[$this->getTableName()]['upl_title']) || !isset($_POST[$this->getTableName()]['upl_pid'])) {
                throw new SystemException('ERR_NO_DATA');
            }
            $data = $_POST[$this->getTableName()];
            if (!$data['upl_pid']) {
                throw new SystemException('ERR_BAD_PID');
            }

            $mode = (empty($data[$this->getPK()])) ? QAL::INSERT : QAL::UPDATE;
            if ($mode == QAL::INSERT) {
                $fileData = self::cleanFileData($data['upl_path']);
                $parentData = $this->dbh->select($this->getTableName(), array('upl_path'), array('upl_id' => $data['upl_pid']));
                if (empty($parentData)) {
                    throw new SystemException('ERR_BAD_PID');
                }
                list($parentData) = $parentData;

                unset($data[$this->getPK()]);
                $data['upl_filename'] = FileObject::generateFilename($parentData['upl_path'], pathinfo($data['upl_filename'], PATHINFO_EXTENSION));
                $data['upl_path'] = $parentData['upl_path'] . '/' . $data['upl_filename'];
                if (!file_put_contents($data['upl_path'], $fileData)) {
                    throw new SystemException('ERR_SAVE_IMG');
                }
                $fi = new FileRepoInfo($data['upl_path']);
                $fi = $fi->analyze();

                $data['upl_mime_type'] = $fi->mime;
                $data['upl_internal_type'] = $fi->type;
                $data['upl_width'] = $fi->width;
                $data['upl_height'] = $fi->height;

                $result = $this->dbh->modify($mode, $this->getTableName(), $data);

                if (isset($_POST['thumbs'])) {
                    $this->saveThumbs($_POST['thumbs'], $data['upl_path']);
                }
            }
            $transactionStarted = !($this->dbh->commit());

            $b = new JSONCustomBuilder();
            $b->setProperties(array(
                'data' => (is_int($result)) ? $result
                        : (int)$_POST[$this->getTableName()][$this->getPK()],
                'result' => true,
                'mode' => (is_int($result)) ? 'insert' : 'update'
            ));
            $this->setBuilder($b);
        } catch (SystemException $e) {
            if ($transactionStarted) {
                $this->dbh->rollback();
            }
            throw $e;
        }

    }

    protected function add() {
        $sp = $this->getStateParams(true);
        if (isset($sp['pid'])) {
            $uplPID = (int)$sp['pid'];
        }
        else {
            $uplPID = '';
        }

        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->setBuilder($this->createBuilder());
        $this->setDataDescription($this->createDataDescription());
        $this->setData(new Data());
        $f = new Field('upl_pid');
        $f->setData($uplPID);
        $this->getData()->addField($f);

        $toolbars = $this->createToolbar();
        if (!empty($toolbars)) {
            $this->addToolbar($toolbars);
        }
        $this->js = $this->buildJS();
        $this->setAction('save/');
        if ($this->getConfigValue('thumbnails')) {
            $thumbs = $this->getConfigValue('thumbnails');
            $tabName = $this->translate('TXT_THUMBS');
            foreach ($thumbs as $name => $data) {
                $fd = new FieldDescription($name);
                $fd->setType(FieldDescription::FIELD_TYPE_THUMB);
                $this->getDataDescription()->addFieldDescription($fd);
                $fd->setProperty('tabName', $tabName);
                $fd->setProperty('tableName', 'thumbs');
                foreach ($data as $attrName => $attrValue) {
                    $fd->setProperty($attrName, $attrValue);
                }
            }
        }
    }

    protected function getRawData() {
        $sp = $this->getStateParams(true);

        if (!isset($sp['pid'])) {
            $this->addFilterCondition('(upl_pid IS NULL)');
            $uplPID = '';
        }
        else {
            $this->addFilterCondition(array('upl_pid' => ($uplPID = (int)$sp['pid'])));
        }

        parent::getRawData();

        if (isset($sp['pid'])) {
            $data = $this->getData();
            $newData = array(
                'upl_id' => $uplID = simplifyDBResult($this->dbh->select($this->getTableName(), 'upl_pid', array('upl_id' => $sp['pid'])), 'upl_pid', true),
                'upl_pid' => $uplPID,
                'upl_title' => '...',
                'upl_internal_type' => self::TYPE_FOLDER_UP
            );
            if (!$data->isEmpty())
                foreach ($this->getDataDescription()->getFieldDescriptionList() as $fieldName) {

                    $data->getFieldByName($fieldName)->addRowData(((isset($newData[$fieldName])) ? $newData[$fieldName] : ''), false);
                }
            else {
                $data->load(array($newData));
            }
        }
    }

    private static function cleanFileData($data, $mime=false) {
        $tmp = explode(';base64,', $data);
        return base64_decode($tmp[1]);
    }


}