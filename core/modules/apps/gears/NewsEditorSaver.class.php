<?php

class NewsEditorSaver extends ExtendedFeedSaver {

    public function setData(Data $data) {
        parent::setData($data);
        if (!$data->getFieldByName('news_segment')->getRowData(0)) {
            $f = new Field('news_segment');
            $translitedTitle = Translit::asURLSegment($data->getFieldByName('news_title')->getRowData(0));
            for ($i = 0, $l = sizeof(E()->getLanguage()->getLanguages()); $i < $l; $i++)
                $f->setRowData($i, $translitedTitle);

            $data->addField($f);
        }
    }

}
