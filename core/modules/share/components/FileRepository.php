<?php
/**
 * @file
 * FileRepository
 *
 * It contains the definition to:
 * @code
class FileRepository;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2012
 *
 * @version 1.0.0
 */
namespace Energine\share\components;
use Energine\share\gears\QAL, Energine\share\gears\FileRepoInfo, Energine\share\gears\DataDescription, Energine\share\gears\FieldDescription, Energine\share\gears\Data, Energine\share\gears\Field, Energine\share\gears\SystemException, Energine\share\gears\JSONCustomBuilder, Energine\share\gears\Translit, Energine\share\gears\JSONRepoBuilder;
/**
 * File repository.
 *
@code
class FileRepository;
@endcode
 */
class FileRepository extends Grid implements SampleFileRepository{
    /**
     * Path to temporary directory.
     * @var string TEMPORARY_DIR
     */
    const TEMPORARY_DIR = 'uploads/temp/';
    /**
     * Fake type to go to the upper folder.
     * @var string TYPE_FOLDER_UP
     */
    const TYPE_FOLDER_UP = 'folderup';


    /**
     * Cookie name.
     * It holds the last viewed folder ID.
     * @var string STORED_PID
     */
    const STORED_PID = 'NRGNFRPID';

    /**
     * Repository info.
     * @var FileRepoInfo $repoinfo
     */
    protected $repoinfo;

    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->repoinfo = E()->FileRepoInfo;
        $this->setTableName('share_uploads');
        $this->setFilter(array('upl_is_active' => 1));

        $this->setOrder(array('upl_publication_date' => QAL::DESC));
        $this->addTranslation('TXT_NOT_READY', 'FIELD_UPL_IS_READY', 'ERR_UPL_NOT_READY');
        //Если данные пришли из модального окна
        if (isset($_POST['modalBoxData']) && ($d = json_decode($_POST['modalBoxData']))) {
            if ((isset($d->upl_pid) && ($uplPID = ($this->dbh->getScalar($this->getTableName(), 'upl_id', array('upl_id' => $d->upl_pid)))))
                || (isset($d->upl_path) && ($uplPID = ($this->dbh->getScalar($this->getTableName(), 'upl_pid', array('upl_path' => $d->upl_path)))))
            ) {
                $this->response->addCookie(self::STORED_PID, $uplPID, 0, E()->getSiteManager()->getCurrentSite()->host, E()->getSiteManager()->getCurrentSite()->root);
            }
        }
    }

    /**
     * Proxy method for editing.
     */
    // разбрасывает по методам редактирования директории и файла
    protected function edit() {
        $sp = $this->getStateParams();
        $uplID = $sp[0];
        if ($this->dbh->getScalar($this->getTableName(),
                'upl_internal_type',
                array('upl_id' => $uplID)) == FileRepoInfo::META_TYPE_FOLDER
        ) {
            $this->editDir($uplID);
        } else {
            $this->editFile($uplID);
        }
    }

    /**
     * Method for adding video in text blocks.
     */
    protected function putVideo() {
        $sp = $this->getStateParams();
        $uplID = intval($sp[0]);
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
    }

    /**
     * Edit directory.
     *
     * @param int $uplID Upload ID.
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
     * Edit file.
     * @param int $uplID Upload ID.
     */
    private function editFile($uplID) {
        $this->setType(self::COMPONENT_TYPE_FORM_ALTER);
        $this->setBuilder($this->createBuilder());
        $this->setDataDescription($this->createDataDescription());
        $this->addFilterCondition(array('upl_id' => $uplID));

        $repository = $this->repoinfo->getRepositoryInstanceById($uplID);
        // меняем mode у поля для загрузки файла, если репозитарий RO
        if (!$repository->allowsUploadFile()) {
            $fd = $this->getDataDescription()->getFieldDescriptionByName('upl_path');
            if ($fd) {
                $fd->setMode(1);
                $fd->setProperty('title', 'FIELD_UPL_PATH_READ');
            }
        }

        $this->setData($this->createData());

        $toolbars = $this->createToolbar();
        if (!empty($toolbars)) {
            $this->addToolbar($toolbars);
        }
        $this->js = $this->buildJS();
        $this->setAction('save/');

        $this->createThumbFields();
    }

    /**
     * Create tab for thumbs.
     */
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
     * Add directory.
     */
    protected function addDir() {
        $sp = $this->getStateParams(true);
        if (isset($sp['pid'])) {
            $uplPID = (int)$sp['pid'];
        } else {
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
     * Save data in directory.
     *
     * @throws SystemException 'ERR_NO_DATA'
     * @throws SystemException 'ERR_BAD_PID'
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

            // получаем instance IFileRepository
            $repository = $this->repoinfo->getRepositoryInstanceById($data['upl_pid']);

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
                $data['upl_childs_count'] = 0;
                $data['upl_publication_date'] = date('Y-m-d H:i:s');
                $data['upl_path'] = $parentData['upl_path'] . ((substr($parentData['upl_path'], -1) != '/') ? '/' : '') . $data['upl_filename'];

                $where = false;

                $repository->createDir($data['upl_path']);

            } else {
                $where = array('upl_id' => $data['upl_id']);
                /*$currentUplPath = simplifyDBResult($this->dbh->select($this->getTableName(), array('upl_path'), array('upl_id' => $data['upl_id'])), 'upl_path', true);
                $data['upl_name'] = $data['upl_filename'] = Translit::asURLSegment($data['upl_title']);
                if($currentUplPath != $data['upl_path']){
                    rename($currentUplPath, $data['upl_path']);
                }*/
            }
            $result = $this->dbh->modify($mode, $this->getTableName(), $data, $where);

            $transactionStarted = !($this->dbh->commit());
            $uplID = (is_int($result)) ? $result : (int)$_POST[$this->getTableName()][$this->getPK()];

            $args = array($uplID, date('Y-m-d H:i:s'));

            $this->dbh->call('proc_update_dir_date', $args);

            $b = new JSONCustomBuilder();
            $b->setProperties(array(
                'data' => $uplID,
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
     * Save thumbs.
     *
     * @param array $thumbsData Thumbs data in the form @code name -> tmp_filename @endcode.
     * @param string $baseFileName Base file name.
     * @param IFileRepository $repo Repository.
     *
     * @throws SystemException 'ERR_SAVE_THUMBNAIL'
     */
    private function saveThumbs($thumbsData, $baseFileName, $repo) {
        $thumbProps = $this->getConfigValue('thumbnails');
        foreach ($thumbsData as $thumbName => $thumbTmpName) {
            if ($thumbTmpName) {
                $w = (!empty($thumbProps[$thumbName]['width'])) ? (int)$thumbProps[$thumbName]['width'] : 0;
                $h = (!empty($thumbProps[$thumbName]['height'])) ? (int)$thumbProps[$thumbName]['height'] : 0;
                try {
                    // todo: thumbName == preview ?
                    $repo->uploadAlt($thumbTmpName, $baseFileName, $w, $h);
                } catch (\Exception $e) {
                    throw new SystemException('ERR_SAVE_THUMBNAIL', SystemException::ERR_CRITICAL, (string)$e);
                }
            }
        }
    }


    /**
     * @copydoc Grid::save
     *
     * @throws SystemException 'ERR_NO_DATA'
     * @throws SystemException 'ERR_BAD_PID'
     * @throws SystemException 'ERR_SAVE_FILE'
     * @throws SystemException 'ERR_INCORRECT_MIME'
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

            // получаем instance IFileRepository
            $repository = $this->repoinfo->getRepositoryInstanceById($data['upl_pid']);

            $mode = (empty($data[$this->getPK()])) ? QAL::INSERT : QAL::UPDATE;

            // добавление файла в репозиторий
            if ($mode == QAL::INSERT) {

                $tmpFileName = $data['upl_path'];
                $uplPath = $this->dbh->getScalar($this->getTableName(), array('upl_path'), array('upl_id' => $data['upl_pid']));

                if ($uplPath && substr($uplPath, -1) == '/') $uplPath = substr($uplPath, 0, -1);

                if (empty($uplPath)) {
                    throw new SystemException('ERR_BAD_PID');
                }

                unset($data[$this->getPK()]);

                $data['upl_filename'] = self::generateFilename($uplPath, pathinfo($data['upl_filename'], PATHINFO_EXTENSION));
                $data['upl_path'] = $uplPath . ((substr($uplPath, -1) != '/') ? '/' : '') . $data['upl_filename'];

                if (!($fi = $repository->uploadFile($tmpFileName, $data['upl_path']))) {
                    throw new SystemException('ERR_SAVE_FILE');
                }

                $data['upl_mime_type'] = $fi->mime;
                $data['upl_internal_type'] = $fi->type;
                $data['upl_width'] = $fi->width;
                $data['upl_height'] = $fi->height;
                $data['upl_is_ready'] = $fi->ready;
                $data['upl_publication_date'] = date('Y-m-d H:i:s');

                $ext = strtolower(pathinfo($data['upl_filename'], PATHINFO_EXTENSION));
                switch ($ext) {
                    case 'mp4':
                        $data['upl_is_mp4'] = '1';
                        break;
                    case 'webm':
                        $data['upl_is_webm'] = '1';
                        break;
                    case 'flv':
                        $data['upl_is_flv'] = '1';
                        break;
                }

                $result = $this->dbh->modify($mode, $this->getTableName(), $data);

            } // редактирование файла в репозитории
            elseif ($mode == QAL::UPDATE) {

                $pk = $data[$this->getPK()];

                $old_upl_path = $this->dbh->getScalar($this->getTableName(), 'upl_path', array($this->getPK() => $pk));
                $new_upl_path = $data['upl_path'];

                unset($data['upl_path']);

                // если пришел новый tmpfile в поле upl_path
                if ($new_upl_path != $old_upl_path) {

                    $old_mime = $this->dbh->getScalar($this->getTableName(), 'upl_mime_type', array($this->getPK() => $pk));
                    $new_info = $repository->analyze($new_upl_path);

                    if ($new_info && $new_info->mime != $old_mime) {
                        throw new SystemException('ERR_INCORRECT_MIME');
                    }

                    if (!$repository->updateFile($new_upl_path, $old_upl_path)) {
                        throw new SystemException('ERR_SAVE_FILE');
                    }

                    $fi = $repository->analyze($new_upl_path);
                    if ($fi) {
                        $data['upl_width'] = $fi->width;
                        $data['upl_height'] = $fi->height;
                    }

                    $data['upl_publication_date'] = date('Y-m-d H:i:s');
                }

                $result = $this->dbh->modify($mode, $this->getTableName(), $data, array($this->getPK() => $pk));
                $data['upl_path'] = $old_upl_path;
            }

            if (isset($_POST['thumbs'])) {
                $this->saveThumbs($_POST['thumbs'], $data['upl_path'], $repository);
            }

            $transactionStarted = !($this->dbh->commit());
            $uplID = (is_int($result)) ? $result : (int)$_POST[$this->getTableName()][$this->getPK()];

            if ($mode == QAL::INSERT) {
                $args = array($uplID, $data['upl_publication_date']);
                $this->dbh->call('proc_update_dir_date', $args);
            }

            $b = new JSONCustomBuilder();
            $b->setProperties(array(
                'data' => $uplID,
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
     * @copydoc Grid::add
     */
    protected function add() {
        $sp = $this->getStateParams(true);
        if (isset($sp['pid'])) {
            $uplPID = (int)$sp['pid'];
        } else {
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

    /**
     * @copydoc Grid::loadData
     */
    protected function loadData() {
        $result = parent::loadData();

        if ($this->getState() == 'getRawData') {

            $sp = $this->getStateParams(true);

            $uplPID = (!empty($sp['pid'])) ? (int)$sp['pid'] : null;

            if (!$uplPID) return $result;
            // инстанс IFileRepository для текущего $uplPID
            $repo = $this->repoinfo->getRepositoryInstanceById($uplPID);
            $repo->prepare($result);
            if ($result) {
                foreach ($result as $i => $row) {
                    $result[$i]['upl_allows_create_dir'] = $repo->allowsCreateDir();
                    $result[$i]['upl_allows_upload_file'] = $repo->allowsUploadFile();
                    $result[$i]['upl_allows_edit_dir'] = $repo->allowsEditDir();
                    $result[$i]['upl_allows_edit_file'] = $repo->allowsEditFile();
                    $result[$i]['upl_allows_delete_dir'] = $repo->allowsDeleteDir();
                    $result[$i]['upl_allows_delete_file'] = $repo->allowsDeleteFile();
                }
            }
        }
        return $result;
    }

    /**
     * @copydoc Grid::getRawData
     */
    protected function getRawData() {
        $sp = $this->getStateParams(true);

        if (!isset($sp['pid'])) {
            jump:
            $this->addFilterCondition('(upl_pid IS NULL)');
            $uplPID = '';
        } else {
            $uplPID = (int)$sp['pid'];
            if (isset($_COOKIE[self::STORED_PID])) {
                //проверям а есть ли такое?
                if (!($this->dbh->getScalar($this->getTableName(), 'upl_id', array('upl_id' => $uplPID)))) {
                    goto jump;
                }
            }
            $this->addFilterCondition(array('upl_pid' => $uplPID));
        }

        parent::getRawData();
        // Плохо реализован дефолтный механизм подключения билдера
        $this->setBuilder(new JSONRepoBuilder());
        if ($this->pager) $this->getBuilder()->setPager($this->pager);

        if ($uplPID) {
            $data = $this->getData();
            $uplID = $this->dbh->getScalar($this->getTableName(), 'upl_pid', array('upl_id' => $sp['pid']));
            if (is_null($uplID)) {
                $uplID = 0;
            }
            // инстанс IFileRepository для текущего $uplPID
            $repo = $this->repoinfo->getRepositoryInstanceById($uplPID);
            $newData = array(
                'upl_id' => $uplID,
                'upl_pid' => $uplPID,
                'upl_title' => '...',
                'upl_internal_type' => self::TYPE_FOLDER_UP,
                // набор виртуальных upl_allows_* полей репозитария для folderup
                'upl_allows_create_dir' => $repo->allowsCreateDir(),
                'upl_allows_upload_file' => $repo->allowsUploadFile(),
                'upl_allows_edit_dir' => $repo->allowsEditDir(),
                'upl_allows_edit_file' => $repo->allowsEditFile(),
                'upl_allows_delete_dir' => $repo->allowsDeleteDir(),
                'upl_allows_delete_file' => $repo->allowsDeleteFile(),
            );

            //Так получилось что uplPID содержит текущий идентификатор, а uplID - родительский
            $p = array($uplPID);
            $res = $this->dbh->call('proc_get_upl_pid_list', $p);

            unset($p);
            if (!empty($res)) {
                $breadcrumbsData = array();
                foreach ($res as $row) {
                    $breadcrumbsData[$row['id']] = $row['title'];
                }
                $this->getBuilder()->setBreadcrumbs(array_reverse($breadcrumbsData, true));
            }

            if (!$data->isEmpty())
                foreach ($this->getDataDescription()->getFieldDescriptionList() as $fieldName) {
                    if ($f = $data->getFieldByName($fieldName))
                        $f->addRowData(((isset($newData[$fieldName])) ? $newData[$fieldName] : ''), false);
                }
            else {
                $data->load(array($newData));
            }
        }
    }

    /**
     * Show form to upload Zip file.
     *
     * @todo доделать
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
            $tmpFileName = $_POST['data'];

            if (!copy($tmpFileName, $fileName)) {
                throw new SystemException('ERR_CANT_CREATE_FILE', SystemException::ERR_CRITICAL);
            }
            $uplPID = $_POST['PID'];
            $transactionStarted = $this->dbh->beginTransaction();
            $extractPath = $this->dbh->getScalar($this->getTableName(), 'upl_path', array('upl_id' => $uplPID));

            $zip = new \ZipArchive();
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
                    } else {
                        $path = Translit::transliterate(addslashes($fileInfo['dirname'])) . '/';
                    }

                    //Directory
                    if (!isset($fileInfo['extension'])) {
                        $zip->renameIndex(
                            $i,
                            $currentFile = $path .
                                Translit::transliterate($fileInfo['filename'])
                        );
                    } else {
                        $zip->renameIndex(
                            $i,
                            $currentFile = $path . self::generateFilename('', $fileInfo['extension'])
                        );
                    }
                }
            }
            $zip->close();
            throw new SystemException('ERR_FAKE');
            $this->dbh->commit();
        } catch (SystemException $e) {
            if ($transactionStarted) {
                $this->dbh->rollback();
            }
        }
    }

    /**
     * Get path of temporary file.
     *
     * @param string $filename Filename.
     * @return string
     */
    public static function getTmpFilePath($filename) {
        return self::TEMPORARY_DIR . basename($filename);
    }

    /**
     * Generate filename.
     *
     * @param string $dirPath Directory path.
     * @param string $fileExtension File extension.
     * @return string
     */
    public static function generateFilename($dirPath, $fileExtension) {
        /*
         * Генерируем уникальное имя файла.
         */
        $c = ''; // первый вариант имени не будет включать символ '0'
        do {
            $filename = time() . rand(1, 10000) . "$c.{$fileExtension}";
            $c++; // при первом проходе цикла $c приводится к integer(1)
        } while (file_exists($dirPath . $filename));

        return $filename;
    }

    /**
     * Upload temporary file.
     */
    protected function uploadTemporaryFile() {
        $builder = new JSONCustomBuilder();
        $this->setBuilder($builder);

        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Origin, X-Requested-With');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit();
        }

        $response = array(
            'name' => '',
            'type' => '',
            'tmp_name' => '',
            'error' => false,
            'error_message' => '',
            'size' => 0,
            'preview' => ''
        );

        try {
            if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
                header('HTTP/1.1 201 Created');
                $key = (isset($_POST['key'])) ? $_POST['key'] : 'unknown';
                // $pid = (isset($_POST['pid'])) ? (int) $_POST['pid']: false;
                // $repo = $this->getRepositoryInstance($pid);
                if (isset($_FILES[$key]) and is_uploaded_file($_FILES[$key]['tmp_name'])) {
                    $tmp_name = $this->getTmpFilePath($_FILES[$key]['name']);
                    if (!is_writeable(dirname($tmp_name))) {
                        throw new SystemException('ERR_TEMP_DIR_WRITE', SystemException::ERR_CRITICAL, dirname($tmp_name));
                    }

                    if (move_uploaded_file($_FILES[$key]['tmp_name'], $tmp_name)) {
                        $response['name'] = $_FILES[$key]['name'];
                        $response['type'] = $_FILES[$key]['type'];
                        $response['tmp_name'] = $tmp_name;
                        $response['error'] = $_FILES[$key]['error'];
                        $response['size'] = $_FILES[$key]['size'];
                    } else {
                        $response['error'] = true;
                        $response['error_message'] = 'ERR_NO_FILE';
                    }
                } else {
                    $response['error'] = true;
                    $response['error_message'] = 'ERR_NO_FILE';
                }
            } else {
                $response['error'] = true;
                $response['error_message'] = 'ERR_INVALID_REQUEST_METHOD';
            }
        } catch (\Exception $e) {
            $response['error'] = true;
            $response['result'] = false;
            $response['error_message'] = (string)$e->getMessage();
        }

        // IE9 no-flash / iframe upload (fallback)
        $jsonp = isset($_REQUEST['callback']) ? trim($_REQUEST['callback']) : null;
        if (!empty($jsonp)) {
            echo '<script type="text/javascript">'
                . '(function(ctx,jsonp){'
                . 'if(ctx&&ctx[jsonp]){'
                . 'ctx[jsonp](200, "OK", "' . addslashes(json_encode($response)) . '")'
                . '}'
                . '})(this.parent, "' . $jsonp . '")'
                . '</script>';
            exit();
        }

        $builder->setProperties($response);
    }

    /**
     * Clean incoming from JS FileReader.
     *
     * @param string $data Data.
     * @param int $maxFileSize maximum file size (5mB default)
     * @return object
     * @throws SystemException
     *
     */
    public static function cleanFileData($data, $maxFileSize = 5242880) {
        ini_set('pcre.backtrack_limit', $maxFileSize);
        if (!preg_match('/data\:(.*);base64\,(.*)$/', $data, $matches)) {
            switch (preg_last_error()) {
                case PREG_NO_ERROR:
                    $errorMessage = 'ERR_BAD_FILE';
                    break;
                case PREG_INTERNAL_ERROR:
                    $errorMessage = 'ERR_PREG_INTERNAL';
                    break;
                case PREG_BACKTRACK_LIMIT_ERROR:
                    $errorMessage = 'ERR_PREG_BACKTRACK_LIMIT';
                    break;
                case PREG_RECURSION_LIMIT_ERROR:
                    $errorMessage = 'ERR_PREG_RECURSION_LIMIT';
                    break;
                case PREG_BAD_UTF8_ERROR:
                    $errorMessage = 'ERR_PREG_BAD_UTF8_ERROR';
                    break;
            }
            throw new SystemException($errorMessage, SystemException::ERR_WARNING);
        }
        $mime = $matches[1];
        $string = $matches[2];
        unset($matches);
        //http://j-query.blogspot.com/2011/02/save-base64-encoded-canvas-image-to-png.html?showComment=1402329668513#c517521780203205620
        $string = str_replace_opt(' ', '+', $string);

        return (object)array('mime' => $mime, 'data' => base64_decode($string));
    }
}

/**
 * Fake interface for XSLT
 * Interface SampleFileRepository
 */
interface SampleFileRepository{

}