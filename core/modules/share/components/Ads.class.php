<?php
/**
 * Содержит класс Ads
 *
 * @package energine
 * @subpackage share
 * @author spacelord
 * @copyright Energine 2011
 * @version $Id$
 */

/**
 *
 * @package energine
 * @subpackage share
 * @author spacelord
 */
class Ads extends DataSet
{
    const TABLE_NAME = 'share_ads';
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null)
    {
        parent::__construct($name, $module, $params);
    }
    protected function main(){
        parent::main();

        $IDs = array_keys(E()->getMap()->getParents($this->document->getID()));
        $IDs[] = $this->document->getID();
        
        //Get segment ads or ads of his next parent
        $result = $this->dbh->select(self::TABLE_NAME, true, array('smap_id' => $IDs), array('smap_id' => QAL::DESC), 1);

        if(is_array($result)){
            //We don't need smap_id, so don't write it to Data
            unset($result[0]['smap_id']);
            foreach ($result[0] as $key => $value) {
                $fd = new FieldDescription($key);
                if (in_array($key, array('ad_id'))) {
                    $fd->setType(FieldDescription::FIELD_TYPE_INT);
                } else {
                    $fd->setType(FieldDescription::FIELD_TYPE_TEXT);
                }
                $this->getDataDescription()->addFieldDescription($fd);
            }
            $this->getData()->load($result);
        }
    }
}