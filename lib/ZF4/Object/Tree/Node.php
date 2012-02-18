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
 * ZF4 Tree Item Node
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Tree
 */

class ZF4_Object_Tree_Node {

    /**
     * Node key
     *
     * @var mixed
     */
    public $key = null;
    /**
     * Additional user supplied data for node
     *
     * @var unknown_type
     */
    public $data = null;
    /**
     * Child node keys
     *
     * @var array
     */
    public $children  = null;
    /**
     * Key of parent node
     *
     * @var mixed
     */
    public $parent = null;

    public function __construct() {
        $this->children = new ArrayObject();
    }
    
    /**
     * Return node and its descendents as an hierarchical array
     *
     * @return array
     */
    public function toArray() {
    	return $this->_createArray($this);
    }
    
    private function _createArray($node) {
    	$ret = array(
    		'key' => $node->key,
    		'data' => $node->data,
    		'parent' => $node->parent,
    		'children' => array()
    	);

    	if (count($node->children) > 0) {
    		foreach ($node->children as $child) {
    			$ret['children'][] = $this->_createArray($child);
    		}
    	}

    	return $ret;
    }
    
    /**
     * Does this node have a specific child?
     *
     * @param int $id
     * @return boolean
     */
    public function hasChild($id) {
    	return array_key_exists($id,$this->children);
    }
}