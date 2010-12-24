<?php
/**
 * Содержит класс TextBlock.
 *
 * @package energine
 * @subpackage share
 * @author 1m.dm
 * @copyright Energine 2006
 */

/**
 * Текстовый блок.
 *
 * @package energine
 * @subpackage share
 * @author 1m.dm
 * @final
 */
final class TextBlock extends DataSet {
    /**
     * Компонент менеджера изображений
     *
     * @var ImageManager
     * @access private
     */
    private $imageManager;

    /**
     * Компонент библиотеки файлов
     *
     * @var FileLibrary
     * @access private
     */
    private $fileLibrary;

    /**
     * Имя основной таблицы
     *
     * @var string
     * @access private
     */
    private $tableName;

    /**
     * Идентификатор текстового блока
     *
     * @var int
     * @access private
     */
    private $id = false;

    /**
     * Содержимое текстового блока
     *
     * @var string
     * @access private
     */
    private $content = '';

    /**
     * Находится ли страница в режиме редактирования содержимого
     *
     * @var boolean
     * @access private
     */
    private $isEditable;

    /**
     * Конструктор класса
     *
     * @param string
     * @param string
     * @return void
     * @access public
     */
    public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        /**
         * @todo Не забыть убрать $_REQUEST или переделать чтобы для режима отладки  -_REQUEST а так  - _POST
         *
         */
        $this->isEditable = $this->document->isEditable();
        $this->tableName = 'share_textblocks';
        if ($this->isEditable) {
        	$this->addWYSIWYGTranslations();
        	//выставляем свойство указывающее на то что блок находится в режиме редактирования
            $this->setProperty('editable', 'editable');
        }
    }

    /**
     * Добавлен параметр num
     *
     * @return array
     * @access protected
     */

    protected function defineParams() {
        return array_merge(
        parent::defineParams(),
        array(
            'num' => 1,
            'active' => true,
        )
        );
    }

    /**
     * Возвращает идентификатор текстового блока по переданному идентификатору документа и порядковому номеру
     *
     * @param int идентификатор документа
     * @param string идентификатор текстового блока
     * @return int
     * @access protected
     */

    protected function getTextBlockID($smapID , $num) {
        $smapID = (empty($smapID))?null:$smapID;
        $result = false;
        $res = $this->dbh->select($this->tableName, array('tb_id'), array('smap_id'=>$smapID, 'tb_num'=>$num));
        if (is_array($res)) {
            $result = simplifyDBResult($res, 'tb_id', true);
        }
        return $result;
    }

    /**
	 * Загрузка данных
	 *
	 * @return void
	 * @access protected
	 */
    protected function main() {
        /**
         * @todo Тут вообще получается ограничение, что num лейаутного текстового блока не должен быть цифрой
         */

        if (intval($this->getParam('num'))!==0) {
            $docID = $this->document->getID();
        }
        else {
            $docID = '';
        }
        
        $res = $this->dbh->selectRequest(
            'SELECT st.tb_id as id, stt.tb_content as content '.
            'FROM `share_textblocks`  st '.
            'LEFT JOIN share_textblocks_translation stt ON st.tb_id = stt.tb_id and lang_id = %s '.
            'WHERE smap_id '.(($docID)?' = '.$docID:' IS NULL ').' AND tb_num = %s ',
            $this->document->getLang(),
            $this->getParam('num')
        );
        
        if(is_array($res)){
            list($res) = $res;
            $this->id = $res['id'];
            $this->content = $res['content'];	
        }

        $this->setProperty('num', $this->getParam('num'));
        $this->prepare();
    }

    /**
     * Переопределен метод создания объекта мета данных
     *
     * @return DataDescription
     * @access protected
     */

    protected function createDataDescription() {
        $dataDescr = new DataDescription();
        $fieldDescr = new FieldDescription($this->getName());
        $fieldDescr->setType(FieldDescription::FIELD_TYPE_HTML_BLOCK);
        $dataDescr->addFieldDescription($fieldDescr);

        return $dataDescr;
    }

    /**
     * Создаем свои данные
     *
     * @return Data
     * @access protected
     */

    protected function createData() {
        $data = new Data();
        $field = new Field($this->getName());
        $field->setData($this->getContent());
        $data->addField($field);

        return $data;
    }

    /**
     * Возвращает содержимое текстового блока
     *
     * @return string
     * @access protected
     */

    protected function getContent() {
        return $this->content;
    }

    /**
     * Возвращает идентификатор текстового блока
     *
     * @return int
     * @access protected
     */

    protected function getID() {
        return $this->id;
    }

    /**
     * Создание панели инструментов
     *
     * @return void
     * @access protected
     */

    protected function createToolbar() {
        return false;
    }

    /**
     * Строит JS описание
     *
     * @return DOMNode
     * @access protected
     */

    protected function buildJS() {
        $result = false;
        if ($this->isEditable) {
            $result = parent::buildJS();
        }

        return $result;
    }

    /**
     * Сохранение данных
     *
     * @return void
     * @access protected
     */

    protected function save() {
        $this->dbh->beginTransaction();
        try {

            if (!isset($_POST['data']) && !isset($_POST['num'])) {
                throw new SystemException('ERR_DEV_NO_DATA', SystemException::ERR_DEVELOPER );
            }
            $langID = $this->document->getLang();
            $docID = (isset($_POST['ID']))?$_POST['ID']:'';
            //пытаемся определить есть ли у нас запись о содержимом блока в основной таблице

            $tbID = $this->getTextBlockID($docID, $_POST['num']);
            $result = DataSet::cleanupHTML($_POST['data']);
            //$result = $_POST['data'];

            if (!$tbID) {
                $tbID = $this->dbh->modify(QAL::INSERT, 'share_textblocks', array('smap_id'=>$docID, 'tb_num'=>$_POST['num']));
            }
            $tableName = $this->tableName.'_translation';

            $res = $this->dbh->select($tableName, array('tb_id'), array('tb_id'=>$tbID, 'lang_id'=>$langID));
            //если есть запись в таблице переводов - апдейтим
            if (is_array($res)) {

                $res = $this->dbh->modify(QAL::UPDATE, $tableName, array('tb_content'=>$result), array('tb_id'=>$tbID, 'lang_id'=>$langID));
            }
            elseif ($res === true) {
                //если нет - вставляем
                $res = $this->dbh->modify(QAL::INSERT, $tableName, array('tb_content'=>$result, 'tb_id'=>$tbID, 'lang_id'=>$langID));
            }

            $this->dbh->commit();
        }
        catch (Exception $e) {
            $this->dbh->rollback();
            $result = $e->getMessage();
        }

        $this->response->setHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->response->write($result);
        $this->response->commit();
    }

    protected function source() {
        $this->source = $this->document->componentManager->createComponent('textblocksource', 'share', 'TextBlockSource', null);
        $this->source->run();
    }

    /**
     * Выводит компонент менеджер изображений
     *
     * @return void
     * @access protected
     */
    protected function imageManager() {
        $this->imageManager  = $this->document->componentManager->createComponent('imagemanager', 'share', 'ImageManager', null);
        $this->imageManager->run();
    }

    /**
     * Выводит компонент библиотеки файлов
     *
     * @return void
     * @access protected
     */
     protected function fileLibrary() {
        $this->request->setPathOffset($this->request->getPathOffset() + 1);
        $this->fileLibrary = $this->document->componentManager->createComponent('filelibrary', 'share', 'FileLibrary', null, false);
        $this->fileLibrary->run();
     }

    /**
     * Для метода вывода редактора изображений вызывает построитель редактора изоборажений во всех других случаях - свой
     *
     * @return DOMNode
     * @access public
     */

    public function build() {
        switch ($this->getAction()) {
        	case 'imageManager':
        	    $result = $this->imageManager->build();
        		break;
        	case 'fileLibrary':
        	    $result = $this->fileLibrary->build();
        	    break;
            case 'source':
                $result = $this->source->build();
                break;
        	default:
                $result = parent::build();
        		break;
        }
        return $result;
    }

}

