<?php
/**
 * @file.
 * Ads
 *
 * It contains the definition to:
 * @code
class Ads;
@endcode
 *
 * @author spacelord
 * @copyright Energine 2011
 *
 * @version 1.0.0
 */
namespace Energine\apps\components;
use Energine\share\components\DataSet, Energine\apps\gears\AdsManager, Energine\share\gears\QAL, Energine\share\gears\FieldDescription;
/**
 * Class to show ad blocks.
 *
 * @code
class Ads;
@endcode
 *
 * @note Ads should be in the "apps_ads" table. The list of blocks is described in the form of fields and bounds by "smap_id" to the section.
 * If you do not specify your block then parent block will be token.
 */
class Ads extends DataSet{
    /**
     * @copydoc DataSet::__construct
     */
    public function __construct($name, $module, array $params = null){
        parent::__construct($name, $module, $params);
        if(!AdsManager::isActive()){
            $this->disable();
        }
    }

    /**
     * @copydoc DataSet::main
     */
    protected function main(){
        parent::main();
        //Сначала проверили есть ли свой код баннера
        $result = $this->dbh->select(AdsManager::TABLE_NAME, true, array('smap_id' => $this->document->getID()), array('smap_id' => QAL::DESC), 1);
        //Если нет собственного  - ищем у родителей
        if(!is_array($result)){
            $result = false;
            //Список идентфикаторов родителей в порядке увелечения уровня
            $IDs =  array_reverse(array_keys(E()->getMap()->getParents($this->document->getID())));
            $tmp = $this->dbh->select(AdsManager::TABLE_NAME, true, array('smap_id' => $IDs));
            if(is_array($tmp)){
                $tmp = convertDBResult($tmp, 'smap_id');
            }

            //перебираем записи родителей
            foreach($IDs as $id){
                //если есть родитель с рекламой
                if(isset($tmp[$id])){
                    $result = array($tmp[$id]);
                    //дальше смотреть нет смысла
                    break;
                }
            }
        }

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