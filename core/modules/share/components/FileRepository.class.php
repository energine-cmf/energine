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
    const TEMPORARY_DIR = 'uploads/temp/';
    //путь к кешу ресайзера
    const IMAGE_CACHE = 'uploads/resizer/';
    //Путь к кешу для альтернативных картинок
    const IMAGE_ALT_CACHE = 'uploads/alts/';
    //const TYPE_FOLDER = 'folder';
    //Фейковый тип для перехода на уровень выше
    const TYPE_FOLDER_UP = 'folderup';
    //Имя куки в которой хранится идентификатор папки к которой в последний раз обращались
    const STORED_PID = 'NRGNFRPID';

    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('share_uploads');
        $this->setFilter(array('upl_is_active' => 1));
        $this->setOrder(array('upl_title' => QAL::ASC));
    }

    /**
     * прокси метод для редактирования
     * разбрасывает по методам редактирования директории и файла
     */
    protected function edit() {
        $sp = $this->getStateParams();
        $uplID = $sp[0];
        if ($this->dbh->getScalar($this->getTableName(),
            'upl_internal_type',
            array('upl_id' => $uplID)) == FileRepoInfo::META_TYPE_FOLDER
        ) {
            $this->editDir($uplID);
        }
        else {
            $this->editFile($uplID);
        }
    }

    /**
     * Редактирование директории
     *
     * @param int $uplID идентификатор аплоада
     */
    private function editDir($uplID) {
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

    /**
     * Редактирование файла
     * @param $uplID
     */
    private function editFile($uplID) {
        $this->setType(self::COMPONENT_TYPE_FORM_ALTER);
        $this->setBuilder($this->createBuilder());
        $this->setDataDescription($this->createDataDescription());
        $this->addFilterCondition(array('upl_id' => $uplID));
        $this->setData($this->createData());

        $toolbars = $this->createToolbar();
        if (!empty($toolbars)) {
            $this->addToolbar($toolbars);
        }
        $this->js = $this->buildJS();
        $this->setAction('save/');

        $this->createThumbFields();
    }

    private function createThumbFields() {
        if ($thumbs = $this->getConfigValue('thumbnails')) {
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

    /**
     * Создание директории
     */
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

    /**
     * Создание временного файла
     * Используется для генерации превью
     *
     * @throws SystemException
     */
    protected function createTemporaryFile() {

        $b = new JSONCustomBuilder();
        $this->setBuilder($b);

        try {
            if (!isset($_POST['data']) || !isset($_POST['name'])) {
                throw new SystemException('ERR_BAD_DATA');
            }
            $fileData = self::cleanFileData($_POST['data']);
            if (!file_put_contents(($result = self::getTmpFilePath($_POST['name'])), $fileData)) {
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

    /**
     * Сохранеие данных директории
     * @throws SystemException
     */
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

    /**
     * Сохранение thumbов
     *
     * @param $thumbsData
     * @param $baseFileName
     */
    private function saveThumbs($thumbsData, $baseFileName) {
        $thumbProps = $this->getConfigValue('thumbnails');
        foreach ($thumbsData as $thumbName => $thumbData) {
            if ($thumbData) {
                if ($thumbName != 'preview') {
                    $thumbPath = 'w0-h0/';
                    if (isset($thumbProps[$thumbName])) {
                        $thumbPath = 'w' . $thumbProps[$thumbName]['width'] . '-h' . $thumbProps[$thumbName]['height'] . '/';
                    }
                    $dir = self::IMAGE_CACHE . $thumbPath . dirname($baseFileName) . '/';
                    $fullFileName = self::IMAGE_CACHE . $thumbPath . $baseFileName;
                }
                else {
                    $thumbPath = '';
                    $dir = self::IMAGE_ALT_CACHE . $thumbPath . dirname($baseFileName) . '/';
                    $fullFileName = self::IMAGE_ALT_CACHE . $thumbPath . $baseFileName;
                }

                if (!file_exists($dir)) {
                    //                    stop($dir);
                    mkdir($dir, 0777, true);
                }
                if (!file_put_contents($fullFileName, self::cleanFileData($thumbData))) {
                    throw new SystemException('ERR_CANT_SAVE_THUMB', SystemException::ERR_WARNING, $fullFileName);
                }
            }
        }
    }

    /**
     * Сохранение файла
     * @throws SystemException
     */
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
                $uplPath = $this->dbh->getScalar($this->getTableName(), array('upl_path'), array('upl_id' => $data['upl_pid']));
                if (empty($uplPath)) {
                    throw new SystemException('ERR_BAD_PID');
                }
                unset($data[$this->getPK()]);
                $data['upl_filename'] = self::generateFilename($uplPath, pathinfo($data['upl_filename'], PATHINFO_EXTENSION));
                $data['upl_path'] = $uplPath . '/' . $data['upl_filename'];
                if (!file_put_contents($data['upl_path'], $fileData)) {
                    throw new SystemException('ERR_SAVE_IMG');
                }
                $fi = E()->FileRepoInfo->analyze($data['upl_path'], true);

                $data['upl_mime_type'] = $fi->mime;
                $data['upl_internal_type'] = $fi->type;
                $data['upl_width'] = $fi->width;
                $data['upl_height'] = $fi->height;
                $data['upl_publication_date'] = date('Y-m-d H:i:s');
                $result = $this->dbh->modify($mode, $this->getTableName(), $data);

            }
            elseif ($mode == QAL::UPDATE) {
                $pk = $data[$this->getPK()];

                $result = $this->dbh->modify($mode, $this->getTableName(), $data, array($this->getPK() => $pk));
            }
            if (isset($_POST['thumbs'])) {
                $this->saveThumbs($_POST['thumbs'], $data['upl_path']);
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

    /**
     * Форма добавления файла
     */
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

        $this->createThumbFields();
    }

    protected function applyUserFilter() {
        //Формат фильтра
        //$_POST['filter'][$tableName][$fieldName] = значение фильтра
        if (isset($_POST['filter'])) {
            $condition = $_POST['filter']['condition'];
            $conditionPatterns = array(
                'like' => 'LIKE \'%%%s%%\'',
                'notlike' => 'NOT LIKE \'%%%s%%\'',
                '=' => '= \'%s\'',
                '!=' => '!= \'%s\'',
                '<' => '<\'%s\'',
                '>' => '>\'%s\'',
                'between' => 'BETWEEN \'%s\' AND \'%s\''
            );

            unset($_POST['filter']['condition']);
            $tableName = key($_POST['filter']);
            $fieldName = key($_POST['filter'][$tableName]);
            $values = $_POST['filter'][$tableName][$fieldName];

            $currentFilters = $this->getFilter();
            unset($currentFilters['upl_pid']);
            $this->setFilter($currentFilters);

            $tableName = ($tableName) ? $tableName . '.' : '';
            $this->addFilterCondition(
                $tableName . $fieldName . ' ' .
                        call_user_func_array('sprintf', array_merge(array($conditionPatterns[$condition]), $values)) .
                        ' '
            );

        }
    }

    protected function getRawData() {
        $sp = $this->getStateParams(true);

        if (!isset($sp['pid'])) {
            jump:
            $this->addFilterCondition('(upl_pid IS NULL)');
            $uplPID = '';
        }
        else {
            $uplPID = (int)$sp['pid'];

            if (isset($_COOKIE[self::STORED_PID])) {
                //проверям а есть ли такое?
                if (!($this->dbh->getScalar($this->getTableName(), 'upl_id', array('upl_id' => $uplPID)))) {
                    /* $site = E()->getSiteManager()->getCurrentSite();
              setcookie(self::STORED_PID, '', time() - 3600, $site->root, $site->protocol.'://'.$site->host);*/
                    goto jump;
                }
            }
            $this->addFilterCondition(array('upl_pid' => $uplPID));
        }

        parent::getRawData();

        if ($uplPID) {
            $data = $this->getData();
            $uplID = $this->dbh->getScalar($this->getTableName(), 'upl_pid', array('upl_id' => $sp['pid']));
            if (is_null($uplID)) {
                $uplID = 0;
            }
            $newData = array(
                'upl_id' => $uplID,
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

    /**
     * Выводим форму загрузки Zip файла содержащего набор файлов
     *
     * @todo доделать
     * @return void
     * @access protected
     */
    protected function uploadZip() {
        $builder = new JSONCustomBuilder();
        $this->setBuilder($builder);
        $transactionStarted = false;
        try {
            if (!isset($_POST['data']) || !isset($_POST['PID'])) {
                throw new SystemException('ERR_BAD_DATA', SystemException::ERR_CRITICAL);
            }
            $fileName = tempnam(self::TEMPORARY_DIR, "zip");
            $data = self::cleanFileData($_POST['data']);

            if (!file_put_contents($fileName, $data)) {
                throw new SystemException('ERR_CANT_CREATE_FILE', SystemException::ERR_CRITICAL);
            }
            $uplPID = $_POST['PID'];
            $transactionStarted = $this->dbh->beginTransaction();
            $extractPath = $this->dbh->getScalar($this->getTableName(), 'upl_path', array('upl_id' => $uplPID));

            $zip = new ZipArchive();
            $zip->open($fileName);
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $currentFile = $zip->statIndex($i);
                $currentFile = $currentFile['name'];
                $fileInfo = pathinfo($currentFile);
                inspect($fileInfo);
                if (
                    !((substr($fileInfo['filename'], 0, 1) === '.') || (strpos($currentFile, 'MACOSX') !== false)
                    )
                ) {
                    if ($fileInfo['dirname'] == '.') {
                        $path = '';
                    }
                    else {
                        $path = Translit::transliterate(addslashes($fileInfo['dirname'])) . '/';
                    }

                    //Directory
                    if (!isset($fileInfo['extension'])) {
                        $zip->renameIndex(
                            $i,
                            $currentFile = $path .
                                    Translit::transliterate($fileInfo['filename'])
                        );
                    }
                    else {
                        $zip->renameIndex(
                            $i,
                            $currentFile = $path .self::generateFilename('', $fileInfo['extension'])
                        );
                    }
                }
            }
            $zip->close();
            throw new SystemException('ERR_FAKE');
            $this->dbh->commit();
        }
        catch (SystemException $e) {
            if ($transactionStarted) {
                $this->dbh->rollback();
            }
        }
    }

    /**
     * Очистка пришедших из JS FileReader
     * @static
     * @param $data
     * @param bool $mime
     * @return string
     */
    public static function cleanFileData($data) {
        $tmp = explode(';base64,', $data);
        return base64_decode($tmp[1]);
    }

    public static function getTmpFilePath($filename){
   		return self::TEMPORARY_DIR.basename($filename);
   	}

    public static function generateFilename($dirPath, $fileExtension){
   		/*
   		 * Генерируем уникальное имя файла.
   		 */
   		$c = ''; // первый вариант имени не будет включать символ '0'
   		do {
   			$filename = time().rand(1, 10000)."$c.{$fileExtension}";
   			$c++; // при первом проходе цикла $c приводится к integer(1)
   		} while(file_exists($dirPath.$filename));

   		return $filename;
   	}

}