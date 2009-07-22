<?php
/**
 * Содержит класс CommentForm.
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 * @copyright d.pavka@gmal.com
 * @version $Id$
 */

 /**
 * Форма добавления комментария
 *
 * @package energine
 * @subpackage misc
 * @author d.pavka
 */
class CommentForm extends DataSet {
    /**
     * Имя таблицы
     *
     * @access private
     * @var string
     */
    private $tableName = false;

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
        $params['active'] = true;
        parent::__construct($name, $module, $document,  $params);
        $this->tableName = 'aux_comments';
        $this->setDataSetAction('send', true);
        $this->setProperty('linked_component', $this->getParam('linkTo'));
        $this->setProperty('main_component', $this->getParam('mainComponent'));
        if ($linkComponent = $this->document->componentManager->getComponentByName($this->getParam('mainComponent'))) {
            if ($linkComponent->getAction() !== 'view') {
            	$this->disable();
            }
        }
    }

    /**
     * Добавляем параметр  - имя связанного компонента
     *
     * @return array
     * @access protected
     */

    protected function defineParams() {
        return array_merge(
        parent::defineParams(),
            array(
            'mainComponent' => false,
            'linkTo' => false,
            'typograph' => false,
            'linkTable' => false,
            'linkField' => false
            )
        );
    }

    /**
     * ВОзвращает имя таблицы
     *
     * @access protected
     * @return string
     */
    protected function getTableName() {
        return $this->tableName;
    }

    /**
     * Поля формы в соответствии с полями в таблице
     *
     * @access protected
     * @return array
     */
    protected function loadDataDescription() {
        $result = $this->dbh->getColumnsInfo($this->getTableName());

        unset($result['comment_time'], $result['u_id'], $result['comment_pid']);

        return $result;
    }

    /**
     * Метод обработки и сохранения данных полученных через XHR
     *
     * @access protected
     * @return void
     */
    protected function send() {
        $incomeData = $_POST[$this->getTableName()];
        $incomeData['comment_time'] = date('Y-m-d H:i:s');

        /*if ($this->getParam('typograph')) {
            $data['comment_text'] = $this->typograf('www.typograf.ru', '/webservice/', 'text='.urlencode($data['comment_text']));
        }*/

        $this->prepare();
        $this->dbh->beginTransaction();
        try {
            $result = $this->save($incomeData);
            $this->dbh->commit();
        }
        catch (SystemException $e){
            $result = array('result' => false, 'errors' =>array($e->getCustomMessage()));
            $this->dbh->rollback();
        }


        $this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->response->write(json_encode($result));
        $this->response->commit();
    }

    /**
     * Сохранение даных
     *
     * @access protected
     * @return mixed
     */
    protected function save($clearData) {
        $saver = new Saver();
        $data = new Data();
        $data->load(array($clearData));
        $saver->setData($data);

        $fd = new FieldDescription('comment_time');
        $fd->setType(FieldDescription::FIELD_TYPE_DATETIME);
        $fd->addProperty('tableName', $this->getTableName());

        $this->getDataDescription()->addFieldDescription($fd);
        $saver->setDataDescription($this->getDataDescription());

        if($saver->validate()){
            $saver->save();
            $this->dbh->modify(QAL::INSERT , $this->getParam('linkTable'), array($this->getParam('linkField') =>(isset($_POST['link_id']))?$_POST['link_id']:$this->document->getID(), 'comment_id' => $saver->getResult(), 'lang_id' => $this->document->getLang()));

            $result['result'] = true;
            $result['data'] = $clearData;
        }
        else {
        	throw new SystemException($this->translate('ERR_SAVE'), SystemException::ERR_WARNING, $saver->getErrors());
        }

        return $result;
    }

    private function typograf($host,$script,$data){
        $opts = array(
        'http'=>array(
            'method' => 'POST',
            'user_agent' => 'Energine CMS script',
            'content' => $data,
            'header'=>
                "Content-type: application/x-www-form-urlencoded\n".
                "Content-length: " . strlen($data) . "\n".
                "Connection: close\n\n"
            )
        );
        $buf = file_get_contents('http://'.$host.$script, false, stream_context_create($opts));
    	return $buf;
    }


}