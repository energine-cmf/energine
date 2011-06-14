<?php
/**
 * Список служебных компонентов
 * Вынесено в отдельный объект для
 * того чтобы дать возможность переопределить
 * системный компонент
 */
 
class SystemComponents extends Object{
    private $default = array(
        'files' => array('FileLibrary', 'share'),
        'divisions' => array('DivisionEditor', 'share'),
        'translations' => array('TranslationEditor', 'share'),
    );
    private $items = array();

    public function __construct(){
        $this->items = $this->default;
    }
}
