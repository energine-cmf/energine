<?php

/**
 * Содержит класс DivisionEditor
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//Sitemap 

/**
 * Редактор раздело
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @final
 */
final class DivisionEditor extends Grid {
	const TMPL_CONTENT = 'content';
	const TMPL_LAYOUT = 'layout';

	/**
	 * Редактор переводов
	 *
	 * @var TranslationEditor
	 * @access private
	 */
	private $transEditor;
	/**
	 * Редактор пользователей
	 *
	 * @var UserEditor
	 * @access private
	 */
	private $userEditor;
	/**
	 * Редактор ролей
	 *
	 * @var RoleEditor
	 * @access private
	 */
	private $roleEditor;
	/**
	 * Редактор языков
	 *
	 * @var LanguageEditor
	 * @access private
	 */
	private $langEditor;

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct($name, $module, Document $document,  array $params = null) {
		parent::__construct($name, $module, $document,  $params);
		$this->setTableName('share_sitemap');
		$this->setTitle($this->translate('TXT_DIVISION_EDITOR'));
		//$this->setOrder('smap_order_num', QAL::ASC);

		$this->setParam('recordsPerPage', false);
		$this->setOrderColumn('smap_order_num');
	}

	/**
	 * Строит вкладку прав
	 *
	 * @param int идентификатор раздела(при создании раздела используем родительский идентификатор)
	 * @return DOMNode
	 * @access private
	 */

	private function buildRightsTab($id) {
		$builder = new SimpleBuilder($this->getTitle());

		//получаем информацию о всех группах имеющихся в системе
		$groups = $this->dbh->select('user_groups', array('group_id', 'group_name'));
		$groups = convertDBResult($groups, 'group_id');
		//создаем матриц
		//название группы/перечень прав
		foreach (array_keys($groups) as $groupID) {
			$res[] = array('right_id'=>0, 'group_id'=>$groupID);
		}

		$resultData = new Data();
		$resultData->load($res);
		$builder->setData($resultData);

		$rightsField = $resultData->getFieldByName('right_id');
		$groupsField = $resultData->getFieldByName('group_id');


		//создаем переменную содержащую идентификторы групп в которые входит пользователь
		$data = $this->dbh->select('share_access_level', array('group_id', 'right_id'), array('smap_id'=>$id));

		if(is_array($data)) {
			$data = convertDBResult($data, 'group_id', true);

			for ($i=0; $i<$resultData->getRowCount(); $i++) {

				//если установлены права для группы  - изменяем в объекте данных
				if (isset($data[$groupsField->getRowData($i)])) {
					$rightsField->setRowData($i, $data[$groupsField->getRowData($i)]['right_id']);
				}

				$groupsField->setRowProperty($i, 'group_id', $groupsField->getRowData($i));
			}
		}


		for ($i=0; $i<$resultData->getRowCount(); $i++) {
			$groupsField->setRowProperty($i, 'group_id', $groupsField->getRowData($i));
			$groupsField->setRowData($i, $groups[$groupsField->getRowData($i)]['group_name']);
		}

		$resultDD = new DataDescription();
		$fd = new FieldDescription('group_id');
		$fd->setSystemType(FieldDescription::FIELD_TYPE_STRING);
		$fd->setMode(FieldDescription::FIELD_MODE_READ);
		$fd->setLength(30);
		$resultDD->addFieldDescription($fd);

		$fd = new FieldDescription('right_id');
		$fd->setSystemType(FieldDescription::FIELD_TYPE_SELECT);
		$data = $this->dbh->select('user_group_rights', array('right_id', 'right_const as right_name'));
		$data = array_map(create_function('$a', '$a["right_name"] = DBWorker::_translate("TXT_".$a["right_name"]); return $a;'), $data);
		$data[] = array('right_id'=>0, 'right_name'=>$this->translate('TXT_NO_RIGHTS'));

		$fd->loadAvailableValues($data, 'right_id', 'right_name');
		$resultDD->addFieldDescription($fd);

		$builder->setDataDescription($resultDD);
		$builder->build();
		
		$field = new Field('page_rights');
            for ($i = 0; $i < count(Language::getInstance()->getLanguages()); $i++) {
                $field->addRowData(
                $builder->getResult()
                );
            }
            $this->getData()->addField($field);
	}

	/**
	 * Для setRole создаем свое описание данных
	 * Для поля smap_pid формируется Дерево разделов
	 *
	 * @return DataDescription
	 * @access protected
	 */

	protected function createDataDescription() {
		$result = parent::createDataDescription();

		//для редактирования и добавления нужно сформировать "красивое дерево разделов"
		if (in_array($this->getAction(), array('add', 'edit'))) {
			$fd = $result->getFieldDescriptionByName('smap_pid');
			$fd->setType(FieldDescription::FIELD_TYPE_STRING);
			//$fd->setMode(FieldDescription::FIELD_MODE_READ);
			
			$result->getFieldDescriptionByName('smap_name')->removeProperty('nullable');

			foreach (array(self::TMPL_CONTENT, self::TMPL_LAYOUT) as $type)
			if($f = $result->getFieldDescriptionByName('smap_'.$type)){
				$f->setType(FieldDescription::FIELD_TYPE_SELECT);
				$f->loadAvailableValues(
				$this->loadTemplateData($type),
                'key','value');                         
			}
		}
		else {
			//Для режима списка нам нужно выводить не значение а ключ
			if ($this->getType() == self::COMPONENT_TYPE_LIST) {
				$smapPIDFieldDescription = $result->getFieldDescriptionByName('smap_pid');
				if ($smapPIDFieldDescription) {
					$smapPIDFieldDescription->setType(FieldDescription::FIELD_TYPE_INT);
				}
			}
			if ($this->getAction() == 'getRawData') {
				$field = new FieldDescription('smap_segment');
				$field->setType(FieldDescription::FIELD_TYPE_STRING);
				$field->setProperty('tableName', $this->getTableName());
				$result->addFieldDescription($field);
			}
		}
		return $result;
	}

	private function loadTemplateData($type){
		$result = array();
		$dirPath = 'templates/'.$type.'/';
		foreach(
		array_merge(
		glob($dirPath."*/*.".$type.".xml"),
		glob($dirPath."*.".$type.".xml")
		) as $path){
			$path = str_replace($dirPath, '', $path);
			$result[] = array(
               'key' => $path,
               'value' => $path 
			);
		}
		return $result;
	}

	/**
	 * Добавляет данные об УРЛ
	 *
	 * @return array
	 * @access protected
	 */

	protected function loadData() {
		$result = parent::loadData();
		if($result && $this->getAction() == 'getRawData') {
			//Используется GLOBALS поскольку нет другой возможности передать 
			//в runtime created function посторонее значение 
			/*$GLOBALS['__SMAP2ICONS'] = convertDBResult($this->dbh->selectRequest('
			SELECT DISTINCT t.tmpl_icon, s.smap_id FROM `share_sitemap` s
			LEFT JOIN share_templates t ON t.tmpl_id=s.tmpl_id
			'), 'smap_id', true);

			*/
			$params  = $this->getActionParams(true);
			$result = array_map(
			create_function(
                    '$val', 
                    '
                    $val["smap_segment"] = SiteMap::getInstance('.$params['site_id'].')->getURLByID($val["smap_id"]);
                    '.(($this->getDataDescription()->getFieldDescriptionByName('site'))?'$val["site"] = SiteManager::getInstance()->getSiteByID('.$params['site_id'].')->base;':'').'
                    return $val;
                    '
                    )
                    , $result);

                    //unset($GLOBALS['__SMAP2ICONS']);
		}

		return $result;
	}

	protected function getRawData($baseMethod = self::DEFAULT_ACTION_NAME){
		$params = $this->getActionParams(true);
		$this->setFilter(array('site_id' => $params['site_id']));
		try {
			$this->setParam('onlyCurrentLang', true);
			$this->config->setCurrentMethod($baseMethod);
			$this->setBuilder(new JSONDivBuilder());

			$this->setDataDescription($this->createDataDescription());

			$this->getBuilder()->setDocumentId($this->document->getID());
			$this->getBuilder()->setDataDescription($this->getDataDescription());

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
			$message['errors'][] = array('message'=>$e->getMessage()."\n".current($e->getCustomMessage())."\nFile: ".$e->getFile()."\n Line:".$e->getLine()."\n".$e->getTraceAsString());
			$result = json_encode(array_merge(array('result'=>false, 'header'=>$this->translate('TXT_ERROR')), $message));
		}
		$this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
		$this->response->write($result);
		$this->response->commit();
	}

	/**
	 * Подменяем построитель для метода setPageRights
	 *
	 * @return Builder
	 * @access protected
	 */

	protected function prepare() {
		parent::prepare();
		if(in_array($this->getAction(), array('add', 'edit'))){
			$this->addTranslation('ERR_NO_DIV_NAME');
			list($pageID) = $this->getActionParams();
			$this->getDataDescription()->getFieldDescriptionByName('smap_pid')->setProperty('base', SiteManager::getInstance()->getSiteByPage($pageID)->base);
		}
	}

	/**
	 * Переопределенный внешний метод сохранения
	 * добавлено значение урла страницы
	 * Вызывает внутренний метод сохранения saveData(), который и производит собственно все действия
	 *
	 * @return void
	 * @access protected
	 */

	protected function save() {
		$this->setSaver(
		  new DivisionSaver()
		);
		$transactionStarted = $this->dbh->beginTransaction();
		try {
			$result = $this->saveData();
			if (is_int($result)) {
				$mode = 'insert';
				$id = $result;
				/*Тут пришлось пойти на извращаения для получения УРЛа страницы, поскольку новосозданная страница еще не присоединена к дереву*/
				//$smapPID = simplifyDBResult($this->dbh->select('share_sitemap', 'smap_pid', array('smap_id'=>$id)), 'smap_pid', true);
				$smapPID = $this->getSaver()->getData()->getFieldByName('smap_pid')->getRowData(0);
				$url = $_POST[$this->getTableName()]['smap_segment'].'/';
				if ($smapPID) {
					$url = Sitemap::getInstance(
					   SiteManager::getInstance()->getSiteByPage($smapPID)->id
					)->getURLByID($smapPID).$url;
				}
			}
			else {
				$mode = 'update';
				$id = $this->getFilter();
				$id = $id['smap_id'];
				$url = Sitemap::getInstance(SiteManager::getInstance()->getSiteByPage($id)->id)->getURLByID($id);
			}


			$transactionStarted = !($this->dbh->commit());

			$JSONResponse = array(
            'result' => true,
            'url' => $url,
            'mode' => $mode
			);
		}
		catch (FormException $formError) {
			$this->dbh->rollback();
			//Формируем JS массив ошибок который будет разбираться на клиент
			$errors = $this->saver->getErrors();
			foreach ($errors as $errorFieldName) {
				$message['errors'][] = array(
                'field'=>$this->translate('FIELD_'.strtoupper($errorFieldName)),
                'message'=>$this->translate($this->saver->getDataDescription()->getFieldDescriptionByName($errorFieldName)->getPropertyValue('message'))
				);
			}
			$JSONResponse = array_merge(array('result'=>false, 'header'=>$this->translate('TXT_SHIT_HAPPENS')), $message);
		}
		catch (SystemException $e){
			if ($transactionStarted) {
				$this->dbh->rollback();
			}
			$message['errors'][] = array('message'=>$e->getMessage().current($e->getCustomMessage()).$e->getTraceAsString());
			$JSONResponse = array_merge(array('result'=>false, 'header'=>$this->translate('TXT_SHIT_HAPPENS')), $message);

		}
		$this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
		$this->response->write(json_encode($JSONResponse));
		$this->response->commit();
	}

	protected function add() {
		parent::add();
        //@todo Тут пришлось пойти на извращение
        $actionParams = $this->getActionParams(true);
        $this->buildRightsTab($actionParams['pid']);
        
        $siteID = SiteManager::getInstance()->getSiteByPage($actionParams['pid'])->id;
		$this->addAttFilesField('share_sitemap_uploads');
		$sitemap = Sitemap::getInstance($siteID);
		
		$this->getData()->getFieldByName('site_id')->setData($siteID, true);
		
		$field = $this->getData()->getFieldByName('smap_pid');
		$smapSegment = $sitemap->getURLByID($actionParams['pid']);
	       
		$res =
		$this->dbh->select(
		$this->getTranslationTableName(),
		array('smap_name'),
		array(
                     'smap_id' => $actionParams['pid'], 
                     'lang_id' => $this->document->getLang()));
		if (!empty($res)) {
			$name = simplifyDBResult($res, 'smap_name', true);
			for ($i = 0; $i < count(Language::getInstance()->getLanguages()); $i++) {
				$field->setRowData($i, $actionParams['pid']);
				$field->setRowProperty($i, 'data_name', $name);
				$field->setRowProperty($i, 'segment', $smapSegment);
			}
		}
		if($field = $this->getDataDescription()->getFieldDescriptionByName('tags')){
            //$field->setProperty('nullable', 'nullable');
            $field->removeProperty('pattern');
            $field->removeProperty('message');
		}
		
	}

	protected function edit() {
		parent::edit();
		$this->buildRightsTab($smapID = $this->getData()->getFieldByName('smap_id')->getRowData(0));
		$this->addAttFilesField(
                'share_sitemap_uploads',
		$this->dbh->selectRequest('
                    SELECT files.upl_id, upl_path, upl_name
                    FROM `share_sitemap_uploads` s2f
                    LEFT JOIN `share_uploads` files ON s2f.upl_id=files.upl_id
                    WHERE smap_id = %s
                ', $smapID)
		);
		//Выводим УРЛ в поле сегмента 
		$field = $this->getData()->getFieldByName('smap_pid');

		$smapSegment = '';
		if($field->getRowData(0) !== null) {
			$smapSegment = Sitemap::getInstance($this->getData()->getFieldByName('site_id')->getRowData(0))->getURLByID($field->getRowData(0));
		}
		else{
			$this->getDataDescription()->getFieldDescriptionByName('smap_pid')->setMode(FieldDescription::FIELD_MODE_READ);	
			$this->getDataDescription()->getFieldDescriptionByName('smap_pid')->setType(FieldDescription::FIELD_TYPE_HIDDEN);
			foreach(
			array(
	             'smap_segment',
	             //'smap_pid',
	             'smap_redirect_url'
	             )
	             as
	             $fieldName
	             )
	             {
	             	$this->getDataDescription()->removeFieldDescription(
	             	$this->getDataDescription()->getFieldDescriptionByName($fieldName)
	             	);
	             }
		}
		$smapName = simplifyDBResult($this->dbh->select($this->getTranslationTableName(), array('smap_name'), array('smap_id' => $field->getRowData(0), 'lang_id' => $this->document->getLang())), 'smap_name', true);

		for ($i = 0; $i < ($langs = count(Language::getInstance()->getLanguages())); $i++) {
			$field->setRowProperty($i, 'data_name', $smapName);
			$field->setRowProperty($i, 'segment', $smapSegment);
		}
		//@todo поправить 
		$field = $this->getData()->getFieldByName('smap_redirect_url');
		if($field->getRowData(0)){
			$field->setRowData(0, SiteManager::getInstance()->getCurrentSite()->base.$field->getRowData(0));
		}
		if($field = $this->getDataDescription()->getFieldDescriptionByName('tags')){
			//$field->setProperty('nullable', 'nullable');
			$field->removeProperty('pattern');
			$field->removeProperty('message');
			$field = new Field('tags');
			$fieldData = implode(TagManager::TAG_SEPARATOR.' ', 
                  array_keys(TagManager::getInstance()->pull($smapID, 'share_sitemap_tags'))
            ); 
			for($i=0; $i<$langs; $i++){
			 $field->setRowData($i, $fieldData);	
			}
		    
			$this->getData()->addField($field);
		}
		
	}

	/**
	 * Добавлен перевод для корня дерева разделов
	 *
	 * @return void
	 * @access protected
	 */

	protected function main() {
		parent::main();
		$params = $this->getActionParams(true);

		if($params){
			$siteID = $params['site_id'];
		}
		else {
			$siteID = SiteManager::getInstance()->getCurrentSite()->id;
		}

		$this->setProperty('site', $siteID);
		$this->setFilter(array('site_id' => $siteID));
		$this->addTranslation('TXT_DIVISIONS');
	}


	/**
	 * Не позволяет удалить раздел по умолчанию а также системные разделы
	 *
	 * @param int
	 * @return void
	 * @access protected
	 */

	protected function deleteData($id) {
		$res = $this->dbh->select('share_sitemap', array('smap_pid'), array($this->getPK()=>$id));
		if (!is_array($res))
		throw new SystemException('ERR_DEV_BAD_DATA', SystemException::ERR_CRITICAL);

		list($res) = $res;

		$PID = $res['smap_pid'];
		if (empty($PID)) {
			$PID = null;
		}

		$this->setFilter(array('smap_pid'=>$PID));

		parent::deleteData($id);
	}


	/**
	 * Для метода show слешатся имена разделов
	 *
	 * @return DOMNode
	 * @access public
	 */

	public function build() {
		switch ($this->getAction()) {
			case 'showPageToolbar':
				$result = false;
				// вызываем родительский метод построения
				$result = Component::build();
				$result->documentElement->appendChild($result->importNode($this->buildJS(), true));
				$tbs = $this->getToolbar();
				if(!empty($tbs))
				foreach($tbs as $toolbar){
					$result->documentElement->appendChild($result->importNode($toolbar->build(), true));
				}
				break;
			case 'showTransEditor':
				$result = $this->transEditor->build();
				break;
			case 'showUserEditor':
				$result = $this->userEditor->build();
				break;
			case 'showRoleEditor':
				$result = $this->roleEditor->build();
				break;
			case 'showLangEditor':
				$result = $this->langEditor->build();
				break;
			default:
				$result = parent::build();
				break;
		}

		return $result;
	}

	/**
	 * Метод возвращает свойства узла
	 *
	 * @return void
	 * @access protected
	 */

	protected function getProperties() {
		try {
			$id = $_POST['id'];
			$langID = $_POST['languageID'];
			if (!$this->recordExists($id)) {
				throw new SystemException('ERR_404', SystemException::ERR_404);
			}

			$this->setFilter(array('smap_id'=>$id, 'lang_id'=>$langID));
			$result = $this->dbh->selectRequest(
                'SELECT smap_name, smap_pid, smap_order_num '.
                ' FROM share_sitemap s'.
                ' LEFT JOIN share_sitemap_translation st ON s.smap_id = st.smap_id'.
                ' WHERE s.smap_id = '.$id.' AND lang_id = '.$langID
			);
			list($result) = $result;
			$JSONResponse = array(
            'result'=>true,
            'data'=>$result,
			);
		}
		catch (SystemException $e){
			$JSONResponse = $this->generateError($e->getCode(), $e->getMessage());

		}
		$this->response->setHeader('Content-Type', 'text/javascript; charset=utf-8');
		$this->response->write(json_encode($JSONResponse));
		$this->response->commit();
	}

	/**
	 * Выводит панель управления страницей
	 *
	 * @return void
	 * @access protected
	 */

	protected function showPageToolbar() {
		if (!$this->config->getCurrentMethodConfig()) {
			throw new SystemException('ERR_DEV_TOOLBAR_MUST_HAVE_CONFIG', SystemException::ERR_DEVELOPER);
		}
		$this->addToolbar($this->createToolbar());
		if($this->document->isEditable())
		$this->getToolbar('main_toolbar')->getControlByID('editMode')->setState(1);
	}

	/**
	 * Селектор
	 *
	 * @return void
	 * @access protected
	 */

	protected function selector() {
		$this->addTranslation('TXT_DIVISIONS');
		$this->prepare();
		$params = $this->getActionParams(true);

        if($params){
            $siteID = $params['site_id'];
        }
        else {
            $siteID = SiteManager::getInstance()->getCurrentSite()->id;
        }

        $this->setProperty('site', $siteID);
        $this->setFilter(array('site_id' => $siteID));
        
	}


	/**
	 * Вывод редактора переводов
	 *
	 * @return void
	 * @access protected
	 */

	protected function showTransEditor() {
		$this->request->setPathOffset($this->request->getPathOffset() + 1);
		$this->transEditor = $this->document->componentManager->createComponent('transEditor', 'share', 'TranslationEditor', null);
		$this->transEditor->run();
	}
	/**
	 * Вывод редактора пользователей
	 *
	 * @return void
	 * @access protected
	 */

	protected function showUserEditor() {
		$this->request->setPathOffset($this->request->getPathOffset() + 1);
		$this->userEditor = $this->document->componentManager->createComponent('userEditor', 'user', 'UserEditor', null);
		$this->userEditor->run();
	}
	/**
	 * Вывод редактора ролей
	 *
	 * @return void
	 * @access protected
	 */

	protected function showRoleEditor() {
		$this->request->setPathOffset($this->request->getPathOffset() + 1);
		$this->roleEditor = $this->document->componentManager->createComponent('roleEditor', 'user', 'RoleEditor', null);
		$this->roleEditor->run();
	}
	/**
	 * Вывод редактора языков
	 *
	 * @return void
	 * @access protected
	 */

	protected function showLangEditor() {
		$this->request->setPathOffset($this->request->getPathOffset() + 1);
		$this->langEditor = $this->document->componentManager->createComponent('langEditor', 'share', 'LanguageEditor', null);
		$this->langEditor->run();
	}
	/**
	 * Изменяет порядок следования
	 *
	 * @param string
	 * @return JSON String
	 * @access protected
	 */

	protected function changeOrder($direction) {
		try {
			$id = $this->getActionParams();
			list($id) = $id;
			if (!$this->recordExists($id)) {
				throw new SystemException('ERR_404', SystemException::ERR_404);
			}
			$order = $this->getOrder();
			if ($direction == Grid::DIR_UP) {
				$order[key($order)] = ($order[key($order)] == QAL::ASC)?QAL::DESC:QAL::ASC;
			}

			//Определяем PID
			$res = $this->dbh->select($this->getTableName(), array('smap_pid'), array('smap_id' => $id));
			$PID = simplifyDBResult($res, 'smap_pid', true);

			if (!is_null($PID)) {
				$PID = ' = '.$PID;
			}
			else {
				$PID = 'IS NULL';
			}

			$orderFieldName = key($order);
			$request = sprintf('SELECT %s, %s
                FROM %s
                WHERE %s %s= (
                SELECT %s
                FROM %s
                WHERE %s = %s )
                AND smap_pid %s
                %s
                LIMIT 2 ',
			$this->getPK(), $orderFieldName,
			$this->getTableName(),
			$orderFieldName, $direction,
			$orderFieldName,
			$this->getTableName(),
			$this->getPK(), $id,
			$PID,
			$this->dbh->buildOrderCondition($order));

			$result = $this->dbh->selectRequest($request);
			if ($result === true || sizeof($result)<2) {
				throw new SystemException('ERR_CANT_MOVE', SystemException::ERR_NOTICE);
			}

			$result = convertDBResult($result, $this->getPK(), true);

			/**
			 * @todo Тут нужно что то пооптимальней придумать для того чтобы осуществить операцию переноса значений между двумя элементами массива
			 *  $a = $b;
			 *  $b =$a;
			 */
			$keys = array_keys($result);
			$data = array();

			$c = $result[current($keys)];
			$data[current($keys)] = $result[next($keys)];
			$data[current($keys)] = $c;

			foreach ($data as $id2 => $value) {
				$order = $value['smap_order_num'];
				$this->dbh->modify(QAL::UPDATE, $this->getTableName(), array($orderFieldName=>$order), array($this->getPK()=>$id2));
				if ($id2 != $id) {
					$result = $id2;
				}
			}
			$JSONResponse = array(
            'result' => true,
            'nodeID' => $result,
            'dir' => $direction
			);
		}
		catch (SystemException $e){
			$JSONResponse = $this->generateError($e->getCode(), $e->getMessage());

		}
		return json_encode($JSONResponse);
	}
}
