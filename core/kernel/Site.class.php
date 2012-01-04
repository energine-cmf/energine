<?php 
/**
 * Содержит класс Site
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Работа с сайтом
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka@gmail.com
 */
class Site extends DBWorker {
    /**
     * Данные сайта
     *
     * @access private
     * @var array
     */
    private $data;

    /**
     * Конструктор класса
     *
     * @param array $data
     * @access public
     */
    public function __construct($data) {
        parent::__construct();
        $this->data = convertFieldNames($data, 'site_');
    }

    /**
     * Возвращает информацию о всех сайтах в виде массив объектов Site
     * @static
     * @return Site[]
     */
    public static function load() {
        $result = array();
        $res = E()->getDB()->select('share_sites');
        foreach ($res as $siteData) {
            $result[$siteData['site_id']] = new Site($siteData);
        }
        return $result;
    }

    /**
     * Загружается информация о домене
     *
     * @return void
     */
    public function setDomain($domainData) {
        $this->data = array_merge($this->data, $domainData);
        $this->data['base'] =
                $this->data['protocol'] . '://' .
                $this->data['host'] . (($this->data['port'] == 80) ? '' : ':' . $this->data['port']) .
                $this->data['root'];
    }

    /**
     * Magic method возврщающий свойства сайта
     *
     * @return Object
     * @access public
     */
    public function __get($propName) {
        $result = null;
        if (isset($this->data[$propName])) {
            $result = $this->data[$propName];
        }
        elseif (strtolower($propName) == 'name') {
            //@todo - нужно бы получать информацию обо всех сайтах, проблема в том что на момент первого обращения не известен текущий язык
            $result =
            $this->data[$propName] =
                    simplifyDBResult(
                        $this->dbh->select(
                            'share_sites_translation',
                            'site_name',
                            array(
                                 'lang_id' => E()->getLanguage()->getCurrent(),
                                 'site_id' => $this->data['id']
                            )
                        ),
                        'site_name',
                        true
                    );
        }
        return $result;
    }

}