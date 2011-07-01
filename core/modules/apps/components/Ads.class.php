<?php
/**
 * Містить клас Ads.
 * Відповідає за виборку реклами на сторінках сайту.
 *
 * @package energine
 * @subpackage apps
 * @author spacelord
 * @copyright Energine 2011
 */

/**
 * Клас для вывода рекламных блоков
 * находящихся в таблице apps_ads
 * Перечень блоков прописывается в виде полей
 * и привязывается по smap_id к разделу
 * Если не задан свой блок  - берется родительский
 *
 * @package energine
 * @subpackage apps
 * @author spacelord
 */
class Ads extends DataSet{
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