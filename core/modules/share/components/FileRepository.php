<?php
/**
 * @file
 * FileRepository
 * It contains the definition to:
 * @code
class FileRepository;
 * @endcode
 * @author dr.Pavka
 * @copyright Energine 2012
 * @version 1.0.0
 */
namespace Energine\share\components;

use Energine\share\gears\Data;
use Energine\share\gears\DataDescription;
use Energine\share\gears\Field;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\FileRepoInfo;
use Energine\share\gears\IFileRepository;
use Energine\share\gears\JSONCustomBuilder;
use Energine\share\gears\JSONRepoBuilder;
use Energine\share\gears\QAL;
use Energine\share\gears\SystemException;
use Energine\share\gears\Translit;

use Energine\share\gears\EmptyBuilder;
use Energine\share\gears\Document;
/**
 * Fake interface for XSLT
 * Interface SampleFileRepository
 */
interface SampleFileRepository {

}

/**
 * File repository.
 * @code
 * class FileRepository;
 * @endcode
 */
class FileRepository extends Grid implements SampleFileRepository {
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
    public function __construct($name, array $params = null) {
        parent::__construct($name, $params);
        $this->repoinfo = E()->FileRepoInfo;
        $this->setTableName('share_uploads');
        $this->setFilter(['upl_is_active' => 1]);

        $this->setOrder(['upl_publication_date' => QAL::DESC]);
        $this->addTranslation('TXT_NOT_READY', 'FIELD_UPL_IS_READY', 'ERR_UPL_NOT_READY','TXT_FILE_SIZE');
        //Если данные пришли из модального окна
        if (isset($_POST['modalBoxData']) && ($d = json_decode($_POST['modalBoxData']))) {
            if ((isset($d->upl_pid) && ($uplPID = ($this->dbh->getScalar($this->getTableName(), 'upl_id',
                        ['upl_id' => $d->upl_pid]))))
                || (isset($d->upl_path) && ($uplPID = ($this->dbh->getScalar($this->getTableName(), 'upl_pid',
                        ['upl_path' => $d->upl_path]))))
            ) {
                $this->response->addCookie(self::STORED_PID, $uplPID, 0, E()->getSiteManager()->getCurrentSite()->host,
                    E()->getSiteManager()->getCurrentSite()->root);
            }
        }
    }

    /**
     * Proxy method for editing.
     */
    // разбрасывает по методам редактирования директории и файла
    /**
     * Clean incoming from JS FileReader.
     * @param string $data Data.
     * @param int $maxFileSize maximum file size (5mB default)
     * @return object
     * @throws SystemException
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

        return (object)['mime' => $mime, 'data' => base64_decode($string)];
    }
     /**
     * moveToDir
     * @param int $uplID Upload ID.
     */
    protected function moveToDir() {
        $sp = $this->getStateParams();
        $uplID = $sp[0];
        $this->setBuilder(new EmptyBuilder());
        $this->setDataDescription($this->createDataDescription());
        $this->setData(new Data());
        $this->setProperty('move_id', $uplID);
        $this->addToolbar($this->loadToolbar()); // add ok cancel buttons
        $this->js = $this->buildJS();
    }
     /**
     * GetDirectories
     * @param
     */
    protected function getDirs() {
        $this->setDataDescription($this->createDataDescription());

        $this->addFilterCondition("(upl_internal_type = 'repo' )");
        $repos=$this->loadData();

        $this->clearFilter();
        $this->addFilterCondition("(upl_internal_type = 'folder' )");
        $folders=$this->loadData();
        $d=new Data();
        $d->load(array_merge($repos,$folders));
        $this->setData($d);
        $this->setBuilder(new JSONRepoBuilder());

    }
      /**
     * Move Folder
     * @param int,int $moving_id $movetoid
     */
    protected function getDirsMove() {
        $transactionStarted = $this->dbh->beginTransaction();
       try {
        $sp = $this->getStateParams();
        list($moving_id,$movetoid)= explode(',',$sp[0]);
        $currentUplPath = simplifyDBResult($this->dbh->select($this->getTableName(), array('upl_path'), array('upl_id' => $moving_id)), 'upl_path', true);
        $moveUplPath = simplifyDBResult($this->dbh->select($this->getTableName(), array('upl_path'), array('upl_id' => $movetoid)), 'upl_path', true);
        //basename
        $newpath=$moveUplPath.'/'.(basename($currentUplPath));
        $result_rename=rename($currentUplPath,$newpath);
        //update refs
        if ($result_rename===true) {
            $result = $this->dbh->modify(QAL::UPDATE, $this->getTableName(),['upl_path'=>$newpath,'upl_pid'=>$movetoid] , [$this->getPK() => $moving_id]);
             $this->dbh->commit();
        }

        $b = new JSONCustomBuilder();
        $b->setProperties([
               'data'   => $movetoid,
              'result' => true,
              'mode'   => 'select'
        ]);
        $this->setBuilder($b);

        } catch (SystemException $e) {
            if ($transactionStarted) {
                $this->dbh->rollback();
            }
            throw $e;
        }
    }
     /**
     * Copy
     * @param int $uplID Upload ID.
     */
    protected function copy() {
        $sp = $this->getStateParams();
        $uplID = $sp[0];
        if ($this->dbh->getScalar($this->getTableName(),
                'upl_internal_type',
                ['upl_id' => $uplID]) == FileRepoInfo::META_TYPE_FOLDER
        ) {
            $this->copyDir($uplID);
        } else {
            $this->copyFile($uplID);
        }
        $b = new JSONCustomBuilder();
        $b->setProperties([
              'result' => true,
              'mode'   => 'select'
        ]);
        $this->setBuilder($b);
    }
     /**
     * Copy File
     * @param int $uplID Upload ID.
     */
    protected function copyFile($uplID,$root_path=null,$root_upl_pid=null) {
        $db_key=$this->getPK();
        $sql= "SELECT * FROM ".$this->getTableName()." WHERE ".$db_key."=". $uplID. " LIMIT 1";
        $file_info=$this->dbh->select($sql)[0];
        $file_path=pathinfo($file_info['upl_path']);
        $extension=$file_path['extension'];
        if ($root_path==null) { //same or other location
            $dir=$file_path['dirname'].'/';
        } else {
            //$newpath=$this->dbh->getScalar($this->getTableName(), 'upl_path', [$db_key => $upl_pid]);
            $dir=$root_path.'/';
        }
        $new_filename=self::generateFilename($dir,$extension);

        if (!copy($file_info['upl_path'],$dir.$new_filename)) {
            throw new SystemException('ERR_CANT_COPY_FILE');
        }
        //chmod($dir.$new_filename,0777);
        //update_db
        if ($root_upl_pid!=null)  {
            $file_info['upl_pid']=$root_upl_pid;
        }
        unset($file_info['upl_id']);
        $file_info['upl_title']="copy_".$file_info['upl_title'];
        $file_info['upl_path']=$dir.$new_filename;
        $file_info['upl_filename']=$new_filename;
        $file_info['upl_childs_count']=intval($file_info['upl_childs_count']);//bug workaround
        $result = $this->dbh->modify(QAL::INSERT, $this->getTableName(), $file_info);
    }
      /**
     * Copy Dir Recursive
     * @param int $uplID Upload ID.
     */
    protected function copyDir($uplID,$root_path=null,$root_upl_pid=null) {
        $db_key=$this->getPK();
        $sql= "SELECT * FROM ".$this->getTableName()." WHERE ".$db_key."=". $uplID. " LIMIT 1";
        $file_info=$this->dbh->select($sql)[0];

        $new_dirname="copy_".basename($file_info['upl_path']);
        $old_path=$file_info['upl_path'];
        if ($root_path==null) {
            $file_info['upl_path']=dirname($file_info['upl_path']).'/'.$new_dirname;
        } else {
            $file_info['upl_path']=$root_path.'/'.$new_dirname;
        }

        if (!mkdir($file_info['upl_path'])) {
            throw new SystemException('ERR_CANT_CREATE_DIR');
        }
        //chmod($file_info['upl_path'],0777);
        //update_db
        $orig_upl_id=$file_info['upl_id'];
        unset($file_info['upl_id']);
        if ($root_upl_pid!=null)  {
            $file_info['upl_pid']=$root_upl_pid;
        }
        $file_info['upl_title']=$new_dirname;
        $file_info['upl_filename']=$new_dirname;
        $file_info['upl_width']=intval($file_info['upl_width']);//bug workaround
        $file_info['upl_height']=intval($file_info['upl_height']);//bug workaround
        $file_info['upl_childs_count']=intval($file_info['upl_childs_count']);//bug workaround
        $insert_id = $this->dbh->modify(QAL::INSERT, $this->getTableName(), $file_info);

        //fetching childs
        $sql= "SELECT * FROM ".$this->getTableName()." WHERE upl_pid=". $orig_upl_id." ORDER BY upl_internal_type,upl_path";
        $child_records=$this->dbh->select($sql);
        foreach ($child_records as $child) {
            if ($child['upl_internal_type'] == FileRepoInfo::META_TYPE_FOLDER) {
                $this->copyDir($child['upl_id'],$file_info['upl_path'],$insert_id);
            }else { //file
                $this->copyFile($child['upl_id'],$file_info['upl_path'],$insert_id);
            }
        }
    }
      /**
     * Delete
     * @param int $uplID Upload ID.
     */
    protected function delete() {
        $sp = $this->getStateParams();
        $uplID = $sp[0];
        $db_key=$this->getPK();
        $sql= "SELECT * FROM ".$this->getTableName()." WHERE ".$db_key."=". $uplID. " LIMIT 1";
        $file_info=$this->dbh->select($sql)[0];
        parent::delete();
        if ($file_info['upl_internal_type'] == FileRepoInfo::META_TYPE_FOLDER) {
            $this->rmdir_recursive($file_info['upl_path']);
        } else {
            unlink($file_info['upl_path']);
        }
    }
      /**
     * Delete Dirs+Files Recursive
     * @param int $dir name
     */
    function rmdir_recursive($dir) {
        foreach(scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) continue;
            if (is_dir("$dir/$file")) $this->rmdir_recursive("$dir/$file");
            else unlink("$dir/$file");
        }
        rmdir($dir);
    }

    protected function edit() {
        $sp = $this->getStateParams();
        $uplID = $sp[0];
        if ($this->dbh->getScalar($this->getTableName(),
                'upl_internal_type',
                ['upl_id' => $uplID]) == FileRepoInfo::META_TYPE_FOLDER
        ) {
            $this->editDir($uplID);
        } else {
            $this->editFile($uplID);
        }
    }

    /**
     * Edit directory.
     * @param int $uplID Upload ID.
     */
    private function editDir($uplID) {
        $this->setFilter(['upl_id' => $uplID]);
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
        $this->addToolbar($this->loadToolbar());
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
        $this->addFilterCondition(['upl_id' => $uplID]);

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

        $this->addToolbar($this->loadToolbar());
        $this->js = $this->buildJS();
        $this->setAction('save/');

        $this->createThumbFields();
    }

    /**
     * Create tab for thumbs.
     */
    private function createThumbFields() {
        if ($thumbs = $this->getConfigValue('thumbnails')) {
            foreach ($thumbs as $name => $data) {
                $fd = new FieldDescription($name);
                $fd->setType(FieldDescription::FIELD_TYPE_THUMB);
                $this->getDataDescription()->addFieldDescription($fd);
                $fd->setProperty('tabName', E()->Utils->translate('TXT_THUMBS'));
                $fd->setProperty('tableName', 'thumbs');
                foreach ($data as $attrName => $attrValue) {
                    $fd->setProperty($attrName, $attrValue);
                }
            }
        }
    }

    /**
     * @copydoc Grid::save
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
                $uplPath = $this->dbh->getScalar($this->getTableName(), ['upl_path'], ['upl_id' => $data['upl_pid']]);

                if ($uplPath && substr($uplPath, -1) == '/') {
                    $uplPath = substr($uplPath, 0, -1);
                }

                if (empty($uplPath)) {
                    throw new SystemException('ERR_BAD_PID');
                }

                unset($data[$this->getPK()]);

                $data['upl_filename'] = self::generateFilename($uplPath,
                    pathinfo($data['upl_filename'], PATHINFO_EXTENSION));
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

                $old_upl_path = $this->dbh->getScalar($this->getTableName(), 'upl_path', [$this->getPK() => $pk]);
                $new_upl_path = $data['upl_path'];

                unset($data['upl_path']);

                // если пришел новый tmpfile в поле upl_path
                if ($new_upl_path != $old_upl_path) {

                    $old_mime = $this->dbh->getScalar($this->getTableName(), 'upl_mime_type', [$this->getPK() => $pk]);
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

                $result = $this->dbh->modify($mode, $this->getTableName(), $data, [$this->getPK() => $pk]);
                $data['upl_path'] = $old_upl_path;
            }

            if (isset($_POST['thumbs'])) {
                $this->saveThumbs($_POST['thumbs'], $data['upl_path'], $repository);
            }

            $transactionStarted = !($this->dbh->commit());
            $uplID = (is_int($result)) ? $result : (int)$_POST[$this->getTableName()][$this->getPK()];

            if ($mode == QAL::INSERT) {
                $args = [$uplID, $data['upl_publication_date']];
                $this->dbh->call('proc_update_dir_date', $args);
            }

            $b = new JSONCustomBuilder();
            $b->setProperties([
                'data'   => $uplID,
                'result' => true,
                'mode'   => (is_int($result)) ? 'insert' : 'update'
            ]);
            $this->setBuilder($b);
        } catch (SystemException $e) {
            if ($transactionStarted) {
                $this->dbh->rollback();
            }
            throw $e;
        }

    }

    /**
     * Generate filename.
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
     * Save thumbs.
     * @param array $thumbsData Thumbs data in the form @code name -> tmp_filename @endcode.
     * @param string $baseFileName Base file name.
     * @param IFileRepository $repo Repository.
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

        $this->addToolbar($this->loadToolbar());
        $this->js = $this->buildJS();
        $this->setAction('save/');

        $this->createThumbFields();
    }

    /**
     * @copydoc Grid::loadData
     */
    protected function loadData() {
        $this->setOrder(['upl_internal_type'=>QAL::ASC,'upl_title'=>QAL::ASC]);
        $result = parent::loadData();

        if ($this->getState() == 'getRawData') {

            $sp = $this->getStateParams(true);

            $uplPID = (!empty($sp['pid'])) ? (int)$sp['pid'] : null;

            if (!$uplPID) {
                return $result;
            }
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
                if (!($this->dbh->getScalar($this->getTableName(), 'upl_id', ['upl_id' => $uplPID]))) {
                    goto jump;
                }
            }
            $this->addFilterCondition(['upl_pid' => $uplPID]);
        }

        parent::getRawData();
        // Плохо реализован дефолтный механизм подключения билдера
        $this->setBuilder(new JSONRepoBuilder());
        if ($this->pager) {
            $this->getBuilder()->setPager($this->pager);
        }

        if ($uplPID) {
            $data = $this->getData();
            $uplID = $this->dbh->getScalar($this->getTableName(), 'upl_pid', ['upl_id' => $sp['pid']]);
            if (is_null($uplID)) {
                $uplID = 0;
            }
            // инстанс IFileRepository для текущего $uplPID
            $repo = $this->repoinfo->getRepositoryInstanceById($uplPID);
            $newData = [
                'upl_id'                 => $uplID,
                'upl_pid'                => $uplPID,
                'upl_title'              => '...',
                'upl_internal_type'      => self::TYPE_FOLDER_UP,
                // набор виртуальных upl_allows_* полей репозитария для folderup
                'upl_allows_create_dir'  => $repo->allowsCreateDir(),
                'upl_allows_upload_file' => $repo->allowsUploadFile(),
                'upl_allows_edit_dir'    => $repo->allowsEditDir(),
                'upl_allows_edit_file'   => $repo->allowsEditFile(),
                'upl_allows_delete_dir'  => $repo->allowsDeleteDir(),
                'upl_allows_delete_file' => $repo->allowsDeleteFile(),
            ];

            //Так получилось что uplPID содержит текущий идентификатор, а uplID - родительский
            $p = [$uplPID];
            $res = $this->dbh->call('proc_get_upl_pid_list', $p);

            unset($p);
            if (!empty($res)) {
                $breadcrumbsData = [];
                foreach ($res as $row) {
                    $breadcrumbsData[$row['id']] = $row['title'];
                }
                $this->getBuilder()->setBreadcrumbs(array_reverse($breadcrumbsData, true));
            }

            if (!$data->isEmpty()) {
                foreach ($this->getDataDescription()->getFieldDescriptionList() as $fieldName) {
                    if ($f = $data->getFieldByName($fieldName)) {
                        $f->addRowData(((isset($newData[$fieldName])) ? $newData[$fieldName] : ''), false);
                    }
                }
            } else {
                $data->load([$newData]);
            }
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
        $this->addFilterCondition(['upl_id' => $uplID]);
        $this->setData($this->createData());

        $this->addToolbar($this->loadToolbar());
        $this->js = $this->buildJS();
        $this->setAction('save/');
    }

    /**
     * Show form to upload Zip file.
     * @todo доделать
     */
    /*protected function uploadZip() {
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
            $extractPath = $this->dbh->getScalar($this->getTableName(), 'upl_path', ['upl_id' => $uplPID]);

            $zip = new \ZipArchive();
            $zip->open($fileName);
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $currentFile = $zip->statIndex($i);
                $currentFile = $currentFile['name'];
                $fileInfo = pathinfo($currentFile);

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
    }*/

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

        $this->addToolbar($this->loadToolbar());
        $this->js = $this->buildJS();
        $this->setAction('save-dir/');
    }

    /**
     * Save data in directory.
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

                $parentPath = $this->dbh->getScalar($this->getTableName(), ['upl_path'],
                    ['upl_id' => $data['upl_pid']]);

                if (!$parentPath) {
                    throw new SystemException('ERR_BAD_PID');
                }
                unset($data[$this->getPK()]);
                $data['upl_name'] = $data['upl_filename'] = Translit::asURLSegment($data['upl_title']);
                $data['upl_mime_type'] = 'unknown/mime-type';
                $data['upl_internal_type'] = FileRepoInfo::META_TYPE_FOLDER;
                $data['upl_childs_count'] = 0;
                $data['upl_publication_date'] = date('Y-m-d H:i:s');
                $data['upl_path'] = $parentPath . ((substr($parentPath, -1) != '/') ? '/' : '') . $data['upl_filename'];

                $where = false;

                $repository->createDir($data['upl_path']);

            } else {
                $where = ['upl_id' => $data['upl_id']];
                /*$currentUplPath = simplifyDBResult($this->dbh->select($this->getTableName(), array('upl_path'), array('upl_id' => $data['upl_id'])), 'upl_path', true);
                $data['upl_name'] = $data['upl_filename'] = Translit::asURLSegment($data['upl_title']);
                if($currentUplPath != $data['upl_path']){
                    rename($currentUplPath, $data['upl_path']);
                }*/
            }
            $result = $this->dbh->modify($mode, $this->getTableName(), $data, $where);

            $transactionStarted = !($this->dbh->commit());
            $uplID = (is_int($result)) ? $result : (int)$_POST[$this->getTableName()][$this->getPK()];

            $args = [$uplID, date('Y-m-d H:i:s')];

            $this->dbh->call('proc_update_dir_date', $args);

            $b = new JSONCustomBuilder();
            $b->setProperties([
                'data'   => $uplID,
                'result' => true,
                'mode'   => (is_int($result)) ? 'insert' : 'update'
            ]);
            $this->setBuilder($b);
        } catch (SystemException $e) {
            if ($transactionStarted) {
                $this->dbh->rollback();
            }
            throw $e;
        }
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

        $response = [
            'name'          => '',
            'type'          => '',
            'tmp_name'      => '',
            'error'         => false,
            'error_message' => '',
            'size'          => 0,
            'preview'       => ''
        ];

        try {
            if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
                header('HTTP/1.1 201 Created');
                $key = (isset($_POST['key'])) ? $_POST['key'] : 'unknown';
                // $pid = (isset($_POST['pid'])) ? (int) $_POST['pid']: false;
                // $repo = $this->getRepositoryInstance($pid);
                if (isset($_FILES[$key]) and is_uploaded_file($_FILES[$key]['tmp_name'])) {
                    $tmp_name = $this->getTmpFilePath($_FILES[$key]['name']);
                    if (!is_writeable(dirname($tmp_name))) {
                        throw new SystemException('ERR_TEMP_DIR_WRITE', SystemException::ERR_CRITICAL,
                            dirname($tmp_name));
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
     * Get path of temporary file.
     * @param string $filename Filename.
     * @return string
     */
    public static function getTmpFilePath($filename) {
        return self::TEMPORARY_DIR . basename($filename);
    }
}