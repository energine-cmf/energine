<?php
/**
 * @file
 * PageMedia
 *
 * It contains the definition to:
 * @code
class PageMedia;
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */

/**
 * Show media container on the page with attached to that page media files.
 *
 * @code
class PageMedia;
@endcode
 */
class PageMedia extends DataSet {
    //todo VZ: This can be removed.
    /**
     * @copydoc DataSet::__construct
     */
    public function __construct($name,  array $params = null) {
        parent::__construct($name, $params);
    }

    /**
     * @copydoc DataSet::main
     */
    // Выводит галлерею
    protected function main() {
        $this->prepare();

        //Поле добавлено чтобы Data не был пустым
        $this->getDataDescription()->addFieldDescription(new FieldDescription('fake'));
        $this->getData()->addField($f = new Field('fake'));
        $f->setData(false);
        $m = new AttachmentManager(
            $this->getDataDescription(),
            $this->getData(),
            'share_sitemap',
            true
        );
        $m->createFieldDescription();
        $m->createField('smap_id', false, $this->document->getID());
    }
}