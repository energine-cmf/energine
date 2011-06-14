<?php
/**
 * Містить клас Ads.
 * Відповідає за виборку реклами на сторінках сайту.
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
class Ads extends DataSet{
    
    protected function main(){
        parent::main();

        $IDs = array_keys(E()->getMap()->getParents($this->document->getID()));
        $IDs[] = $this->document->getID();
        
        //Get segment ads or ads of his next parent
        $result = $this->dbh->select(AdsManager::TABLE_NAME, true, array('smap_id' => $IDs), array('smap_id' => QAL::DESC), 1);

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