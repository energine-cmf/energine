<?php

/**
 * @file      OrderEditor
 *
 * It contains the definition to:
 * @code      class OrderEditor; @endcode
 *
 * @author    andy.karpov
 * @copyright Energine 2015
 *
 * @version   1.0.0
 */

namespace Energine\shop\components;

use Energine\share\components\Grid,
	Energine\share\gears\FieldDescription,
	Energine\share\gears\Field,
	Energine\share\gears\ComponentManager,
	Energine\share\gears\Sitemap;
use Energine\share\gears\Data;
use Energine\share\gears\DataDescription;
use Energine\share\gears\JSONCustomBuilder;
use Energine\share\gears\QAL;
use Energine\share\gears\GridExtender;

/**
 * Order editor.
 *
 * @code
 * class OrderEditor;
 * @endcode
 */
class OrderEditor extends Grid implements SampleOrderEditor {
	use GridExtender;
	/**
	 * Order Goods editor.
	 * @var OrderGoodsEditor $orderGoodsEditor
	 */
	protected $orderGoodsEditor;

	/**
	 * @copydoc Grid::__construct
	 */
	public function __construct( $name, array $params = null ) {
		parent::__construct( $name, $params );
		$this->setTableName( 'shop_orders' );
		$this->setOrder( [ 'order_updated' => QAL::DESC ] );
		if ( $sites = E()->getSiteManager()->getSitesByTag( 'shop' ) ) {
			$site_ids = array_map( function ( $site ) {
				return (string) $site;
			}, $sites );

			$this->setFilter( [
				'site_id' => $site_ids,
			] );
		}
	}

	/**
	 * Added "goods" data description to the forms
	 *
	 * @throws \Energine\share\gears\SystemException
	 */
	protected function prepare() {

		parent::prepare();

		if ( in_array( $this->getState(), [ 'add', 'edit' ] ) ) {

			// relations
			$fd = new FieldDescription( 'goods' );
			$fd->setType( FieldDescription::FIELD_TYPE_TAB );
			$fd->setProperty( 'title', $this->translate( 'TAB_ORDER_GOODS' ) );
			$this->getDataDescription()->addFieldDescription( $fd );

			$field   = new Field( 'goods' );
			$state   = $this->getState();
			$tab_url = ( ( $state != 'add' ) ? $this->getData()->getFieldByName( $this->getPK() )->getRowData( 0 ) : '' ) . '/goods/';

			$field->setData( $tab_url, true );
			$this->getData()->addField( $field );
			$field = new Field( 'order_goods_discount' );
			$field->setData( 0, true );
			$this->getData()->addField( $field );
		}
	}

	protected function add() {
		parent::add();
		$shopID = E()->getSiteManager()->getDefaultSite()->id;
		$this->getData()->addField(
			( new Field( 'site_id' ) )->addRowData( $shopID )
		);
		$currID = E()->getSiteManager()->getDefaultSite()->currencyId;

		$this->getData()->addField(
			( new Field( 'currency_id' ) )->addRowData( $currID )
		);
	}

	protected function edit() {
		parent::edit();
		if($f = $this->getData()->getFieldByName('order_promocode')){

			if($f->getRowData(0)){
				$this->getDataDescription()->getFieldDescriptionByName('order_discount')->setMode(FieldDescription::FIELD_MODE_READ);
			}
		}
	}

	/**
	 * Create component for editing ordered goods.
	 */
	protected function orderGoodsEditor() {
		$sp     = $this->getStateParams( true );
		$params = [ 'config' => 'core/modules/shop/config/OrderGoodsEditor.component.xml' ];

		if ( isset( $sp['order_id'] ) ) {
			$this->request->shiftPath( 2 );
			$params['orderID'] = $sp['order_id'];

		} else {
			$this->request->shiftPath( 1 );
		}
		$this->orderGoodsEditor = $this->document->componentManager->createComponent( 'orderGoodsEditor',
			'Energine\shop\components\OrderGoodsEditor', $params );
		$this->orderGoodsEditor->run();
	}

	/**
	 * @copydoc GoodsEditor::build
	 */
	public function build() {
		if ( $this->getState() == 'orderGoodsEditor' ) {
			$result = $this->orderGoodsEditor->build();
		} else {
			$result = parent::build();
		}

		return $result;
	}

	/**
	 * @copydoc Grid::saveData
	 */
	protected function saveData() {
		$_POST[ $this->getTableName() ]['order_updated'] = date( 'Y-m-d H:i:s' );

		$orderID = parent::saveData();
		$this->saveOrderGoods( $orderID );
		$this->dbh->modify(
			'UPDATE shop_orders
			SET order_goods_count=(SELECT SUM(goods_quantity) FROM shop_orders_goods WHERE order_id = %s)
			WHERE order_id = %1$s',
			$orderID
		);

		return $orderID;
	}

	/**
	 * Link order goods to the current order_id (after save)
	 *
	 * @param int $orderID
	 *
	 * @throws \Energine\share\gears\SystemException
	 */
	protected function saveOrderGoods( $orderID ) {
		$this->dbh->modify(
			'UPDATE shop_orders_goods
			SET session_id = NULL, order_id=%s
			WHERE (order_id IS NULL AND session_id=%s) or (order_id = %1$s)',
			$orderID, session_id()
		);
	}

	protected function orderTotal() {

		$sp      = $this->getStateParams( true );
		$orderID = isset( $sp['order_id'] ) ? $sp['order_id'] : 0;

		$this->setBuilder( new JSONCustomBuilder() );

		$goods_total = $this->dbh->getScalar(
			'select SUM(goods_real_price*goods_quantity) from shop_orders_goods
             where (order_id = %s) or (order_id is NULL and session_id = %s)',
			$orderID, session_id()
		);

		$total          = $this->dbh->getScalar(
			'select SUM(goods_price*goods_quantity) from shop_orders_goods
             where (order_id = %s) or (order_id is NULL and session_id = %s)',
			$orderID, session_id()
		);
		$goods_discount = $goods_total - $total;

		$discount = ( isset( $_REQUEST['order_discount'] ) and is_numeric( $_REQUEST['order_discount'] ) ) ? $_REQUEST['order_discount'] : 0;
		$total -= $discount;
		$b = $this->getBuilder();

		$b->setProperty( 'result', true )
		  ->setProperty( 'discount', number_format( $goods_discount, 2, '.', '' ) )
		  ->setProperty( 'amount', number_format( $goods_total, 2, '.', '' ) )
		  ->setProperty( 'total', number_format( $total, 2, '.', '' ) );
	}

	protected function userDetails() {

		$sp  = $this->getStateParams( true );
		$uID = isset( $sp['u_id'] ) ? $sp['u_id'] : 0;
		//@todo move to shop_addresses
		$this->setBuilder( new JSONCustomBuilder() );
		$res = $this->dbh->getRow(
			'
        select 
        	u_fullname as order_user_name, u_city as order_city, u_phone as order_phone, LCASE(u_name) as order_email,
        	 u_avatar_img as image,
IFNULL(u_add_phone, adr_aux_phone) as order_aux_phone,
adr_index as order_index, 
adr_floor as order_floor,
adr_apt  as order_apt,
adr_building as order_building,
adr_street as order_street
	        from user_users u
            left join site_address a ON (u.u_id = a.u_id) AND (adr_is_main || !adr_is_main)
             where u.u_id = %s',
			$uID
		);
		/**
		 * @var $b JSONCustomBuilder
		 */
		$b = $this->getBuilder();
		$b->setProperty( 'result', (boolean) $res );
		if ( ! $res ) {
			$res = [
				'order_user_name' => '',
				'order_city'      => '',
				'order_phone'     => '',
				'order_email'     => '',
			];
		}

		$b->setProperties( $res );
	}
	
	protected function ordersExport() {
            $data=$this->GetExportData();
            $filename = $this->getTitle() . '.csv';
//            $filename = 'OrdersExport' . '.csv';
//             $MIMEType = 'application/csv; charset=utf-8';
//             $this->downloadFile($data, $MIMEType, $filename);
            $this->response->setHeader('Content-Type', 'application/csv; charset=utf-8');
            $this->response->setHeader('Content-Disposition','attachment; filename="' . $filename . '"');
            $this->response->write($data);
            $this->response->commit();   
	}    
	protected function GetExportData(){
            $order_list="";
            $order_list.= $this->FormatToCSVString([["Campagin","Order #","Updated","User","Phone","Total","Discount","Promocode","Status"]]);
            $shop_table=$this->getTableName();            
            $sql="SELECT distinct order_campagin FROM ".$shop_table;
            $campagins=$this->dbh->select($sql);
            foreach ($campagins as $campagin ) {
            if ($campagin["order_campagin"]==NULL) {
                $where_condition=" IS NULL ";
            } else {
                $where_condition=" = '".$campagin["order_campagin"]."' ";
            }             
            $sql="SELECT order_campagin,order_id,order_updated,order_user_name,order_phone,order_total,order_discount,order_promocode,shop_order_statuses.status_sysname FROM ".$shop_table." LEFT JOIN shop_order_statuses ON shop_orders.status_id=shop_order_statuses.status_id 
            WHERE shop_orders.order_campagin".$where_condition." 
             UNION 
            SELECT 'Sum','','','','',SUM(order_total),SUM(order_discount),'','' FROM ".$shop_table." WHERE order_campagin".$where_condition;
            
            $orders=$this->dbh->select($sql);            
            $order_list.= $this->FormatToRowCSVString(($campagin["order_campagin"]==NULL)?"":$campagin["order_campagin"]);
            $order_list.= $this->FormatToCSVString($orders);
            }
            return $order_list;
	}
        /**
            * Prepare CSV string.
            * @param array $nextValue Next value.
            * @return string
            */
        protected function FormatToCSVString(Array $Value) {
        $separator = '"';
        $delimiter = ';';
        $rowDelimiter = "\r\n";
        $row = '';
        foreach ($Value as $nextValue) {            
            foreach ($nextValue as $fieldValue) {
                $row .= $separator .
                str_replace([$separator, $delimiter], ["''", ','], $fieldValue) .
                $separator . $delimiter;
            }
            //$row = substr($row, 0, -1);
            $row.=$rowDelimiter;
        }
        return $row;
        }	
        protected function FormatToRowCSVString(String $Value) {
        $separator = '"';
        $delimiter = ';';
        $rowDelimiter = "\r\n";
        return  $separator.str_replace([$separator, $delimiter], ["''", ','], $Value).$separator . $delimiter.$rowDelimiter;
        }
}

interface SampleOrderEditor {

}