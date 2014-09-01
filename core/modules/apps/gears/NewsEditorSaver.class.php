<?php
/**
 * @file
 * NewsEditorSaver
 *
 * It contains the definition to:
 * @code
class NewsEditorSaver;
@endcode
 *
 * @version 1.0.0
 */
namespace Energine\apps\gears;
use Energine\share\gears\Data, Energine\share\gears\ExtendedSaver, Energine\share\gears\Translit, Energine\share\gears\Field;
/**
 * Saver for news editor.
 *
 * @code
class NewsEditorSaver;
@endcode
 */
class NewsEditorSaver extends ExtendedSaver {
    /**
     * @copydoc ExtendedSaver::setData
     */
    public function setData(Data $data) {
        parent::setData($data);
        if (!$data->getFieldByName('news_segment')->getRowData(0)) {
            $f = new Field('news_segment');
            $translitedTitle = Translit::asURLSegment($data->getFieldByName('news_title')->getRowData(0));
            $filter = array();
            $pkF = $this->getData()->getFieldByName($this->getPK());

            if($pkF = $pkF->getRowData(0)){
                $filter[$this->getPK()] = $pkF;
            }
            for($i=0; $i<30; $i++){
                $tempTitle = $translitedTitle;
                if($i){
                    $tempTitle = $translitedTitle.'-'.$i;
                }

                $filter = array('news_segment' => $tempTitle);
                if(!$this->dbh->getScalar($this->getTableName(), 'news_segment', $filter)){
                    break;
                }
                else {
                    $tempTitle = $translitedTitle.'-'.$i;
                }
            }

            $translitedTitle = $tempTitle;
            for ($i = 0, $l = sizeof(E()->getLanguage()->getLanguages()); $i < $l; $i++)
                $f->setRowData($i, $translitedTitle);

            $data->addField($f);
        }
    }

}
