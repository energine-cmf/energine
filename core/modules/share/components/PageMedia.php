<?php
/**
 * @file
 * PageMedia
 *
 * It contains the definition to:
 * @code
class PageMedia;
 * @endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\share\components;

use Energine\share\gears\AttachmentManager;
use Energine\share\gears\Data;
use Energine\share\gears\DataDescription;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\SimpleBuilder;

/**
 * Show media container on the page with attached to that page media files and additional page info
 *
 * @code
class PageMedia;
 * @endcode
 */
class PageMedia extends DataSet {

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'id' => false
            ]
        );
    }

    protected function main() {
        $id = (!$id = $this->getParam('id')) ? $this->document->getID() : $id;

        $this->setBuilder(new SimpleBuilder());
        $dd = new DataDescription();
        $info = E()->getMap()->getDocumentInfo($id);

        $dd->load([
            'Id' => [
                'type' => FieldDescription::FIELD_TYPE_INT,
            ],
            'Name' => [
                'type' => FieldDescription::FIELD_TYPE_STRING,
            ],
            'Title' => [
                'type' => FieldDescription::FIELD_TYPE_STRING,
            ],
            'HtmlTitle' => [
                'type' => FieldDescription::FIELD_TYPE_STRING,
            ],
            'DescriptionRtf' => [
                'type' => FieldDescription::FIELD_TYPE_HTML_BLOCK,
            ]

        ]);
        $d = new Data();
        $info['Id'] = $id;
        $d->load([$info]);
        $this->setDataDescription($dd);
        $this->setData($d);

        $m = new AttachmentManager(
            $this->getDataDescription(),
            $this->getData(),
            'share_sitemap',
            true
        );
        $m->createFieldDescription();
        $m->createField('smap_id', false, $id);
    }
}