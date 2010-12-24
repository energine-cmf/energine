<?php
/**
 * Содержит класс FileLibrary
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 */

/**
 * Библитека изображений
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @final
 */
final class FileLibrary extends DataSet {
    /**
     * Путь к директории в которой хранятся загруженные пользователями файлы
     *
     */
    const UPLOADS_MAIN_DIR = 'uploads/public';

    /**
     * Uploads Directory
     *
     * @var DirectoryObject
     * @access private
     */
    private $uploadsDir;

    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setProperty('exttype', 'grid');
        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->setTitle($this->translate(
            'TXT_' . strtoupper($this->getName())));
        //Отключили pager
        //$this->setParam('recordsPerPage', false);

        if (!isset($_POST['path']) || empty($_POST['path'])) {
            $path = $this->getParam('base');
        }
        else {
            $path = $_POST['path'];
        }

        $this->uploadsDir = DirectoryObject::loadFrom($path);
    }

    /**
     * Переопределен параметр active
     *
     * @return int
     * @access protected
     */

    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
            array(
                'active' => true,
                'base' => self::UPLOADS_MAIN_DIR
            ));
        return $result;
    }

    /**
     * Загружает описание данных из таблицы
     *
     * @return DataDescription
     * @access protected
     */

    protected function loadDataDescription() {
        $result = $this->dbh->getColumnsInfo(DirectoryObject::TABLE_NAME);
        $result['upl_mime_type'] = array(
            'length' => 100,
            'nullable' => false,
            'default' => false,
            'key' => false,
            'type' => FieldDescription::FIELD_TYPE_STRING
        );
        $result['className'] = array(
            'length' => 100,
            'nullable' => false,
            'default' => false,
            'key' => false,
            'type' => FieldDescription::FIELD_TYPE_STRING
        );
        foreach ($result as $key => $value) {
            $result[$key]['tabName'] = $this->getTitle();
        }
        return $result;
    }

    /**
     * Метод загрузки данных
     *
     * @return void
     * @access public
     */

    public function loadData() {
        if ($this->getAction() == 'getRawData') {
            $result = array();
            if ($this->uploadsDir->getPath() != $this->getParam('base')) {
                $result =
                        call_user_func_array(array($this->uploadsDir, 'asArray'), $this->pager->getLimit());
                $result['upl_path'] = dirname($result['upl_path']);
                $result['upl_name'] = '...';
            }
            //inspect($result);
            $this->uploadsDir->open();
            if ($this->pager) {
                $this->pager->setRecordsCount($this->uploadsDir->getFileCount());
            }
            if (!empty($result)) {
                $result =
                        array_merge(array($result), call_user_func_array(array($this->uploadsDir, 'asArray'), $this->pager->getLimit()));
            }
            else {
                $result =
                        call_user_func_array(array($this->uploadsDir, 'asArray'), $this->pager->getLimit());
            }

            $result = array_map(array($this, 'addClass'), $result);
        }
        elseif ($this->getAction() == self::DEFAULT_ACTION_NAME) {
            $result = false;
        }
        else {
            $result = parent::loadData();
        }
        return $result;
    }

    /**
     * На основании значения константы набора типов формируется поле типа class
     *
     * @return array
     * @access private
     */

    private function addClass($row) {
        switch ($row['upl_mime_type']) {
            case FileInfo::META_TYPE_FOLDER:
                $row['className'] = 'folder';
                break;
            case FileInfo::META_TYPE_IMAGE:
                $row['className'] = 'image';
                break;
            case FileInfo::META_TYPE_ZIP:
                $row['className'] = 'zip';
                break;
            default:
                $row['className'] = 'undefined';
                break;
        }
        return $row;
    }

    /**
     * Выводит данные в JSON формате для AJAX
     *
     * @return void
     * @access protected
     */

    protected function getRawData() {
        $this->config->setCurrentMethod(self::DEFAULT_ACTION_NAME);
        $this->setBuilder(new JSONUploadBuilder());
        $this->setDataDescription($this->createDataDescription());
        $this->getBuilder()->setDataDescription($this->getDataDescription());
        $this->getBuilder()->setCurrentDir($this->uploadsDir->getPath());

        $this->createPager();
        $data = $this->createData();
        $this->getBuilder()->setPager($this->pager);

        if ($data instanceof Data) {
            $this->setData($data);
            $this->getBuilder()->setData($this->getData());
        }
        $this->getBuilder()->build();


    }

    /**
     * Method Description
     *
     * @return type
     * @access protected
     */

    protected function main() {
        parent::main();
        $this->setProperty('allowed_file_type', 'all');
        if ($params = $this->getActionParams(true)) {
            if (is_array($params) && !empty($params) &&
                    in_array($params['allowed_file_type'], array('media', 'image'))) {
                $this->setProperty('allowed_file_type', $params['allowed_file_type']);
            }
        }
    }

    /**
     * Метод выводит форму создания новой папки
     *
     * @return void
     * @access protected
     */

    protected function addDir() {
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->prepare();
        if ($field =
                $this->getDataDescription()->getFieldDescriptionByName('tags')) {
            //$field->setProperty('nullable', 'nullable');
            $field->removeProperty('pattern');
            $field->removeProperty('message');
        }
    }

    /**
     * Сохранение данных о папке
     *
     * @return void
     * @access protected
     */

    protected function saveDir() {
        $builder = new JSONCustomBuilder();
        $this->setBuilder($builder);
        try {
            $folder = new DirectoryObject();
            $folder->create($_POST);

            $builder->setProperty('result', true)->setProperty('mode', 'insert');

        }
        catch (SystemException $e) {
            $message['errors'][] = array('message' =>
            $e->getMessage() . current($e->getCustomMessage()));
            $builder->setProperties(array_merge(array('result' => false, 'header' => $this->translate('TXT_SHIT_HAPPENS')), $message));
        }
    }

    /**
     * Метод сохранения файла
     *
     * @return void
     * @access protected
     */

    protected function save() {
        $builder = new JSONCustomBuilder();
        $this->setBuilder($builder);
        try {
            $file = new FileObject();
            $file->create($_POST, (bool) $_POST['upl_resize_image']);

            $builder->setProperty('result', true)->setProperty('mode', 'insert');
        }
        catch (SystemException $e) {
            $message['errors'][] = array('message' =>
            $e->getMessage() . current($e->getCustomMessage()));
            $builder->setProperties(array_merge(array('result' => false, 'header' => $this->translate('TXT_SHIT_HAPPENS')), $message));
        }

    }

    /**
     * Удаление папки/файла
     *
     * @return void
     * @access protected
     */

    protected function delete() {
        $builder = new JSONCustomBuilder();
        $this->setBuilder($builder);
        try {
            if (!isset($_POST['file'])) {
                throw new SystemException('ERR_NO_FILE');
            }
            if (($fileType = key($_POST['file'])) == 'folder') {
                $file = DirectoryObject::loadFrom($_POST['file'][$fileType]);
            }
            else {
                $file = FileObject::loadFrom($_POST['file'][$fileType]);
            }

            $file->delete();

            $builder->setProperty('result', true)->setProperty('mode', 'delete');
        }
        catch (SystemException $e) {
            $message['errors'][] = array('message' =>
            $e->getMessage() . current($e->getCustomMessage()));
            $builder->setProperties(array_merge(array('result' => false, 'header' => $this->translate('TXT_SHIT_HAPPENS')), $message));

        }
    }

    /**
     * Распаковка залитого zip файла
     *
     * @return void
     * @access protected
     */
    protected function saveZip() {
         $builder = new JSONCustomBuilder();
        $this->setBuilder($builder);
        try {
            $filename =
                    FileObject::getTmpFilePath($_POST['share_uploads']['upl_path']);

            if (file_exists($filename)) {
                //setlocale(LC_CTYPE, "uk_UA.UTF-8");
                $zip = new ZipArchive();
                $zip->open($filename);

                for ($i = 0; $i < $zip->numFiles; $i++) {

                    $currentFile = $zip->statIndex($i);

                    $currentFile = $currentFile['name'];
                    $fileInfo = pathinfo($currentFile);
                    /*if($fileInfo['filename'] === ''){

                         }
                         else*/
                    if (
                    !(
                            (substr($fileInfo['filename'], 0, 1) === '.')
                                    ||
                                    (strpos($currentFile, 'MACOSX') !== false)
                    )
                    ) {
                        if ($fileInfo['dirname'] == '.') {
                            $path = '';
                        }
                        else {
                            $path =
                                    Translit::transliterate(addslashes($fileInfo['dirname'])) .
                                            '/';
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
                                $currentFile = $path .
                                        FileObject::generateFilename('', $fileInfo['extension'])
                            );
                        }

                        $zip->extractTo($this->uploadsDir->getPath(), $currentFile);
                        FileObject::createFrom(
                            $this->uploadsDir->getPath() . '/' .
                                    $currentFile, $fileInfo['filename']);
                    }
                }
                $zip->close();
            }
            $builder->setProperty('result', true)->setProperty('mode', 'insert');
        }
        catch (SystemException $e) {
            $message['errors'][] = array('message' =>
            $e->getMessage() . current($e->getCustomMessage()));
            $builder->setProperties(array_merge(array('result' => false, 'header' => $this->translate('TXT_SHIT_HAPPENS')), $message));
        }
    }

    /**
     * Выводит форму создания файла
     *
     * @return void
     * @access protected
     */

    protected function add() {
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->prepare();
        $f = new FieldDescription('upl_resize_image');
        $f->setType(FieldDescription::FIELD_TYPE_BOOL);
        $this->getDataDescription()->addFieldDescription($f);

        $f = new Field('upl_resize_image');
        $f->setData(true, true);
        $this->getData()->addField($f);

        if ($field =
                $this->getDataDescription()->getFieldDescriptionByName('tags')) {
            //$field->setProperty('nullable', 'nullable');
            $field->removeProperty('pattern');
            $field->removeProperty('message');
        }
        if ($field =
                $this->getDataDescription()->getFieldDescriptionByName('upl_description')) {
            $field->setProperty('nullable', 'nullable');
        }
    }

    /**
     * Выводи форму загрузки Zip файла содержащего набор файлов
     *
     * @return void
     * @access protected
     */
    protected function uploadZip() {
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->prepare();
    }

    /**
     * Метод для заливки файла
     * Вызывается в невидимом фрейме и должен отдать HTML страницу включающаю скрипт
     *
     * @return void
     * @access protected
     * @final
     */
    final protected function upload() {
        try {
            if (empty($_FILES) || !isset($_POST['Filename']) ||
                    !isset($_FILES['Filedata']) || !isset($_POST['element'])) {
                throw new SystemException('ERR_BAD_FILE', SystemException::ERR_CRITICAL);
            }

            $additionalPath = '';
            if (isset($_POST['path']) && !empty($_POST['path'])) {
                $additionalPath =
                        str_replace($this->getParam('base'), '', $_POST['path']) .
                                '/';
            }

            $result = array('status' => 1, 'element' => $_POST['element']);

            $uploader = new FileUploader();
            $uploader->setFile($_FILES['Filedata']);
            $uploader->upload(FileObject::TEMPORARY_DIR);
            $fileName = $uploader->getFileObjectName();
            $result['file'] = $this->getParam('base') . $additionalPath .
                    basename($fileName);
            $result['title'] = pathinfo($_POST['Filename'], PATHINFO_FILENAME);
            if (
                    E()->FileInfo->analyze($fileName)->type ==
                    FileInfo::META_TYPE_IMAGE
            ) {
                $result['preview'] = $fileName;
            }
                //
            else {
                $result['preview'] = 'images/icons/icon_undefined.gif';
            }
        }
        catch (Exception $e) {
            $result = array('status' => 0, 'error' => $e->getMessage());
        }
        $b = new JSONCustomBuilder();
        $b->setProperties($result);
        $this->setBuilder($b);
    }

    protected function put() {
        try {
            if (empty($_FILES) || !isset($_FILES['Filedata'])) {
                throw new SystemException('ERR_NO_FILE', SystemException::ERR_CRITICAL);
            }
            $uploader = new FileUploader();
            $uploader->setFile($_FILES['Filedata']);
            $uploader->upload($this->getParam('base') . '/');
            $fileName = $uploader->getFileObjectName();


            $result = FileObject::createFrom($fileName, pathinfo($_FILES['Filedata']['name'], PATHINFO_FILENAME))->asArray();
        }
        catch (SystemException $e) {
            $result = array('status'=> 0, 'error'=>$e->getMessage());
        }
        $b = new JSONCustomBuilder();
        $b->setProperties($result);
        $this->setBuilder($b);
    }

    /**
     * Переименование файла/папки
     *
     * @return void
     * @access protected
     */

    protected function rename() {
        try {
            if (!isset($_POST['file'])) {

            }
            if (($fileType = key($_POST['file'])) == 'folder') {
                $file = DirectoryObject::loadFrom($_POST['file'][$fileType]);
            }
            else {
                $file = FileObject::loadFrom($_POST['file'][$fileType]);
            }

            $file->rename($_POST['name']);

            $b = new JSONCustomBuilder();
            $b->setProperty('result', true)->setProperty('mode', 'insert');
            $this->setBuilder($b);
        }
        catch (SystemException $e) {
            $message['errors'][] = array('message' =>
            $e->getMessage() . current($e->getCustomMessage()));
            $JSONResponse =
                    array_merge(array('result' => false, 'header' => $this->translate('TXT_SHIT_HAPPENS')), $message);

        }
    }
}

