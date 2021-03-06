<?php
/**
 * Created by PhpStorm.
 * User: pavka
 * Date: 10/2/15
 * Time: 12:54 PM
 */

namespace Energine\shop\components;


use Energine\share\components\Grid;
use Energine\share\components\SiteEditor;
use Energine\share\gears\AttachmentManager;
use Energine\share\gears\Field;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\TagManager;

class ShopEditor extends SiteEditor {
	/**
	 * @copydoc Grid::__construct
	 */
	public function __construct( $name, array $params = null ) {
		parent::__construct( $name, $params ); // TODO: Change the autogenerated stub
		$this->setFilter( [
			$this->getTableName() . '.site_id' => TagManager::getFilter( 'shop', $this->getTableName() ),
		] );

	}

	protected function attachments() {
		$sp = $this->getStateParams( true );
		if ( ! empty( $sp["id"] ) ) {
			$this->setFilter( [ $this->getTableName() . AttachmentManager::ATTACH_TABLE_SUFFIX . '.site_id' => $sp["id"] ] );
		}
		parent::attachments();
	}

	protected function linkExtraManagers( $tableName, $data = false ) {
		//disable Files tab manually
	}

	protected function prepare() {
		Grid::prepare();
		if ( in_array( $this->getState(), [ 'add', 'edit' ] ) ) {
			$dd = $this->getDataDescription();

			$dd->getFieldDescriptionByName( 'site_is_default' )->setType( FieldDescription::FIELD_TYPE_HIDDEN );
			$dd->getFieldDescriptionByName( 'site_folder' )->setType( FieldDescription::FIELD_TYPE_HIDDEN );
			$dd->getFieldDescriptionByName( 'currency_id' )->removeProperty( 'nullable' );
			$dd->getFieldDescriptionByName( 'country_id' )->removeProperty( 'nullable' );

			$dd->removeFieldDescription( $dd->getFieldDescriptionByName( 'site_meta_robots' ) );
			$dd->removeFieldDescription( $dd->getFieldDescriptionByName( 'site_is_indexed' ) );

			$fd = new FieldDescription( 'domains' );
			$fd->setType( FieldDescription::FIELD_TYPE_TAB );
			$fd->setProperty( 'title', $this->translate( 'TAB_DOMAINS' ) );
			$this->getDataDescription()->addFieldDescription( $fd );

			$state = $this->getState();

			$field   = new Field( 'domains' );
			$tab_url = ( ( $state != 'add' ) ? $this->getData()->getFieldByName( $this->getPK() )->getRowData( 0 ) : '' ) . '/domains/';
			$field->setData( $tab_url, true );
			$this->getData()->addField( $field );

			$fd = new FieldDescription( 'attachments' );
			$fd->setType( FieldDescription::FIELD_TYPE_TAB );
			$fd->setProperty( 'title', $this->translate( 'TAB_SITE_LOGO_FILES' ) );
			$this->getDataDescription()->addFieldDescription( $fd );
			$field   = new Field( 'attachments' );
			$tab_url = ( ( $state != 'add' ) ? $this->getData()->getFieldByName( $this->getPK() )->getRowData( 0 ) : '' ) . '/attachments/';
			$field->setData( $tab_url, true );
			$this->getData()->addField( $field );
		}
	}

	protected function saveData() {
		$_POST['tags'] = 'shop';

		return parent::saveData();
	}
}