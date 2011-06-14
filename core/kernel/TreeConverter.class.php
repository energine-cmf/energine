<?php

/**
 * Содержит класс TreeConverter
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2007
 */

//require_once('TreeNodeList.class.php');

/**
 * Конвертер для превращения древовидного массива в объект Tree
 * По сути представляет из себя контейнер статических методов
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @final
 */
final class TreeConverter{
    /**
     * Имя поля - ключа
     *
     * @var string
     * @access private
     * @static
     */
    static private $keyName;
    /**
     * Имя поля - родительского ключа
     *
     * @var string
     * @access private
     * @static
     */
    static private $parentKeyName;

    /**
     * Список узлов
     *
     * @var TreeNodeList
     * @access private
     * @static
     */
    static private $treeNodeList;


	private function __construct() {}

	/**
	 * Превращает переданный массив в дерево
	 *
     * @param array Загружаемые данные
     * @param string название поля содержащего идентификатор
     * @param string название поля содержащего идентификатор родителя
	 * @return TreeNodeList
	 * @access public
	 * @static
	 */

	static public function convert(array $data, $keyName, $parentKeyName) {
        self::$keyName = $keyName;
        self::$parentKeyName = $parentKeyName;

        //Проверяем данные на правильность
	    if (!self::validate($data)) {
            throw new Exception('Неправильный формат древовидных данных');
        }
        return self::iterate($data, self::$treeNodeList = new TreeNodeList());
	}

    /**
     * Проверяет входные данные на валидность
     *
     * @param array
     * @return bool
     * @access private
     * @static
     * @todo реализовать
     */

    static private function validate(array $data) {
    	foreach ($data as $value) {
    		if (!array_key_exists(self::$parentKeyName, $value) || !array_key_exists(self::$parentKeyName, $value)) {
    			return false;
    		}
    		elseif($value[self::$keyName] === $value[self::$parentKeyName]) {
    			return false;
    		}
    	}
        return true;
    }

    /**
     * Рекурсивный метод итерации по исходному древовидному массиву
     *
	 * @param array массив данных в формате array(array('$keyName'=>$key, '$parentKeyName'=>$parentKey))
	 * @param mixed родительский объект (может быть TreeNode или TreeNodeList)
     * @return TreeNodeList
     * @access private
     * @static
     */

    static private function iterate(array $data, $parent) {
        foreach ($data as $key => $value) {
        	//Если родителем является TreeNodeList  - значит мы на начальном шаге итерации и ключ - пустой, во всех других случаях - ключом является идентификатор узла родителя
        	if ($parent instanceof TreeNodeList) {
        	   $parentKey = '';
        	   $methodName = 'add';
        	}
        	else {
        	   $parentKey = $parent->getID();
        	   $methodName = 'addChild';
        	}

        	if ($value[self::$parentKeyName] == $parentKey) {
        		//добавляем узел к родителю
        		$addedNode = $parent->$methodName(new TreeNode($value[self::$keyName]));
        		//удаляем из массива данных
        		unset($data[$key]);
        		//делаем рекурсивный вызов, передавая изменившийся набор данных, и родительский узел
        		self::iterate($data, $addedNode);
        	}
        }
        return $parent;
    }
}