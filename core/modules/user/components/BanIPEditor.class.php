<?php
/**
 * Содержит класс BanIPEditor
 *
 * @package energine
 * @subpackage share
 * @author spacelord
 * @copyright Energine 2010
 * @version $Id:
 */


/**
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class BanIPEditor extends Grid {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
	public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->setTableName(BanIPSaver::TABLE_NAME);
        $this->setOrder(array('ban_ip_end_date'=> QAL::ASC));
        $this->setSaver(new BanIPSaver());
	}

    protected function loadData(){
        if($this->getState() == 'getRawData'){
            $this->applyUserFilter();
            $this->applyUserSort();
            if ($this->pager) {
                // pager существует -- загружаем только часть данных, текущую страницу
                $this->setLimit($this->pager->getLimit());
            }
            $result = $this->dbh->select($this->getTableName(), array('ban_ip_id', 'INET_NTOA(ban_ip) as ban_ip', 'ban_ip_end_date'), $this->getFilter(), $this->getOrder(), $this->getLimit());
        }
        else {
            $result = parent::loadData();
        }
        return $result;
    }

    protected function add(){
        parent::add();
        $this->getDataDescription()->getFieldDescriptionByName('ban_ip_end_date')->setType(FieldDescription::FIELD_TYPE_SELECT);
        $fdValues = BanDateTransform::getFormattedFDValues();
        foreach($fd = $this->getDataDescription()->getFieldDescriptionByName('ban_ip_end_date') as $key=>$value){
            $fd = $this->getDataDescription()->getFieldDescriptionByName('ban_ip_end_date');
            $fd->loadAvailableValues($fdValues,'date_key','date_value');
        }
    }

    protected function edit(){
        parent::edit();
        $this->getDataDescription()->getFieldDescriptionByName('ban_ip')->setMode(FieldDescription::FIELD_MODE_READ);
        $banIPField = $this->getData()->getFieldByName('ban_ip');
        $banIPField->setRowData(0, long2ip($banIPField->getRowData(0)));
    }

    protected function save(){
        $b = new JSONCustomBuilder();
        $this->setBuilder($b);

        if(isset($_POST['delete_ban']) && intval($_POST['delete_ban'])==1 && isset($_POST[$this->getTableName()])){
            $this->dbh->modify(
                QAL::DELETE,
                $this->getTableName(),
                null,
                array('ban_ip_id' => $_POST[$this->getTableName()]['ban_ip_id'])
            );
        } else {
            $banDate = BanDateTransform::getFormattedBanDate($_POST[$this->getTableName()]['ban_ip_end_date']);
            if($banDate){
                $_POST[$this->getTableName()]['ban_ip_end_date'] = $banDate;
                $this->dbh->modify(QAL::DELETE, 'share_session', null, array('session_ip' => ip2long($_POST[$this->getTableName()]['ban_ip'])));
            }
            $this->saveData();
        }
        $b->setProperties(array(
            'data'=>$_POST[$this->getTableName()],
            'result' => true,
            'mode' => 'save'
        ));
    }

    protected function banUserIP(){
        $UID = $this->getStateParams();
        $UID = $UID[0];
        if(!intval($UID)){
            throw new SystemException('Invalid UserID');
        }
        $UIP = simplifyDBResult(
            $this->dbh->select('share_session', 'INET_NTOA(session_ip) as session_ip', array('u_id' => $UID), array('session_created' => QAL::DESC), 1),
            'session_ip',
            true
        );
        if(!$UIP){
            throw new SystemException('No session data about user.');
        }
        if(strlen($UIP)<7){
            throw new SystemException('Not set or invalid IP address.');
        }

        $IPAlreadyBanned = simplifyDBResult(
            $this->dbh->select($this->getTableName(), 'ban_ip_id', array('ban_ip' => $UIP)),
            'ban_ip_id',
            true
        );
        if(!$IPAlreadyBanned){
            $this->add();
            $this->getData()->getFieldByName('ban_ip')->setRowData(0,$UIP);
            $this->getDataDescription()->getFieldDescriptionByName('ban_ip')->setMode(FieldDescription::FIELD_MODE_READ);

        } else {
            $this->setStateParam('ban_ip_id',$IPAlreadyBanned);
            if (!$this->config->isEmpty()) {
                $this->config->setCurrentState('editBanUserIP');
            }
            $this->editBanUserIP();
        }
    }

    protected function editBanUserIP(){
        $ap = $this->getStateParams(true);
        $BID = $ap['ban_ip_id'];
        if(!$BID){
            throw new SystemException('ERR_NOT_VALID_BAN_IP_ID');
        }
        $dbRes = $this->dbh->select($this->getTableName(), 'INET_NTOA(ban_ip) as ban_ip, ban_ip_end_date', array('ban_ip_id' => $BID));
        if(!$dbRes || !is_array($dbRes)){
            throw new SystemException('ERR_NO_BAN_IP_FOUND');
        }
        $this->setBuilder(new Builder());
        $this->setData(new Data());
        $this->setDataDescription(new DataDescription());
        $this->setType(self::COMPONENT_TYPE_FORM);



        $f = new Field('ban_ip_id');
        $f->setData($BID);
        $this->getData()->addField($f);

        $fd = new FieldDescription('ban_ip_id');
        $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        $fd->setProperty('tabName','TXT_USER_BAN_EDITOR');
        $fd->setProperty('tableName', $this->getTableName());
        $this->getDataDescription()->addFieldDescription($fd);

        $f = new Field('ban_ip');
        $f->setData($dbRes[0]['ban_ip']);
        $this->getData()->addField($f);

        $fd = new FieldDescription('ban_ip');
        $fd->setType(FieldDescription::FIELD_TYPE_STRING);
        $fd->setMode(FieldDescription::FIELD_MODE_READ);
        $fd->setProperty('tabName','TXT_USER_BAN_EDITOR');
        $fd->setProperty('tableName',$this->getTableName());
        $this->getDataDescription()->addFieldDescription($fd);

        $f = new Field('ban_ip_end_date');
        $f->setData($dbRes[0]['ban_ip_end_date']);
        $this->getData()->addField($f);

        $fd = new FieldDescription('ban_ip_end_date');
        $fd->setType(FieldDescription::FIELD_TYPE_DATE);
        $fd->setProperty('tabName','TXT_USER_BAN_EDITOR');
        $fd->setProperty('tableName',$this->getTableName());
        $this->getDataDescription()->addFieldDescription($fd);

        $f = new Field('delete_ban');
        $this->getData()->addField($f);

        $fd = new FieldDescription('delete_ban');
        $fd->setType(FieldDescription::FIELD_TYPE_BOOL);
        $fd->setProperty('tabName','TXT_USER_BAN_EDITOR');
        $this->getDataDescription()->addFieldDescription($fd);

        $this->js = $this->buildJS();
        $this->addToolbar($this->createToolbar());
    }







}