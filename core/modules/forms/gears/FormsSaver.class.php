<?php

class FormsSaver extends Saver{

    public function save(){
        $result = parent::save();
        if($this->getMode() == QAL::INSERT){

        }
        return $result;
    }
}
