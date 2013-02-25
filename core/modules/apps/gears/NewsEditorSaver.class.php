<?php

class NewsEditorSaver extends ExtendedSaver {

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
            for($i=0; $i<10; $i++){
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
