<?php

require_once('Singleton.class.php');

class Memcacher extends Singleton{
    /**
     * Состояние мемкеша
     * @var bool
     */
    private $enabled;
    /**
     * Собственно объект мемкеша
     * @var Memcached
     */
    private $memcache;
    
    public function __construct(){
        if($this->enabled = (bool)$this->getConfigValue('cache.enable')){
            $this->memcache = new Memcached();
            $this->memcache->addServer($this->getConfigValue('cache.host'), $this->getConfigValue('cache.port'));
        }
    }
    /**
     * Возвращает состояние Мемкеша
     * @return bool
     */
    public function isEnabled(){
        return $this->enabled;
    }
    /**
     * Сохраняет данные в мемкеше
     *
     * @param  $key string
     * @param  $value mixed
     * @param bool $expiration
     * @return bool
     */
    public function store($key, $value, $expiration = false){
        $result = false;
        if($this->enabled){
            $result = $this->memcache->set($key, json_encode($value));
        }
        return $result;
    }
    /**
     * Получает данные из мемкеша
     * @param  $key string
     * @return mixed
     */
    public function retrieve($key){
        $result = null;
        if($this->enabled){
            if($result = $this->memcache->get($key)){
                $result = json_decode($result, true);
            }
        }
        return $result;
    }
}
