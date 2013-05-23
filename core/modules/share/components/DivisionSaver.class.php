<?php
/**
 * Содержит класс DivisionSaver
 *
 * @package energine
 * @subpackage share
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

 /**
  * Сохранитель данных для редактора разделов
  *
  * @package energine
  * @subpackage share
  * @author d.pavka@gmail.com
  */
 class DivisionSaver extends Saver {
    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();
    }
    /**
     * Для метода редактирования заглавной страницы
     * удаляем описание  
     * 
     * @access public
     * @return boolean
     */
    public function validate(){
    	if(!$this->getData()->getFieldByName('smap_pid')->getRowData(0)){
    		$this->getDataDescription()->removeFieldDescription(
    		  $this->getDataDescription()->getFieldDescriptionByName('smap_segment')
   		  );
    	}
    	return parent::validate();
    }
    
    /**
     * переопределенный дефолтный метод
     * 
     * @return mixed
     * @access public
     */
    public function save(){
    	//Выставляем фильтр для родительского идентификатора
        $PID = $this->getData()->getFieldByName('smap_pid')->getRowData(0);
        if (empty($PID)) {
            $PID = null;
        }
        //$this->setFilter(array('smap_pid'=>$PID));
        //Проверяем изменился ли лейаут или контент
        $prevTemplateData =
                $this->dbh->select('share_sitemap', array('smap_layout', 'smap_content'), array('smap_id' => $_POST['share_sitemap']['smap_id']));

        //Значит - редактирование
        if (is_array($prevTemplateData)) {
            list($prevTemplateData) = $prevTemplateData;
        }

        $result = parent::save();
        $smapID = ($this->getMode() ==
                QAL::INSERT) ? $result : $this->getData()->getFieldByName('smap_id')->getRowData(0);

        if ($this->getMode() !== QAL::INSERT) {
            $data = array();
            //Для апдейта - проверяем не изменился ли лейаут
            if ($prevTemplateData['smap_layout'] !=
                    $this->getData()->getFieldByName('smap_layout')->getRowData(0)) {
                $data['smap_layout_xml'] = '';
            }
            //а может изменился контент
            if ($prevTemplateData['smap_content'] !=
                    $this->getData()->getFieldByName('smap_content')->getRowData(0)) {
                $data['smap_content_xml'] = '';
            }

            if (!empty($data)) {
                $this->dbh->modify(QAL::UPDATE, 'share_sitemap', $data, array('smap_id' => $smapID));
            }
        }

        $rights = $_POST['right_id'];

        //Удаляем все предыдущие записи в таблице прав
        $this->dbh->modify(QAL::DELETE , 'share_access_level', null, array('smap_id' => $smapID));
        foreach ($rights as $groupID => $rightID) {
            if ($rightID != ACCESS_NONE) {
                $this->dbh->modify(QAL::INSERT, 'share_access_level', array('smap_id'=>$smapID, 'right_id'=>$rightID, 'group_id'=>$groupID));
            }
        }
        
        if(
            AdsManager::isActive()
            && isset($_POST[AdsManager::TABLE_NAME])
            && is_array($adsData = $_POST[AdsManager::TABLE_NAME])
        ){
            $ads = new AdsManager();
            $adsData['smap_id'] = $smapID;
            $ads->save($adsData);
        }
        
        return $result;
    }
}