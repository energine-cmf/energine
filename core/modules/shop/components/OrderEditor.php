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
use PHPExcel_IOFactory;
use PHPExcel_Worksheet_MemoryDrawing;

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
        const TEMP_TILE = '/uploads/tmp/order_editor_export.csv';
        const XLS_TEMP_FILE = '/uploads/tmp/order_editor_export.xlsx';
//         const TEMP_SELECTED_FILE = '/uploads/tmp/order_editor_selected_export.csv';
        const XLS_TEMP_SELECTED_FILE = '/uploads/tmp/order_editor_selected_export.xlsx';	

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
            $filename=$this->export($data);
            $this->response->setHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
            $this->response->setHeader('Content-Disposition','attachment; filename="' . basename($filename) . '"');
            $handle = fopen(HTDOCS_DIR . self::XLS_TEMP_FILE, "r");
            $this->response->write(  fread($handle,filesize(HTDOCS_DIR . self::XLS_TEMP_FILE)));
            fclose($handle);
            $this->response->commit();   
	}    
        protected function ordersExportSelected() {
            $items = $this->getStateParams();            
            $items=explode(",",$items[0]);           
            $data=$this->GetExportDataSelected($items);            
            $filename=$this->exportSelected($data);
            $this->response->setHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
            $this->response->setHeader('Content-Disposition','attachment; filename="' . basename($filename) . '"');
            $handle = fopen(HTDOCS_DIR . self::XLS_TEMP_SELECTED_FILE, "r");
            $this->response->write(  fread($handle,filesize(HTDOCS_DIR . self::XLS_TEMP_SELECTED_FILE)));
            fclose($handle);
            $this->response->commit();           	
	}
	protected function GetExportData(){
            $order_list=[];
            $tCampagin=$this->translate("Export_Orders_Campagin");
            $tOrder=$this->translate("Export_Orders_Order");
            $tUpdated=$this->translate("Export_Orders_Updated");
            $tUser=$this->translate("Export_Orders_User");
            $tPhone=$this->translate("Export_Orders_Phone");            
            $tTotal=$this->translate("Export_Orders_Total");
            $tDiscount=$this->translate("Export_Orders_Discount");
            $tPromocode=$this->translate("Export_Orders_Promocode");
            $tStatus=$this->translate("Export_Orders_Status");
            $tNoCampagin=$this->translate("Export_Orders_NoCampagin");
            
            $order_list[]=[$tCampagin,$tOrder,$tUpdated,$tUser,$tPhone,$tTotal,$tDiscount,$tPromocode,$tStatus];
            $shop_table=$this->getTableName();            
            $sql="SELECT distinct order_campagin FROM ".$shop_table;
            $campagins=$this->dbh->select($sql);
            foreach ($campagins as $campagin ) {
            if ($campagin["order_campagin"]==NULL) {
                $where_condition=" IS NULL ";
            } else {
                $where_condition=" = '".$campagin["order_campagin"]."' ";
            }             
            $sql="SELECT IF(order_campagin IS NULL,'".$tNoCampagin."',order_campagin),order_id,order_updated,order_user_name,
            QUOTE(order_phone) as  order_phone,order_total,order_discount,order_promocode,shop_order_statuses.status_sysname FROM ".$shop_table." LEFT JOIN shop_order_statuses ON shop_orders.status_id=shop_order_statuses.status_id 
            WHERE shop_orders.order_campagin".$where_condition." 
             UNION 
            SELECT 'Sum','','','','',SUM(order_total),SUM(order_discount),'','' FROM ".$shop_table." WHERE order_campagin".$where_condition;
            
            $orders=$this->dbh->select($sql);            
            $txt_campagin=($campagin["order_campagin"]==NULL)?$tNoCampagin:$campagin["order_campagin"];
            array_unshift($orders,[0=>$txt_campagin]);
            $order_list[]=$orders;
            }
            return $order_list;
	}
	protected function GetExportDataSelected($items){
            $order_list=[];
            //before table
            $tOrderNum=$this->translate("Export_Orders_Order_Number");
            $tOrderDate=$this->translate("Export_Orders_Order_Date");
            $tUserName=$this->translate("Export_Orders_User_Name");
            //table
            $tOrderProductCode=$this->translate("Export_Orders_Product_Code");
            $tOrderProductName=$this->translate("Export_Orders_Product_Name");
            $tOrderProductAmount=$this->translate("Export_Orders_Product_Amount");
            $tOrderProductPricePerItem=$this->translate("Export_Orders_Product_Price_Per_Item");
            $tOrderProductItemSummPrice=$this->translate("Export_Orders_Product_Item_Summ_Price");
            //after table
            $tOrderProductSummPrice=$this->translate("Export_Orders_Product_Summ_Price");
            //after after table
            $tOrderPromocodeUsed=$this->translate("Export_Orders_Promocode_Used");
            $tOrderDiscountSumm=$this->translate("Export_Orders_Summ_Discount");
            $tOrderStatus=$this->translate("Export_Orders_Status");

            $shop_table=$this->getTableName();
            
            foreach ($items as $item ) {
                $sql="SELECT order_id,order_updated,order_user_name,
                order_total,order_promocode,order_discount,shop_order_statuses.status_sysname as status                   
                FROM ".$shop_table." LEFT JOIN shop_order_statuses ON shop_orders.status_id=shop_order_statuses.status_id 
                WHERE order_id=".intval($item)." LIMIT 1";
                $order_data=$this->dbh->select($sql);                
                $order_data=$order_data[0];
                $sql="SELECT goods_id,goods_title,goods_quantity,goods_price,goods_amount            
                FROM shop_orders_goods
                WHERE order_id=".intval($item)."";
                $order_goods=$this->dbh->select($sql);
                
                $order_list[]=[$tOrderNum,$order_data["order_id"],$tOrderDate,$order_data["order_updated"],$tUserName,$order_data["order_user_name"]];
                $order_list[]=[$tOrderProductCode,$tOrderProductName,$tOrderProductAmount,$tOrderProductPricePerItem,$tOrderProductItemSummPrice];
                foreach ($order_goods as $data ) {                    
                    $order_list[]=[$data["goods_id"],$data["goods_title"],$data["goods_quantity"],$data["goods_price"],$data["goods_amount"]];
                }
                $order_list[]=['','','',$tOrderProductSummPrice,$order_data["order_total"]];
                $order_list[]=[$tOrderPromocodeUsed,$order_data["order_promocode"]];
                $order_list[]=[$tOrderDiscountSumm,$order_data["order_discount"]];
                $order_list[]=[$tOrderStatus,$order_data["status"]];
                $order_list[]=[' '];
             
            }
            return $order_list;
            }	
            /**
            * @return string
            * @throws \Exception
            */
            public function export($data)
            {
            try {
                $prices = $data;
                $headers=array_shift($prices);
                $this->addExtraStaceBefore(4);
                $this->writeLineToFile($headers);                
                foreach ($prices as $campagins)
                foreach ($campagins as $priceRow) {                    
                    $this->writeLineToFile($priceRow);
                    
                }
                $this->endOfWriting();

                $csv = PHPExcel_IOFactory::load(HTDOCS_DIR . self::TEMP_TILE);
                
                $writer= PHPExcel_IOFactory::createWriter($csv, 'Excel2007');
                $writer = $this->addLogo($writer);
                $writer->save(HTDOCS_DIR . self::XLS_TEMP_FILE);
                return self::XLS_TEMP_FILE;
            } catch (\Exception $exception) {
                throw new \Exception($exception->getMessage());
                }
            }
            public function exportSelected($data)
            {
            try {
                $prices = $data;
                $headers=array_shift($prices);
                $this->addExtraStaceBefore(4);
                $this->writeLineToFile($headers);
                foreach ($prices as $line)
                  $this->writeLineToFile($line);
                $this->endOfWriting();

                $csv = PHPExcel_IOFactory::load(HTDOCS_DIR . self::TEMP_TILE);
                
                $writer= PHPExcel_IOFactory::createWriter($csv, 'Excel2007');
                $writer = $this->addLogo($writer);
                $writer->save(HTDOCS_DIR . self::XLS_TEMP_SELECTED_FILE);
                return self::XLS_TEMP_SELECTED_FILE;
            } catch (\Exception $exception) {
                throw new \Exception($exception->getMessage());
                }
            }	            
    private function addLogo($excelWriter)
    {
        $dbh = E()->getDb();
        //$site_id=E()->getSiteManager()->getCurrentSite()->id;
        $site_id=E()->getSiteManager()->getDefaultSite()->id;        
        
        $logoUrl = $dbh->getScalar(
            'SELECT su.upl_path FROM share_sites_uploads as ssu,share_uploads as su WHERE (ssu.site_id=%s) and (ssu.upl_id=su.upl_id) and (su.upl_is_active=1) ORDER BY ssu.ssu_order_num  LIMIT 1',
            $site_id
        );
        if($logoUrl===false) return;

        if (strpos($logoUrl, 'http') === false) {
            $logoUrl = sprintf('%s/%s', HTDOCS_DIR, $logoUrl);
        }

        try {
            $gdImage = imagecreatefromjpeg($logoUrl);
        } catch (\Exception $e) {
            try {
                $gdImage = imagecreatefrompng($logoUrl);
            } catch (\Exception $exception) {
                $gdImage = '';
            }
        }

        $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
        $objDrawing->setCoordinates('A1');
        $objDrawing->setName('Logo');
        $objDrawing->setDescription('Logo');
        if ($gdImage) {
            $objDrawing->setImageResource($gdImage);
        }
        $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
        $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
        $objDrawing->setHeight(70);
        $objDrawing->setWorksheet($excelWriter->getPHPExcel()->getActiveSheet());
        return $excelWriter;
    }

    private function addExtraStaceBefore($lines)
    {
        for ($i = 0; $i < $lines; $i++) {
            $this->writeLineToFile(array_fill(0, 10, null));
        }
    }
    private function writeLineToFile($row)
    {
        if (is_array($row)) {
        fputcsv($this->getFile(), array_values($row));
        } else {
          fputcsv($this->getFile(), [$row]);
        }
    }

    private function endOfWriting()
    {
        fclose($this->getFile());
    }
    public function getFile()
    {
        if (is_null($this->file)) {
            $this->file = fopen(HTDOCS_DIR . self::TEMP_TILE, 'w+');
        }
        return $this->file;
    }    



}

interface SampleOrderEditor {

}