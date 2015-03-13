<?php
/**
 * @file
 * TreeNodeList, TreeNode.
 *
 * It contains the definition to:
 * @code
class TreeNodeList;
final class TreeNode;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2007
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * List of nodes.
 *
 * @code
class TreeNodeList;
@endcode
 */
class TreeNodeList implements \Iterator{
    /**
     * Internal pointer.
     * @var mixed $currentKey
     */
    private $currentKey;

    /**
     * Node list.
     * Array of TreeNode.
     * @var array $nodeList
     */
    private $nodeList = array();

    public function __construct() {

    }

    /**
     * Add node.
     *
     * @param TreeNode $node New node.
     * @return TreeNode
     */
    public function add(TreeNode $node) {
        $this->nodeList[$node->getID()] = $node;
        return $node;
    }

    /**
     * Get root node.
     * @return TreeNode
     */
    public function getRoot(){
        $k = array_keys($this->nodeList);
        $k = current($k);
        return $this->nodeList[$k];
    }

    /**
     * Insert node before target node.
     *
     * @param TreeNode $node Node that will be inserted.
     * @param TreeNode $beforeNode Target node.
     * @return TreeNode
     */
    public function insertBefore(TreeNode $node, TreeNode $beforeNode) {
    }

    //todo VZ: Why this function has return value?
    /**
     * Remove node.
     *
     * @param TreeNode $node Node.
     * @return TreeNode
     */
    public function remove(TreeNode $node) {
    	$result = $node;
		unset($this->nodeList[$node->getID()]);

		return $result;
    }

    /**
     * Get the length of node list.
     *
     * @return int
     */
    public function getLength() {
		return sizeof($this->nodeList);
    }

    /**
     * Get node by his ID.
	 *
	 * @param int $id Node ID.
	 * @return TreeNode
	 */
    public function getNodeById($id) {
		return $this->findNode($id, $this);
    }

    /**
     * Find the node in node list.
     *
     * @param int $id Node ID.
     * @param TreeNodeList $nodeList Node list.
     * @return TreeNode|null
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
     * Get the tree as an array.
	 *
	 * @param bool $isRecursive Is recursive?
	 * @return array
	 */
    public function asList($isRecursive = true) {
        $result = array();
        foreach ($this as $node) {
            $result += $node->asList($isRecursive);
        }

        return $result;
    }

	public function current() {
		return $this->nodeList[$this->currentKey];
	}

	public function key() {
		return $this->currentKey;
	}

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

	public function rewind() {
		if(empty($this->nodeList)) return;
		//получаем все ключи
		$keys = array_keys($this->nodeList);
		//меняем местами ключ со значением, получая индексы
		$this->currentKey = $keys[0];
	}

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
     * Merge node lists.
	 *
	 * @param TreeNodeList $newNodeList New node list.
	 * @return TreeNodeList
	 */
	public function merge(TreeNodeList $newNodeList) {
        $this->nodeList = array_merge($this->nodeList, $newNodeList->nodeList);
        return $this;
	}
}

/**
 * Tree's node manager.
 *
 * @code
final class TreeNode;
@endcode
 *
 * @final
 */
final class TreeNode implements \IteratorAggregate{
    /**
     * Node ID.
     * @var int $id
     */
    private $id;
    /**
     * Parent node.
     * @var TreeNode $parent
     */
    private $parent = null;
    /**
     * Array of children nodes.
     * @var TreeNodeList $children
     */
    private $children;

    /**
     * @param int $id Node ID.
     */
    public function __construct($id) {
        $this->children = new TreeNodeList();
        $this->id = $id;
    }

    /**
     * Get node ID.
     *
     * @return int
     */
    public function getID() {
        return $this->id;
    }

    /**
     * Get parent node.
     *
     * @return TreeNode
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * Check if this node has children.
     *
     * @return bool
     */
    public function hasChildren() {
        return (bool)$this->children->getLength();
    }

    /**
     * Get children.
     *
     * @return TreeNodeList
     */
    public function getChildren() {
        return $this->children;
    }

    public function getIterator() {
        return $this->getChildren();
    }

    //todo VZ: Why the input node is returned?
    /**
     * Add child node.
     *
     * @param TreeNode $node Child node.
     * @return TreeNode
     */
    public function addChild(TreeNode $node) {
        $node = $this->children->add($node);
        $node->parent = $this;

        return $node;
    }

    /**
     * Remove child node.
     *
     * @param TreeNode $node Child node.
     * @return TreeNode
     */
    public function removeChild($node) {
        $this->children->remove($node)->parent = null;
        return $node;
    }

    /**
     * Get all parents of this node.
     *
     * @return TreeNodeList
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
     * Get all descendants of this node.
     *
     * @return TreeNodeList
     */
    public function getDescendants() {
        $result = $this->iterateDescendants($this->getChildren());
        return $result;
    }

    /**
     * Iterate over descendants and get them all.
     *
     * @param TreeNodeList $nodeList Node list.
     * @return TreeNodeList
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
     * Get this object as an array.
     *
     * @param bool $isRecursive Is recursive?
     * @return array
     */
    public function asList($isRecursive = true) {
        $result[$this->getID()] = (!is_null($this->getParent()))?$this->getParent()->getID():null;
        if ($this->hasChildren() && $isRecursive) {
            $result += $this->getChildren()->asList();
        }
        return $result;
    }
}