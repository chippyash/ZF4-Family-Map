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
 * Defines the interface for a ZF4_Object_Tree class item
 *
 * @category 	ZF4
 * @package  	Object
 * @subpackage  Tree
 */
interface ZF4_Object_Tree_Interface {

    /**
     * Insert a tree node into the tree
     *
     * Where and how it is inserted is dependent on the tree class itself
     *
     * @param mixed $key the node key
     * @param mixed $data optional additional node data
     * @param mixed $parentkey the key of the parent node to attach this node to
     * @return Fluent_Interface
     */
    public function insert($key, $data = null, $parentKey = null);

    /**
     * Retrieve a subtree given its root node 'key'
     *
     * Retrieval algorithm is dependent on tree class
     *
     * @param mixed $key
     * @return ZF4_Object_Tree_..|boolean a tree or false if not found
     */
    public function search($key);
    /**
     * Delete only the node specified by the 'key'.
     * This will re-attach any child trees to the tree
     *
     * @param unknown_type $key
     */
    public function delete($key);

    /**
     * Return the root of the tree (i.e. the whole tree)
     *
     * @return ZF4_Object_Tree_Node The root node or null if not set
     */
    public function getRoot();
    
    /**
     * Return all end nodes (leaves) of the tree
     *
     * @return array of ZF4_Object_Tree_Node
     */
    public function getLeaves();
    
    /**
     * Convert a node to a tree
     *
     * @param ZF4_Object_Tree_Node $node
     * @return ZF4_Object_Tree_..
     */
    public function convertToTree(ZF4_Object_Tree_Node $node);
    
    /**
     * Render the tree in html
     * @todo Change the call to this when I find out how to get the current controller automagically
     *
     * @param $controller Controller calling the render function
     * @param string $name name of display item
     * @param mixed $config Configuration parameters for the rendering
     * @return string Html to display tree
     */
    public function render(Zend_Controller_Action &$controller, $name='default', $config=null);
    
}