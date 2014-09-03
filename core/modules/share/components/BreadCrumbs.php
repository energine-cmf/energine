<?php
/**
 * @file
 * BreadCrumbs.
 *
 * It contains the definition to:
 * @code
final class BreadCrumbs;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\components;
use Energine\share\gears\DataDescription, Energine\share\gears\FieldDescription, Energine\share\gears\SimpleBuilder, Energine\share\gears\Data;

/**
 * "Bread crumbs"
 *
 * @code
final class BreadCrumbs;
 * @endcode
 *
 */
class BreadCrumbs extends DataSet {
    /**
     * List of additional elements.
     * Необходим для того чтобы другие компоненты могли добавлять хлебные крошки
     * @var array $additionalCrumbs
     * @note This is needed to allow other components add bread crumbs.
     */
    private $additionalCrumbs = array();

    /**
     * @copydoc DataSet::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->setProperty('site', E()->getSiteManager()->getCurrentSite()->name);
    }

    /**
     * Create data description.
     *
     * @return DataDescription
     *
     * @note Since it is impossible to change the list of fields, the required values will be forced to reset.
     */
    protected function createDataDescription() {
        $result = new DataDescription();
        $field = new FieldDescription('Id');
        $field->setType(FieldDescription::FIELD_TYPE_INT);
        $field->setProperty('key', true);
        $result->addFieldDescription($field);

        $field = new FieldDescription('Name');
        $field->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($field);

        $field = new FieldDescription('Segment');
        $field->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($field);

        $field = new FieldDescription('Title');
        $field->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($field);

        return $result;
    }

    /**
     * @copydoc DataSet::prepare
     */
    protected function prepare() {
        $this->setBuilder(new SimpleBuilder());
        $this->setDataDescription($this->createDataDescription());
        if (!$this->getData()) {
            $data = $this->createData();
            if ($data instanceof Data) {
                $this->setData($data);
            }
        }
    }

    /**
     * @copydoc DataSet::loadData
     */
    protected function loadData() {
        $sitemap = E()->getMap();
        $result = array();
        $parents = $sitemap->getParents($this->document->getID());
        foreach ($parents as $id => $current) {
            $result[] = array(
                'Id' => $id,
                'Name' => strip_tags($current['Name']),
                'Segment' => $current['Segment'],
                'Title' => $current['HtmlTitle'],
            );
        }
        $docInfo = $sitemap->getDocumentInfo($this->document->getID());
        $result[] = array(
            'Id' => $this->document->getID(),
            'Name' => strip_tags($docInfo['Name']),
            'Segment' => $sitemap->getURLByID($this->document->getID()),
            'Title' => $docInfo['HtmlTitle']
        );
        if (!empty($this->additionalCrumbs)) {
            $result = array_merge($result, $this->additionalCrumbs);
        }


        // добавляем информацию о главной странице в начало
        $defaultID = $sitemap->getDefault();
        if (($this->document->getID() != $defaultID) && (isset($result[0]) && ($result[0]['Id'] != $defaultID))) {
            $docInfo = $sitemap->getDocumentInfo($defaultID);
            $result = array_push_before(
                $result,
                array(
                    array(
                        'Id' => $defaultID,
                        'Name' => strip_tags($docInfo['Name']),
                        'Segment' => '',
                        'Title' => $docInfo['HtmlTitle']
                    )
                ),
                0
            );
        }

        return $result;
    }

    /**
     * Add crumb.
     *
     * Если приходят пустые параметры, то эта крошка не выводится, а предыдущая хлебная крошка будет ссылкой
     *
     * @param string|int $smapID ID.
     * @param string $smapName Name.
     * @param string $smapSegment Segment.
     *
     * @note If the input arguments are empty, then this crumb will be not showed and previous crumb become a link.
     */
    public function addCrumb($smapID = '', $smapName = '', $smapSegment = '') {
        $this->additionalCrumbs[] = array(
            'Id' => $smapID,
            'Name' => $smapName,
            'Segment' => $smapSegment
        );
    }

    /**
     * Replace data.
     * @param array $data Data in the form @code array(array('Id'=>'', 'Name'=>'', 'Segment'=>'')) @endcode
     */
    public function replaceData($data) {
        $d = new Data();
        $d->load($data);
        $this->setData($d);
    }

    /**
     * Remove portion from additioan crumbs
     * @param int $indexFromEnd Индекс
     */
    public function removeCrumb($indexFromEnd){
        $this->additionalCrumbs = array_slice($this->additionalCrumbs, 0 , -$indexFromEnd);
    }
}
