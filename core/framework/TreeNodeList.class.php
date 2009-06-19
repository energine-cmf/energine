<?php

/**
 * Содержит класс Tree
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id$
 */

//require_once('TreeNode.class.php');

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