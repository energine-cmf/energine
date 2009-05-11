<?php
/**
 * Содержит класс FileLibrary
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/image/components/Image.class.php');
//require_once('core/modules/share/components/DataSet.class.php');
//require_once('core/framework/DirectoryObject.class.php');
//require_once('core/framework/FileUploader.class.php');
//require_once('core/modules/share/components/JSONUploadBuilder.class.php');

/**
 * Библитека изображений
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class FileLibrary extends DataSet {
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
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setProperty('exttype', 'grid');
        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->setTitle($this->translate('TXT_'.strtoupper($this->getName())));
        //Отключили pager
        $this->setParam('recordsPerPage', false);
        if (!isset($_POST['path'])) {
            $path = self::UPLOADS_MAIN_DIR;
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
        'active'=>true,
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
        'length'=>100,
        'nullable'=>false,
        'default' => false,
        'key' => false,
        'type' => FieldDescription::FIELD_TYPE_STRING
        );
        $result['className'] = array(
        'length'=>100,
        'nullable'=>false,
        'default' => false,
        'key' => false,
        'type' => FieldDescription::FIELD_TYPE_STRING
        );
        foreach ($result as $key => $value) {
        	$result[$key]['tabName']  = $this->getTitle();
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
            if ($this->uploadsDir->getPath() != self::UPLOADS_MAIN_DIR) {
                $result = $this->uploadsDir->asArray();
                $result['upl_path'] = dirname($result['upl_path']);
                $result['upl_name'] = '...';
            }
            $this->uploadsDir->open();

            if (!empty($result)) {
                $result = array_merge(array($result), $this->uploadsDir->asArray());
            }
            else {
            	$result = $this->uploadsDir->asArray();
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
        	case FileSystemObject::IS_FOLDER:
        		$row['className'] = 'folder';
        		break;
            case FileSystemObject::IS_IMAGE:
                $row['className'] = 'image';
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
        try {
            $this->config->setCurrentMethod(self::DEFAULT_ACTION_NAME);
            $this->setBuilder(new JSONUploadBuilder());


            $this->setDataDescription($this->createDataDescription());
            $this->getBuilder()->setDataDescription($this->getDataDescription());
            $this->getBuilder()->setCurrentDir($this->uploadsDir->getPath());

            $data = $this->createData();
            if ($data instanceof Data) {
                $this->setData($data);
                $this->getBuilder()->setData($this->getData());
            }

            if ($this->getBuilder()->build()) {
                $result = $this->getBuilder()->getResult();
            }
            else {
                $result = $this->getBuilder()->getErrors();
            }

        }
        catch (Exception $e){
            $message['errors'][] = array('message'=>$e->getMessage().current($e->getCustomMessage()));
            $result = json_encode(array_merge(array('result'=>false, 'header'=>$this->translate('TXT_SHIT_HAPPENS')), $message));
        }

        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write($result);
        $this->response->commit();
    }

    /**
     * Добавляем таб
     *
     * @return DOMNode
     * @access public
     */

    public function prepare() {
        parent::prepare();
        $this->addTab($this->buildTab($this->getTitle()));
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
        if ($params = $this->getActionParams()) {
        	if (is_array($params) && !empty($params) && $params[0] == 'image-only') {
        		$this->setProperty('allowed_file_type', 'image');
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
    }

    /**
     * Сохранение данных о папке
     *
     * @return void
     * @access protected
     */

    protected function saveDir() {
        try {
            $folder = new DirectoryObject();
	        $folder->create($_POST);

            $JSONResponse = array(
            'result' => true,
            'mode' => 'insert'
            );
        }
        catch (SystemException $e){
            $message['errors'][] = array('message'=>$e->getMessage().current($e->getCustomMessage()));
            $JSONResponse = array_merge(array('result'=>false, 'header'=>$this->translate('TXT_SHIT_HAPPENS')), $message);

        }
        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write(json_encode($JSONResponse));
        $this->response->commit();
    }

    /**
     * Метод сохранения файла
     *
     * @return void
     * @access protected
     */

    protected function save() {
        try {
            $folder = new FileObject();
            $folder->create($_POST);

            $JSONResponse = array(
            'result' => true,
            'mode' => 'insert'
            );
        }
        catch (SystemException $e){
            $message['errors'][] = array('message'=>$e->getMessage().current($e->getCustomMessage()));
            $JSONResponse = array_merge(array('result'=>false, 'header'=>$this->translate('TXT_SHIT_HAPPENS')), $message);

        }
        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write(json_encode($JSONResponse));
        $this->response->commit();
    }

    /**
     * Удаление папки/файла
     *
     * @return void
     * @access protected
     */

    protected function delete() {
        try {
            if (!isset($_POST['file'])) {

            }
            if (($fileType = key($_POST['file'])) == 'folder') {
                $file = DirectoryObject::loadFrom($_POST['file'][$fileType]);
            }
            else {
                $file = FileObject::loadFrom($_POST['file'][$fileType]);
            }

            $file->delete();

            $JSONResponse = array(
            'result' => true,
            'mode' => 'delete'
            );
        }
        catch (SystemException $e){
            $message['errors'][] = array('message'=>$e->getMessage().current($e->getCustomMessage()));
            $JSONResponse = array_merge(array('result'=>false, 'header'=>$this->translate('TXT_SHIT_HAPPENS')), $message);

        }

        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write(json_encode($JSONResponse));
        $this->response->commit();

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
            $js =
            'var doc = window.parent.document;'."\n".
            'var path = doc.getElementById(\'path\');'."\n".
            'var pb = doc.getElementById(\'progress_bar\'); '."\n".
            'var iframe = doc.getElementById(\'uploader\'); '."\n";

            if (empty($_FILES) || !isset($_FILES['file'])) {
                throw new SystemException('ERR_NO_FILE', SystemException::ERR_CRITICAL);
            }

            $uploader = new FileUploader();
            $uploader->setFile($_FILES['file']);
            $uploader->upload('tmp/');
            $fileName = $uploader->getFileObjectName();
            if (FileSystemObject::getTypeInfo($fileName) == FileSystemObject::IS_IMAGE) {
            	$js .= 'iframe.preview.src = "'.$fileName.'";';
            }
            else {
            	$js .= 'iframe.preview.src = "images/icons/icon_undefined.gif";';
            }
            $js .= sprintf(
            'path.parentNode.removeChild(path);'.
            'pb.parentNode.removeChild(pb); '.
            'iframe.filename.value = "%s"; '.
            'iframe.parentNode.removeChild(iframe);',
            $_POST['path'].'/'.basename($fileName)
            );

        }
        catch (SystemException $e) {
            $js .=
            'path.parentNode.removeChild(path);'."\n".
            'pb.parentNode.removeChild(pb); '."\n".
            'alert(\''.$this->translate('TXT_SHIT_HAPPENS').': '.$e->getMessage().'\'); '.
            'iframe.parentNode.removeChild(iframe); '."\n";
        }

        $responseText = '<html><head/><body><script type="text/javascript">'.$js.'</script></body></html>';
        $response = Response::getInstance();
        $response->setHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->write($responseText);
        $response->commit();
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

            $JSONResponse = array(
            'result' => true,
            'mode' => 'insert'
            );
        }
        catch (SystemException $e){
            $message['errors'][] = array('message'=>$e->getMessage().current($e->getCustomMessage()));
            $JSONResponse = array_merge(array('result'=>false, 'header'=>$this->translate('TXT_SHIT_HAPPENS')), $message);

        }
        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write(json_encode($JSONResponse));
        $this->response->commit();
     }
}

