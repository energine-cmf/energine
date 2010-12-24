<?php

/**
 * Содержит классы TreeNodeList и TreeNode
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2007
 */


/**
 * Набор узлов
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class TreeNodeList implements Iterator{
	/**
     * Внутренний указатель
     *
     * @var mixed
     * @access private
     */
    private $currentKey;

    /**
     * Массив узлов
     *
     * @var array
     * @access private
     */
    private $nodeList = array();

    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Добавляет узел
     *
     * @param TreeNode
     * @return TreeNode
     * @access public
     */

    public function add(TreeNode $node) {
        $this->nodeList[$node->getID()] = $node;
        return $node;
    }
    public function getRoot(){
        $k = array_keys($this->nodeList);
        $k = current($k);
        return $this->nodeList[$k];
    }

    /**
     * Вставляет узел перед указанным узлом
     *
     * @param TreeNode
     * @param TreeNode
     * @return TreeNode
     * @access public
     */

    public function insertBefore(TreeNode $node, TreeNode $beforeNode) {

    }

    /**
     * Удаляет елемент из списка
     *
     * @param TreeNode
     * @return TreeNode
     * @access public
     */

    public function remove(TreeNode $node) {
    	$result = $node;
		unset($this->nodeList[$node->getID()]);

		return $result;
    }

    /**
     * Возвращает количество елментов в списке
     *
     * @return int
     * @access public
     */

    public function getLength() {
		return sizeof($this->nodeList);
    }

    /**
	 * Возвращает узел по его идентификатору
	 *
	 * @param int
	 * @return TreeNode
	 * @access public
	 */

    public function getNodeById($id) {
		return $this->findNode($id, $this);
    }

    /**
     * Внутренний метод поиска узла по его идентификатору
     *
     * @return mixed
     * @access private
     */

    private function findNode($id, TreeNodeList $nodeList) {
		foreach ($nodeList as $node) {
			if ($node->getID() == $id) {
				return $node;
			}
			elseif($node->hasChildren()) {
				$result = $this->findNode($id, $node->getChildren());
				if (!is_null($result)) {
					return $result;
				}
			}
		}
		return null;
    }

    /**
	 * Возвращает дерево в виде массива
	 *
	 * @param bool рекурсия
	 * @return array
	 * @access public
	 */

    public function asList($isRecursive = true) {
        $result = array();
        foreach ($this as $node) {
            $result += $node->asList($isRecursive);
        }

        return $result;
    }

	/**
	 * Возвращает текущий елемент
	 *
	 * @see Iterator
	 * @return unknown
	 * @access public
	 */
	public function current() {
		return $this->nodeList[$this->currentKey];
	}

	/**
	 * Возвращает значение внутреннего указателя
	 *
	 * @see Iterator
	 * @return int
	 * @access public
	 */
	public function key() {
		return $this->currentKey;
	}

	/**
	 * Устанавливает внутренний указатель на последний елемент
	 *
	 * @see Iterator
	 * @return void
	 * @access public
	 */
	public function next() {
		//получаем все ключи
		$keys = array_keys($this->nodeList);
		//меняем местами ключ со значением, получая индексы
		$indexes = array_flip($keys);
		//получаем индекс текущего ключа
		$currentIndex = $indexes[$this->currentKey];
		$currentIndex++;
		if(isset($keys[$currentIndex])) {
			$this->currentKey = $keys[$currentIndex];
		}
		else {
			$this->currentKey = null;
		}
	}

	/**
	 * Устанавливает внутренний указатель на первый елемент
	 *
	 * @see Iterator
	 * @return void
	 * @access public
	 */
	public function rewind() {
		if(empty($this->nodeList)) return;
		//получаем все ключи
		$keys = array_keys($this->nodeList);
		//меняем местами ключ со значением, получая индексы
		$this->currentKey = $keys[0];
	}

	/**
	 * Возвращает bool в зависимости от того является ли текущий елемент последним или нет в списке
	 *
	 * @see Iterator
	 * @return boolean
	 * @access public
	 */
	public function valid() {
		if(!is_null($this->currentKey)){
			$keys = array_keys($this->nodeList);
			$indexes = array_flip($keys);
			if(!isset($indexes[$this->currentKey])) {
				$result = false;
			}
			else {
				$result = $indexes[$this->currentKey] < sizeof($indexes);
			}
		}
		else {
			$result = false;
		}
		return $result;
	}

	/**
	 * Пересекает списки узлов
	 *
	 * @param TreeNodeList
	 * @return TreeNodeList
	 * @access public
	 */

	public function merge(TreeNodeList $newNodeList) {
        $this->nodeList = array_merge($this->nodeList, $newNodeList->nodeList);
        return $this;
	}
	
}

/**
 * Класс реализующий работу с узлом дерева
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @final
 */
final class TreeNode implements IteratorAggregate{
    /**
     * Идентификатор узла
     *
     * @var int
     * @access private
     */
    private $id;
    /**
     * Идентификатор родительского узла
     *
     * @var TreeNode
     * @access private
     */
    private $parent = null;
    /**
     * Массив дочерних узлов
     *
     * @var TreeNodeList
     * @access private
     */
    private $children;

    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($id) {
        $this->children = new TreeNodeList();
        $this->id = $id;
    }

    /**
     * Возвращает идентификатор узла
     *
     * @return int
     * @access public
     */

    public function getID() {
        return $this->id;
    }

    /**
     * Возвращает родительский узел
     *
     * @return TreeNode
     * @access public
     */

    public function getParent() {
        return $this->parent;
    }

    /**
     * Возвращает флаг указывающий на наличие детей
     *
     * @return bool
     * @access public
     */

    public function hasChildren() {
        return (bool)$this->children->getLength();
    }

    /**
     * Возвращает массив потомков
     *
     * @return TreeNodeList
     * @access public
     */

    public function getChildren() {
        return $this->children;
    }
    /**
     * Возвращает итератор объекта
     *
     * @see IteratorAggregate
     * @return TreeNodeList
     * @access public
     */
    public function getIterator() {
        return $this->getChildren();
    }

    /**
     * Добавление узла как дочернего
     *
     * @param TreeNode
     * @return TreeNode
     * @access public
     */

    public function addChild(TreeNode $node) {
        $node = $this->children->add($node);
        $node->parent = $this;

        return $node;
    }

    /**
     * Удаление узла из списка дочерних узлов
     *
     * @param TreeNode
     * @return TreeNode
     * @access public
     */

    public function removeChild($node) {
        $this->children->remove($node)->parent = null;
        return $node;
    }

    /**
     * Возвращает всех родителей узла
     *
     * @return TreeNodeList
     * @access public
     */

    public function getParents() {
        $result = new TreeNodeList();
        $node = $this;
        while (!is_null($node)) {
            if (!is_null($node = $node->getParent())) {
                $result->add($node);
            }
        }
        return $result;
    }

    /**
     * Возвращает всех потомков
     *
     * @return TreeNodeList
     * @access public
     */

    public function getDescendants() {
        $result = $this->iterateDescendants($this->getChildren());
        return $result;
    }

    /**
     * Внутренний метод возвращаения потомков
     *
     * @return TreeNodeList
     * @access private
     */

    private function iterateDescendants(TreeNodeList $nodeList) {
        $result = new TreeNodeList();
        foreach ($nodeList as $node) {
            $result->add($node);
            $result->merge($node->iterateDescendants($node->getChildren()));
        }
        return $result;
    }

    /**
     * Возвращает объект в виде массива
     *
     * @param bool рекурсия
     * @return array
     * @access public
     */

    public function asList($isRecursive = true) {
        $result[$this->getID()] = (!is_null($this->getParent()))?$this->getParent()->getID():null;
        if ($this->hasChildren() && $isRecursive) {
            $result += $this->getChildren()->asList();
        }
        return $result;
    }
}