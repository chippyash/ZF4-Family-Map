<?php
/**
 * ZF4 Library
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Tree
 * @author 	Ashley Kitson
 * @copyright   ZF4 Business Limited 2011, UK
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE V3
 * 
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *    License text is located in /docs/LICENSE.FAMILYMAP.txt
 */


/**
 * Binary tree
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Tree
 */

class ZF4_Object_Tree_Binary implements ZF4_Object_Tree_Interface {

    /**
     * Internal tree array
     *
     * @var array
     */
    private $_tree = null;

    /**
     * Quick retrieval index
     *
     * @var array
     */
    private $_index = array();

    /**
     * Insert a tree node into the tree
     *
     * NB $parentKey is ignored as Binary tree will always
     * insert new node in correct place
     *
     * @param mixed $key the node key
     * @param mixed $data optional additional node data
     * @param mixed $parentkey the key of the parent node to attach this node to
     * @return Fluent_Interface
     */
    public function insert($key, $data = null, $parentKey = null) {
       	$node = $this->createTreeNode($key,$data);
        if ($this->_tree == null) {
            //simple case
            $this->_tree = $node;  //i.e. the root node
            $this->_index[$key] =& $this->_tree;
            return $this;
        } else {
            //find the place to put it, starting at the root
            $this->insertNode($node,$this->_tree);
        }
        return $this;
    }

    /**
     * Recursively search through tree and insert node in correct place
     *
     * @param ZF4_Object_Tree_Node $node The node to insert
     * @param ZF4_Object_Tree_Node $tree The tree index item to compare
     */
    private function insertNode($node, $tree) {
        if ($node->key == $tree->key) {
            throw new ZF4_Exception(sprintf($this->_translator->_("Cannot have duplicate keys (%s) in binary tree"),$node->key),Zend_Log::ERR );
        }
        if ($node->key > $tree->key) {
            if ($tree->children['right'] == null) {
            	//add the new node
            	$node->parent = $tree->key;
                $tree->children['right'] = $node;
                //store index to it
                $this->_index[$node->key] =& $tree->children['right'];
                return;
            } else {
            $this->insertNode($node, $tree->children['right']);
            }
        } else {
            if ($tree->children['left'] == null) {
            	//add the new node
            	$node->parent = $tree->key;
                $tree->children['left'] = $node;
                //store index to it
                $this->_index[$node->key] =& $tree->children['left'];
                return;
            } else {
            $this->insertNode($node, $tree->children['left']);
            }
        }
    }

    /**
     * Retrieve a subtree given its root node 'key'
     *
     * @param mixed $key
     * @return ZF4_Object_Tree_..|boolean a tree or false if not found
     */
    public function search($key) {
    	if (isset($this->_index[$key])) {
    		return $this->_index[$key];
    	} else {
    		return false;
    	}
    }

    /**
     * Return the root of the tree (i.e. the whole tree)
     *
     * @return ZF4_Object_Tree_Node The root node or null if not set
     */
    public function getRoot() {
        return $this->_tree;
    }

    /**
     * Create and initialise a new tree node
     * Use this method to create a new node that will work with the tree
     *
     * @param mixed $key the node key
     * @param mixed $data optional additional node data
     * @return ZF4_Object_Tree_Node
     */
    private function createTreeNode($key, $data = null) {
        $i = new ZF4_Object_Tree_Node();
        $i->children['left'] = null;
        $i->children['right'] = null;
        $i->key = $key;
        $i->data = $data;
        return $i;
    }

    /**
     * Delete the node with key $key from the tree
     *
     * @param mixed $key
     * @return boolean True on success
     */
    public function delete($key) {
    	if (!$nodeToDelete = $this->_index[$key]) {
    		//no node to delete
    		return false;
    	}
    	/* Simple Case 1 - Node has no children */
    	if ($nodeToDelete->children['left'] == null && $nodeToDelete->children['right'] == null ) {
    		$this->trash($key);
    		return true;
    	}
		$childLeft =& $this->_index[$nodeToDelete->children['left']->key];
		$childRight =& $this->_index[$nodeToDelete->children['right']->key];
		$nodeParent =& $this->_index[$nodeToDelete->parent];

    	/* Simple Case 2 - root Node has 2 children that have empty children */
    	if ($childLeft != null  && $childRight != null
					    		&& $childLeft->children['left'] == null
					    		&& $childLeft->children['right'] == null
					    		&& $childRight->children['left'] == null
					    		&& $childRight->children['right'] == null
					    		&& $nodeParent == null) {
			//set parent of left node be the right node
			$childLeft->parent = $childRight->key;
			//set the left child of the ntd's right child as parent of left node
			$childRight->children['left'] = $childLeft;
			//set right child as tree root
			$childRight->parent = null;
			$this->_tree =& $childRight;
			$this->trash($key);
    		return true;
    	}

    	/* Simple case 3 - Node only has right child */
    	if ($childLeft == null && $childRight != null) {
    		if ($nodeParent == null) {
    			//set right child as tree root
				$childRight->parent = null;
				$this->_tree =& $childRight;
    		} else {
    			//set new parent for right child
    			$childRight->parent = $nodeParent->key;
    			//set new child for parent node
    			if ($nodeParent->children['left']->key == $key) {
    				$nodeParent->children['left'] = $childRight;
    			} else {
    				$nodeParent->children['right'] = $childRight;
    			}
    		}
			$this->trash($key);
   			return true;
    	}

    	/* Simple case 4 - Node only has left child */
    	if ($childLeft != null && $childRight == null) {
    		if ($nodeParent == null) {
    			//set left child as tree root
				$childLeft->parent = null;
				$this->_tree =& $childleft;
    		} else {
    			//set new parent for left child
    			$childleft->parent = $nodeParent->key;
    			//set new child for parent node
    			if ($nodeParent->children['left']->key == $key) {
    				$nodeParent->children['left'] = $childLeft;
    			} else {
    				$nodeParent->children['right'] = $childleft;
    			}
    		}
			$this->trash($key);
   			return true;
    	}

    	/* Case 5 - complex cases - delete node T
    	1/ L := T↑L;
    	2/ R := T↑R
    	3/ P := Parent(T);
    	4/ K := X = T+1; (X == R ? X : R ++); Find lowest node of R such that it is at least T+1
    	5/ H := max(K)
    	6/ R := R - K; Remove node K from R
    	7/ K↑L = L
    	8/ H↑R = R
    	9/ trash(T)
    	9/ P↑oldT = K
    	*/
    	$T = $nodeToDelete;
    	$L = $childLeft;
    	$R = $childRight;
    	$P = $nodeParent;
    	//R is always > T therefore we only need to find its lowest node
    	$K = $this->_index[$this->findLowest($R)];
    	$H = $this->_index[$this->findHighest($K)];
    	if ($this->delete($K->key)) {
    		$this->insertNode($L,$K);
    		$this->insertNode($R,$H);
    		$this->trash($key);
    		$this->insertNode($K,$P);
    		return true;
    	} else {
    		return false;
    	}
    }

    /**
     * Find the lowest node in a tree
     * The lowest node is always the leftmost node in a binary tree
     *
     * @param ZF4_Object_Tree_Node $tree
     * @return ZF4_Object_Tree_Node
     */
    private function findLowest($tree) {
    	static $lowest = null;
    	if ($lowest == null) {
    		$lowest = $tree->key;
    	}
    	if ($tree->children['left'] != null && $tree->children['left']->key < $lowest) {
    		$lowest = $tree->children['left']->key;
    		$ret = $this->findLowest($tree->children['left']);
    	} else {
    		$ret = $lowest;
    	}
    	$lowest = null;  //cleanup for next call
    	return $ret;
    }

    /**
     * Find the highest node in the tree
     * Finds the rightmost node
     *
     * @param ZF4_Object_Tree_Node $tree
     * @return ZF4_Object_Tree_Node
     */
    private function findHighest($tree) {
    	static $highest = null;
    	if ($highest == null) {
    		$highest = $tree->key;
    	}
    	if ($tree->children['right'] != null && $tree->children['right']->key > $highest) {
    		$highest = $tree->children['right']->key;
    		$ret = $this->findHighest($tree->children['right']);
    	} else {
    		$ret = $highest;
    	}
    	$highest = null;  //cleanup for next call
    	return $ret;
    }

    /**
     * permanently remove a node
     *
     * @param mixed $key
     */
   	private function trash($key) {
   		$this->_index[$key] = null; //remove the node
   		unset($this->_index[$key]); //remove the index
    }

    /**
     * Create a balanced tree based on this one
     * It does this by deconstructing the tree and then creating a new
     * tree with the nodes.
     *
     * @return ZF4_Object_Tree_Binary
     */
    public function balance() {
    	$newTree = new ZF4_Object_Tree_Binary();
    	//ksort($this->_index);
    	$this->_balance($this->_index,$newTree);
    	return $newTree;
    }

    private function _balance($index, ZF4_Object_Tree_Binary &$tree) {
    	//find the middle node and insert into tree
    	$c = count($index);
    	$midPoint = intval(round($c / 2));
    	$chunks = array_chunk($index,$midPoint,true);
    	$left = $chunks[0]; //last item of left will be middle node
    	$right = (count($chunks) == 2 ? $chunks[1] : null);
    	$node = array_pop($left);
    	$tree->insert($node->key,$node->data);
    	if (count($left)>0) {
    		$this->_balance($left,$tree);
    	}
    	if (count($right)>0) {
    		$this->_balance($right,$tree);
    	}
    }

    /**
     * Return all end nodes (leaves) of the tree
     *
     * @return array of ZF4_Object_Tree_Node
     */
    public function getLeaves(){
    	$ret = array();
    	foreach ($this->_index as $node) {
    		if (count($node->children) == 0) {
    			$ret[] = clone $node;
    		}
    	}
    	return $ret;
    }

    /**
     * Convert a node to a tree
     *
     * @param ZF4_Object_Tree_Node $node
     * @return ZF4_Object_Tree_Binary
     */
    public function convertToTree(ZF4_Object_Tree_Node $node) {
    	$tree = new ZF4_Object_Tree_Binary();
    	$this->_convert($tree,$node,0);
    	return $tree;
    }

    private function _convert(ZF4_Object_Tree_Binary &$tree,ZF4_Object_Tree_Node $node, $parent) {
    	$tree->insert($node->key,$node->data,$parent);
    	if (count($node->children) > 0) {
    		foreach ($node->children as $child) {
    			$this->_convert($tree,$child,$node->key);
    		}
    	}
    }

    /**
     * flag to determined if we need to load css and js for render function
     *
     * @var boolean
     */
    private static $_renderLoaded = false;
    /**
     * Render the tree in html
     *
     * @param $controller Controller calling the render function
     * @param string $name name of display item
     * @param mixed $config Configuration parameters for the rendering
     */
    public function render(Zend_Controller_Action &$controller, $name='default', $config=null) {
    	if ($this->_tree == null) {
    		return '';
    	} else {
    		//set up controller to read in css and js scripts
    		if (!self::$_renderLoaded) {
    			ZF4_Service_Manager::getService(ZF4_SRVC_JAVASCRIPT)
    				->load('ECOTree',$controller->view);
    			self::$_renderLoaded = true;
    		}
    		//construct html output
    		$html = "<div id='ecoTreeDisplay_{$name}'></div>\n";
    		$html .= "<script type='text/javascript'>\n";
    		$html .= "var myTree{$name} = new ECOTree('myTree{$name}','ecoTreeDisplay_{$name}');\n";
    		//we always set the canvas name
    		$html .= "myTree{$name}.config.canvasName = 'ECOTreecanvas_{$name}';\n";
    		//if no config given, then use the default one
    		if ($config == null) $config = new ZF4_Object_Tree_Render_Ecotree();
    		//render all the other config items
    		if ($config != null && $config instanceof ZF4_Object_Tree_Render_Ecotree ) {
    			foreach ($config as $key=>$value) {
   					$html .= "myTree{$name}.config.{$key} = ";
    				if (is_array($value)) {
    					foreach ($value as $k2=>$v2) {
    						if (is_string($v2)) $value[$k2] = "'{$v2}'";
    					}
    					$v = "[" . implode(",",$value) . "]";
    				} else {
    					$v = $value;
    				}
    				if (is_string($v) && !is_array($value)) {
    					$html .= "'{$v}'";
    				} elseif (is_bool($v)) {
    					$html .= ($v ? 'true' : 'false');
    				}else {
    					$html .= $v;
    				}
    				$html .= ";\n";
    			}
    		}
    		/*
    		foreach ($this->_index as $node) {
    			$html .= "myTree{$name}.add({$node->key}," . ($node->parent == null ? -1 : $node->parent) . ",'{$node->key}');\n";
    		}
    		*/
    		$html .= $this->_render($this->_tree, $name, $config->zf4ShowEmptyNodes);
    		$html .= "myTree{$name}.UpdateTree();\n";
    		$html .= "</script>\n";


    		return $html;
    	}
	}

	/**
	 * Walk through tree and create html to display nodes
	 *
	 * @param ZF4_Object Tree_Binary $tree
	 * @param string $name display tree name
	 * @param boolean $showEmptyNodes True if we are showing empty nodes
	 * @return string
	 */
	private function _render($tree, $name, $showEmptyNodes) {
		$html = "myTree{$name}.add('{$tree->key}','" . ($tree->parent == null ? -1 : $tree->parent) . "','{$tree->key}');\n";
		if ($tree->children['left'] != null) {
			$html .= $this->_render($tree->children['left'], $name, $showEmptyNodes);
		} elseif ($showEmptyNodes && $tree->children['right'] != null) {
			$emptyNodeId = rand();
			$html .= "myTree{$name}.add('{$emptyNodeId}','{$tree->key}','',10,10,'black','black');\n";
		}
		if ($tree->children['right'] != null) {
			$html .= $this->_render($tree->children['right'], $name, $showEmptyNodes);
		} elseif ($showEmptyNodes && $tree->children['left'] != null) {
			$emptyNodeId = rand();
			$html .= "myTree{$name}.add('{$emptyNodeId}','{$tree->key}','',10,10,'black','black');\n";
		}
		return $html;
	}
}