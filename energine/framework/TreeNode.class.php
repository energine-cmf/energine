<?php

/**
 * Содержит класс TreeNode
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright ColoCall 2007
 * @version $Id$
 */


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