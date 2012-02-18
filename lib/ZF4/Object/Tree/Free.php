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
 * ZF4 'Free' Tree class
 * A tree that can have many child nodes
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Tree
 */

class ZF4_Object_Tree_Free implements ZF4_Object_Tree_Interface {

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
     * 
     *
     * @var ZF4_Translator
     */
    private $_translator;    

    public function __construct() {
    	$this->_translator = Zend_Registry::get(ZF4_Application_Resource_Language::REGKEY_TRANSLATE );
    }
    
	/**
	 * Proxy to translation service
	 *
	 * @param string $text
	 * @return string
	 */
	final public function _($text) {
		if (!is_null($this->_translator)) {
			return $this->_translator->_($text);
		} else {
			return $text;
		}
	}    
	
    /**
     * Insert a tree node into the tree
     *
     * @param mixed $key the node key
     * @param mixed $data optional additional node data
     * @param mixed $parentkey the key of the parent node to attach this node to
     * @return Fluent_Interface
     */
    public function insert($key, $data = null, $parentKey = null) {
    	if ($parentKey == null) {
    		$treeToUse =& $this->_tree;
    	} elseif (isset($this->_index[$parentKey])) {
    		$treeToUse = $this->_index[$parentKey];
    	} else {
    		$treeToUse = null;
    	}
       	$node = $this->createTreeNode($key,$data);
        if ($treeToUse == null) {
            //simple case
            $this->_tree = $node;  //i.e. the root node
            $this->_index[$key] =& $this->_tree;
        } else {
            //find the place to put it
            $this->insertNode($node,$treeToUse);
        }
        return $this;
    }

    /**
     * Search through children of tree and insert new node at end if it doesn't exist
     * If it does, then abort
     *
     * @param ZF4_Object_Tree_Node $node The node to insert
     * @param ZF4_Object_Tree_Node $tree The tree index item to compare
     */
    private function insertNode($node, $tree) {
    	//simple case - tree is null so create new one
    	if ($tree == null) {
    		$this->_tree = $node;
    		return;
    	}
    	//set the node parent
    	$node->parent = $tree->key;
    	//simple case - no children - create a new child
    	if (count($tree->children) == 0) {
    		$tree->children[$node->key] =& $node;
			//store index to it
            //$this->_index[$node->key] =& $tree->children[$node->key];
            $this->_index[$node->key] =& $node;
    		return;
    	}
    	//simple case - child exists
    	if (isset($tree->children[$node->key]) && $tree->children != null) {
    		throw new ZF4_Exception(sprintf($this->_translator->_("Cannot have duplicate keys (%s) in same branch of tree"),$node->key),Zend_Log::ERR );
    	}
    	//otherwise - add child
    	$tree->children[$node->key] = $node;
		//store index to it
        $this->_index[$node->key] =& $tree->children[$node->key];
    }

    /**
     * Retrieve a subtree of nodes given its root node 'key'
     * The returned Node will have children if present
     * Use convertToTree($returnedNode) to create a new tree
     *
     * @param mixed $key
     * @return ZF4_Object_Tree_Node|boolean a tree or false if not found
     */
    public function search($key) {
    	if (array_key_exists($key,$this->_index)) {
    		return $this->_index[$key];
    	} else {
    		return false;
    	}
    }

    /**
     * Modify (replace) the data in node
     *
     * @param mixed $key
     * @param array $data
     * @return Fluent_Interface
     */
    public function modify($key,$data) {
    	if (array_key_exists($key,$this->_index)) {
    		$this->_index[$key]->data = $data;
    	}
    	return $this;
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
     * @return ZF4_Object_Tree_Free
     */
    public function convertToTree(ZF4_Object_Tree_Node $node) {
    	$tree = new ZF4_Object_Tree_Free();
    	$this->_convert($tree,$node,0);
    	return $tree;
    }

    private function _convert(ZF4_Object_Tree_Free &$tree,ZF4_Object_Tree_Node $node, $parent) {
    	$tree->insert($node->key,$node->data,$parent);
    	if (count($node->children) > 0) {
    		foreach ($node->children as $child) {
    			$this->_convert($tree,$child,$node->key);
    		}
    	}
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
        $i->children = array();
        $i->key = $key;
        $i->data = $data;
        return $i;
    }

    /**
     * Delete the node with key $key from the tree
     * This routine will not attempt to relocate any child
     * nodes - it doesn't make sense to do so for a free tree.
     * It will simply delete the node and ANY children
     *
     * @param mixed $key
     * @return boolean True on success
     */
    public function delete($key) {
    	if (!isset($this->_index[$key])) {
    		//no node to delete
    		return false;
    	} else {
    		$this->trash($key);
    		return true;
    	}
    }

    /**
     * Delete a menu item by its name
     *
     * @param string $name
     * @return boolean true on success else false
     */
    public function deleteByName($name) {
    	$ret = false;
    	foreach ($this->_index as $id => $item) {
    		if ($item->data['name'] == $name) {
    			$ret = $this->delete($id);
    			break;
    		}
    	}
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
   		unset($this->_tree->children[$key]);  //remove the tree element
    }

    /**
     * flag to determined if we need to load css and js for render function
     *
     * @var boolean
     */
    private static $_renderLoaded = false;
    /**
     * Render the tree in html using ECO Tree display
     *
     * @param $controller Controller calling the render function
     * @param string $name name of display item
     * @param string $startMode collapsed|expanded
     * @param boolean $expandRoot Expand the root node. Only effective if $startMode = 'collapsed'
     * @param ZF4_Object_Tree_Render_Ecotree $config Configuration parameters for the rendering
     * @return string HTML to display tree
     */
    public function render(Zend_Controller_Action &$controller, $name='default', $startMode = 'collapsed', $expandRoot = true, $config=null) {
    	if ($this->_tree == null) {
    		return '';
    	} else {
    		//set up controller to read in css and js scripts
    		ZF4_Service_Manager::getService(ZF4_SRVC_JAVASCRIPT)
    			->load('ECOTree', $controller->view);
    		//construct html output
    		$html = "<div id='ecoTreeDisplay_{$name}' class='ecoTreeDisplay'></div>\n";
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
    		$html .= $this->_render($this->_tree, $name);

    		if ($startMode == 'collapsed') {
    			$html .= "myTree{$name}.collapseAll();\n";
    			if ($expandRoot) {
    				$html .= "myTree{$name}.collapseNode(0,true);\n";
    			}
    		} elseif ($startMode == 'expanded') {
    			$html .= "myTree{$name}.expandAll();\n";
    		} else {
    			$html .= "myTree{$name}.UpdateTree();\n";
    		}

    		$html .= "</script>\n";
    		return $html;
    	}
	}

	/**
	 * Walk through tree and create html to display nodes
	 * Overide in ancestor to change display characteristics
	 *
	 * @param ZF4_Object Tree_Free $tree
	 * @param string $name display tree name
	 * @return string
	 */
	protected function _render($tree, $name) {
		$html = "myTree{$name}.add('{$tree->key}','" . (is_null($tree->parent) ? -1 : $tree->parent) . "','{$tree->key}');\n";
		foreach ($tree->children as $child) {
			if ($child != null) {
				$html .= $this->_render($child,$name);
			}
		}
		return $html;
	}

	/**
	 * return the tree's index
	 *
	 * @return array
	 */
	public function getIndex() {
	    return $this->_index;
	}

	/**
	 * Convert tree to an array
	 *
	 * @return array Usually array of arrays
	 */
	public function toArray() {
		if ($this->_tree == null) {
    		return array();
		} else {
			return $this->_tree->toArray();
		}
	}
	
	/**
	 * Swap the tree that this object is managing for another one
	 *
	 * @param ZF4_Object_Tree_Free $newTree
	 */
	public function swapTree(ZF4_Object_Tree_Free $newTree) {
		$this->_tree = $newTree->getRoot();
		$this->_index = $newTree->getIndex();
	}
}