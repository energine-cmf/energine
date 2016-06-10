<?php
/**
 * @file
 * GoodsFeatureEditor
 *
 * It contains the definition to:
 * @code
 * class GoodsFeatureEditor;
 * @endcode
 *
 * @author andy.karpov, Oleg Marichev sunnydrake7@gmail.com
 * @copyright Energine 2015
 *
 * @version 1.0.0
 */
namespace Energine\shop\components;

use Energine\share\components\Grid, Energine\share\gears\QAL;
use Energine\share\gears\DataDescription;
use Energine\share\gears\Field;
use Energine\share\gears\FieldDescription;
use Energine\shop\gears\FeatureFieldAbstract;
use Energine\shop\gears\FeatureFieldFactory;
use Energine\shop\gears\FeatureFieldMultioption;
use Energine\share\gears\SystemException;
use Energine\share\gears\Data;

use Energine\share\gears\JSONCustomBuilder;
/**
 * Goods feature editor editor.
 *
 * @code
 * class GoodsFeatureEditor;
 * @endcode
 */
class GoodsFeatureEditor extends Grid {

    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $this->setTableName('shop_feature2good_values');
		if ($this->getParam('goodsID')) {
			$filter = sprintf(' (goods_id = %s) ', $this->getParam('goodsID'));
		} else {
			$filter = sprintf(' (goods_id IS NULL and session_id="%s") ', session_id());
		}

		$this->setFilter($filter);
		$this->prepare();
    }

    /**
     * @copydoc Grid::defineParams
     */
    protected function defineParams() {	 
        return array_merge(
            parent::defineParams(),
            [
                'goodsID' => false,
                'smapID' => false,
            ]
        );
    }

    /**
     * @copydoc Grid::loadDataDescription
     *
     */
    protected function loadDataDescription() {
        $result = parent::loadDataDescription();

        if (in_array($this->getState(), ['add', 'edit', 'save'])) {
            unset($result['goods_id']);
        }
	  //modbysd dirty hack to add col field description
	$result["feature_title"]=array ("tableName"=>"shop_features_translation" );
        return $result;
    }

    /**
     * Для списка устанавливает фильтр по goods_id и feature_id (в привязке к текущему smap_id, переданному в сессии)
     * Также для характеристик типа OPTION, VARIANT тянет значение FK выбранной опции
     *
     * @return array|bool|false|mixed
     * @throws \Energine\share\gears\SystemException
     */
    protected function loadData() {
        if ($this->getState() == 'getRawData') {   

            $features = (!empty($_SESSION['goods_feature_editor']['filter_feature_id'])) ? $_SESSION['goods_feature_editor']['filter_feature_id'] : ['-1'];
            $goodsID = (!empty($_SESSION['goods_feature_editor']['filter_goods_id'])) ? $_SESSION['goods_feature_editor']['filter_goods_id'] : NULL;

            if ($goodsID) {
                $filter = '(goods_id =' . $goodsID . ' )';
            } else {
                $filter = '(goods_id IS NULL and session_id="'. session_id() .'")';
            }
            $filter = '((' . $filter . ') AND feature_id IN (' . join(',', $features) . ') )';
            $this->setFilter($filter);	    
        }

	$language_id=parent::getDataLanguage();
	if ($language_id !== FALSE)  {
	    E()->getDocument()->setLang($language_id);
	}
	if ($this->getState() == 'save') { //ugly workaround for null or "" values
	  $checknull=&$_POST[$this->getTranslationTableName()];
	  if (is_array($checknull)) 
	   foreach ($checknull as $i=>$row) 
	      if ($row === null or $row == "") unset($checknull[$i]);
	}
        $data = parent::loadData();

        if ($data and is_array($data) and $this->getState() == 'getRawData') {
            foreach ($data as $key => $row) {
                // замена строкового значения характеристики для списка
                $feature = FeatureFieldFactory::getField($row['feature_id'], $row['fpv_data']);
                if ($feature) {
                    $data[$key]['fpv_data'] = (string)$feature;		    
		    $data[$key]['feature_id'] = $feature->getName();		    
		    $data[$key]['feature_title'] = $feature->getTitle();		    
		    $data[$key]['fpv_order'] = $this->dbh->getScalar($this->getTableName(), $this->getOrderColumn(),['fpv_id'=>$row['fpv_id']]);
                }

            }
	  
        }
        if ($data and is_array($data) and $this->getState() == 'save') {
            $feature = FeatureFieldFactory::getField($data[0]['feature_id']);
            if ($feature and $feature->getType() == FeatureFieldAbstract::FEATURE_TYPE_MULTIOPTION or
				$feature and $feature->getType() == FeatureFieldAbstract::FEATURE_TYPE_VARIANT) {
                foreach ($data as $idx => $row) {
                    if (isset($row['fpv_data']) and is_array($row['fpv_data'])) {
                        $data[$idx]['fpv_data'] = implode(',', $data[$idx]['fpv_data']);
                    }
                }
            }
            $langs = array_keys(E()->getLanguage()->getLanguages());
            if (($count = sizeof($langs) - sizeof($data)) != 0) {
                foreach ($langs as $idx => $langID) {
                    if (!isset($data[$idx])) {
                        $data[$idx] = $data[0];
                        $data[$idx]['lang_id'] = $langID;
                    }
                }
            }
        }
        return $data;
    }


    /**
     * При инициализации редактора (активации вкладки в родительском редакторе разделов)
     * создает пустые значения характеристик в привязке к товару.
     * Также передает в сессионные переменные для метода getRawData выбранные фильтры по smapID / goodsID
     *
     * @throws \Energine\share\gears\SystemException
     */
    protected function main() { 


        parent::main();

        $params = $this->getStateParams(true);
        $smapID = ($params) ? $params['smap_id'] : '';

        $goodsID = $this->getParam('goodsID');
        $goodsID = (!empty($goodsID)) ? $goodsID : '';
	if($goodsID!='') { //check for duplicate values
	  $request = "SELECT p1.fpv_id, p1.fpv_order_num FROM shop_feature2good_values AS p1, shop_feature2good_values AS p2 
	  WHERE p1.goods_id = ".$goodsID." and p2.goods_id = p1.goods_id AND p1.fpv_order_num = p2.fpv_order_num AND p1.fpv_id != p2.fpv_id
	  GROUP BY fpv_id";
	  $values=$this->dbh->select($request);
	  if ($values) {
	    $request="";  
	    foreach ($values as $value) $z[$value['fpv_order_num']][]=$value['fpv_id'];
	    foreach (array_keys($z) as $key)  { array_shift($z[$key]);$request.=implode(",",$z[$key]);}
	    $request="set @Count=(SELECT fpv_order_num from shop_feature2good_values ORDER BY fpv_order_num DESC LIMIT 1);
	    UPDATE shop_feature2good_values as k
	    SET k.fpv_order_num=@Count:=@Count+1
	    WHERE k.fpv_id IN (".$request.")";	  
	    $this->dbh->beginTransaction();
	    $this->dbh->modify($request);
	    $this->dbh->commit();	  
	  }
	}	
        $this->setProperty('smap_id', $smapID);
        $this->setProperty('goods_id', $goodsID);

        $languages = E()->getLanguage()->getLanguages();
        $features = [];

        if ($smapID) {
            $features = $this->dbh->getColumn('shop_sitemap2features', 'feature_id', ['smap_id' => $smapID]);
            if ($features) {
                foreach ($features as $feature_id) { 
                    if ($goodsID) {
                        $fpv_id = $this->dbh->getScalar('shop_feature2good_values', 'fpv_id', ['feature_id' => $feature_id, 'goods_id' => $goodsID]);
                    } else {
                        $fpv_id = $this->dbh->getScalar('shop_feature2good_values', 'fpv_id', ['feature_id' => $feature_id, 'session_id' => session_id()]);
                    }
                    if (!$fpv_id) {
                        $fpv_id = $this->dbh->modify(
                            QAL::INSERT_IGNORE, 'shop_feature2good_values',
                            ['feature_id' => $feature_id, 'goods_id' => $goodsID, 'session_id' => session_id()]
                        );
                        if ($fpv_id) {
                            foreach ($languages as $lang_id => $language) {
                                $this->dbh->modify(
                                    QAL::INSERT_IGNORE,
                                    'shop_feature2good_values_translation',
                                    ['fpv_id' => $fpv_id, 'lang_id' => $lang_id]);
                            }
                        }
                    }
                }
            }
        }

        if (empty($features)) {
            $features = ['-1'];
        }
        // устанавливаем сессионный фильтр, который передаем в getRawData
        $_SESSION['goods_feature_editor']['filter_feature_id'] = $features;
        $_SESSION['goods_feature_editor']['filter_smap_id'] = $smapID;
        $_SESSION['goods_feature_editor']['filter_goods_id'] = $goodsID;
    }

    /**
     * Форма редактирования меняет тип поля fpv_data в зависимости от типа характеристики
     * (OPTION, BOOL, INT, STRING, ...)
     * Для типа поля OPTION также наполняет значения выпадающего списка
     *
     * @throws \Energine\share\gears\SystemException
     */
    protected function edit() {
        parent::edit();


        $data = $this->getData();
        $dd = $this->getDataDescription();
        $fd = $dd->getFieldDescriptionByName('fpv_data');

        $feature_id = $data->getFieldByName('feature_id')->getRowData(0);
        $fpv_data = $data->getFieldByName('fpv_data')->getRowData(0);

        $feature = FeatureFieldFactory::getField($feature_id, $fpv_data);
        if ($feature) {
            $feature->modifyFormFieldDescription($dd, $fd);
            $field = $data->getFieldByName('fpv_data');
            $feature->modifyFormField($field);

            $fd = new FieldDescription('feature_name');
            $fd->setType(FieldDescription::FIELD_TYPE_STRING);
            $fd->setMode(FieldDescription::FIELD_MODE_READ);
            $fd->setProperty('tabName', E()->getLanguage()->getNameByID(E()->getLanguage()->getCurrent()));
            $dd->addFieldDescription($fd, DataDescription::FIELD_POSITION_AFTER, 'feature_id');

            $f= new Field('feature_name');
            $f->setData(($feature->getName())?$feature->getName():$feature->getTitle(), true);
            $data->addField($f);

            $fd = new FieldDescription('feature_title');
            $fd->setType(FieldDescription::FIELD_TYPE_STRING);
            $fd->setMode(FieldDescription::FIELD_MODE_READ);
            $fd->setProperty('tabName', E()->getLanguage()->getNameByID(E()->getLanguage()->getCurrent()));
            $dd->addFieldDescription($fd, DataDescription::FIELD_POSITION_AFTER, 'feature_id');

            $f= new Field('feature_title');
            $f->setData(($feature->getTitle())?$feature->getTitle():$feature->getName(), true);
            $data->addField($f);

        } else {
            // unknown feature type
            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        }
    }


    public function build() { 
        $result = parent::build();
        if (in_array($this->getDataDescription()->getFieldDescriptionByName('fpv_data')->getType(), [FieldDescription::FIELD_TYPE_MULTI, FieldDescription::FIELD_TYPE_SELECT, FieldDescription::FIELD_TYPE_INT, FieldDescription::FIELD_TYPE_BOOL])) {
            $xp = new \DOMXPath($result);
            if ($nodes = $xp->query('//field[@name="fpv_data" and @language!=' . $this->document->getLang() . ']')) {
                foreach ($nodes as $node) {
                    $node->parentNode->removeChild($node);
                }
            }
        }
        return $result;
    }
    protected function createDataDescription()
	{       
		$result = parent::createDataDescription();
		if (in_array($this->getState(), array('add', 'edit'))) {
			//disabled due to error
			//$fd = $result->getFieldDescriptionByName('goods_id');
			//$fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
// 			$fd = $result->getFieldDescriptionByName('session_id');
// 			$fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
		}

		return $result;
 	}

    /**
     * Change order.
     * @param string $direction Direction.
     * @throws SystemException 'ERR_NO_ORDER_COLUMN'
     */
    protected function changeOrder($direction) {
	  //$currentID = $this->getStateParams();
	  //list($currentID) = $currentID;
	  $this->changeOrderByID($direction,current($this->getStateParams()));
    }
    protected function changeOrderByID($direction,$id) {
        $this->applyUserFilter();
        if (!$this->getOrderColumn()) {
            //Если не задана колонка для пользовательской сортировки то на выход
            throw new SystemException('ERR_NO_ORDER_COLUMN', SystemException::ERR_DEVELOPER);
        }
        $currentID = $id;
        //Определяем order_num текущей страницы
        $currentOrderNum = $this->dbh->getScalar($this->getTableName(), $this->getOrderColumn(), [$this->getPK() => $id]);

	if (is_null($currentOrderNum)) { //modbysd:bad fix it
		$currentOrderNum=1;
		$this->dbh->modify(
                QAL::UPDATE,
                $this->getTableName(),
                [$this->getOrderColumn() => $currentOrderNum],
                [$this->getPK() => $currentID]
            );
	}
	

        $orderDirection = ($direction == Grid::DIR_DOWN) ? QAL::ASC : QAL::DESC;

        $baseFilter = $this->getFilter();

        if (!empty($baseFilter)) {
            $baseFilter = ' AND ' .
                str_replace('WHERE', '', $this->dbh->buildWhereCondition($this->getFilter()));
        } else {
            $baseFilter = '';
        }

        //Определяем идентификатор записи которая находится рядом с текущей
        $request =
            'SELECT ' . $this->getPK() . ' as neighborID, ' .
            $this->getOrderColumn() . ' as neighborOrderNum ' .
            'FROM ' . $this->getTableName() . ' ' .
            'WHERE ' . $this->getOrderColumn() . ' ' . $direction .
            ' ' . $currentOrderNum . ' ' . $baseFilter .
            'ORDER BY ' . $this->getOrderColumn() . ' ' .
            $orderDirection . ' Limit 1';
	    $data =
            convertDBResult($this->dbh->select($request), 'neighborID');

	if(empty($data)) {	//modbysd:no result,check for dupes looking for same order value if so move to new max fpv_order_num
			// all pos up - max fpv_order_numm down - id max fpv_order_num
	     $request =
            'SELECT ' . $this->getPK() . ' as neighborID, ' .
            $this->getOrderColumn() . ' as neighborOrderNum ' .
            'FROM ' . $this->getTableName() . ' ' .
            'WHERE ' . $this->getPK() . ' != '.
            ' ' . $currentID .' AND '.$this->getOrderColumn().'='.$currentOrderNum.' ' . $baseFilter .
            'ORDER BY '.$this->getPK().' '.$orderDirection.' Limit 1';
	    $data =convertDBResult($this->dbh->select($request), 'neighborID');
	if ($data) { 
		  $maxOrderNum = $this->dbh->select("SELECT fpv_order_num from ".$this->getTableName()."  ORDER BY fpv_order_num DESC LIMIT 1");
		  if (is_null($maxOrderNum[0]["fpv_order_num"])) $maxOrderNum[0]["fpv_order_num"]=0;
		  $currentOrderNum=$maxOrderNum[0]["fpv_order_num"]+1;
		  $this->dbh->beginTransaction();
		  $this->dbh->modify(
		    QAL::UPDATE,
		    $this->getTableName(),
		    [$this->getOrderColumn() => $currentOrderNum],
		    [$this->getPK() => $currentID]
		  );  
		  $this->dbh->commit();
	    }
	} elseif($data) { //normal ops neighbor data found
            $neighborID = NULL;
            $neighborOrderNum = 0;
            extract(current($data));
            $this->dbh->beginTransaction();
            $this->dbh->modify(
                QAL::UPDATE,
                $this->getTableName(),
                [$this->getOrderColumn() => $currentOrderNum],
                [$this->getPK() => $neighborID]
            );
            $this->dbh->modify(
                QAL::UPDATE,
                $this->getTableName(),
                [$this->getOrderColumn() => $neighborOrderNum],
                [$this->getPK() => $currentID]
            );

            $this->dbh->commit();
        }
        $b = new JSONCustomBuilder();
        $b->setProperties([
            'result' => true,
            'dir' => $direction
        ]);
        $this->setBuilder($b);
}
	protected function prepare() { 
		parent::prepare();

	}
    /**
     * Move the record.
     * Allowed movement:
     * - above
     * - below
     * - top
     * - bottom
     * @todo: Пофиксить перемещение в начало списка, т.к. сейчас порядковый номер может выйти меньше 0. Аналогичная ситуация с move above.
     * @throws SystemException 'ERR_NO_ORDER_COLUMN'
     */
   protected function moveTo() {
        if (!$this->getOrderColumn()) {
            //Если не задана колонка для пользовательской сортировки то на выход
            throw new SystemException('ERR_NO_ORDER_COLUMN', SystemException::ERR_DEVELOPER);
        }

        $params = $this->getStateParams();
	$goodsID=$this->getParam('goodsID');
	if (count($params)>2) {
	  list($toid, $direction,$firstItem) = $params;
	}else{
	  list($firstItem, $direction) = $params;
	}
        $allowed_directions = ['first', 'last', 'above', 'below'];
        if (in_array($direction, $allowed_directions) && $firstItem == intval($firstItem)) {
            switch ($direction) {
                // двигаем элемент с id=$firstItem на самый верх
                case 'first': //move element to order 0 and reindex elements by @order
                    $this->dbh->beginTransaction();                 			
                        $this->dbh->modify(
                            QAL::UPDATE,
                            $this->getTableName(),
                            [$this->getOrderColumn() => '0'],
                            [$this->getPK() => $firstItem]
                        );
		    
			$request="set @Count=0;
			   UPDATE ".$this->getTableName()." as a,".
			  " (SELECT ".$this->getPK()."  FROM ".$this->getTableName()." WHERE goods_id=".$goodsID." ORDER BY ".$this->getOrderColumn()." ASC) as b".
			  " SET a.".$this->getOrderColumn()." = @Count:=@Count+1".
			  " WHERE b.".$this->getPK()."=a.".$this->getPK();
			$this->dbh->modify($request);
		   $this->dbh->commit();
                    break;
                // двигаем элемент с id=$firstItem в самый низ
                case 'last':// from curr element all next pos-1,set curr element MAX+1
                    //$oldLastItem = (int)$this->dbh->getScalar('SELECT MAX(' . $this->getOrderColumn() . ') FROM ' . $this->getTableName() . ' WHERE goods_id= '.$goodsID. ' LIMIT 1');
		    
		   $this->dbh->beginTransaction(); 
	      		$request="set @CurPos=(SELECT ".$this->getOrderColumn()." FROM ".$this->getTableName()." WHERE ".$this->getPK()."=".$firstItem."  LIMIT 1);
			   set @Count=@CurPos-1;
			   UPDATE ".$this->getTableName()." as a,".
			  " (SELECT ".$this->getPK()."  FROM ".$this->getTableName()." WHERE goods_id=".$goodsID." AND ".$this->getOrderColumn()." > @CurPos ORDER BY ".$this->getOrderColumn()." ASC) as b".
			  " SET a.".$this->getOrderColumn()." = @Count:=@Count+1".
			  " WHERE b.".$this->getPK()."=a.".$this->getPK();
			$this->dbh->modify($request);
			$request="set @lastpos=(SELECT (".$this->getOrderColumn()."+1) FROM ".$this->getTableName()." ORDER BY ".$this->getOrderColumn()." DESC LIMIT 1);
			UPDATE ".$this->getTableName()." SET ".$this->getOrderColumn()."=@lastpos WHERE ".$this->getPK()."=$firstItem";

			$this->dbh->modify($request);

		   $this->dbh->commit();
                    break;
                // двигаем элемент выше или ниже id=$secondItem
                case 'above':
		    $this->changeOrderByID(Grid::DIR_UP,$firstItem);
		    return ;
		    break;
                case 'below':
		    $this->changeOrderByID(Grid::DIR_DOWN,$firstItem);
		    return ;
                    break;
            }
        }

        $b = new JSONCustomBuilder();
        $b->setProperty('result', true);
        $this->setBuilder($b);
    }
}