<?php

/**
 * ZF4 Library
 *
 * @category 	ZF4
 * @package  	Graph
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
 * Graphing Factory class
 * 
 * Provides static factory methods to create graphs, nodes and edges
 *
 * @category 	ZF4
 * @package  	Graph
 */
class ZF4_Graph {
    /**
     * Namespace for graph types
     *
     * @var string
     */

    const NS = 'ZF4_Graph_Graph_';

    /**
     * Incrementor for node ids
     *
     * @var int
     */
    private static $_nodeId = -1;

    /**
     * Return a graph object
     *
     * @param string $type A graph type
     * @param string|int $id Id for graph
     * @param ZF4_Graph_Render_Interface $renderer Renderer to use
     * @return ZF4_Graph_Abstract
     * @throws ZF4_Graph_Exception if invalid graph type
     */
    public static function graph($type, $id, ZF4_Graph_Renderer_Interface $renderer = null) {
        $class = self::NS . ucfirst($type);
        if (class_exists($class)) {
            return new $class($id, $renderer);
        } else {
            throw new ZF4_Graph_Exception('Invalid graph type');
        }
    }

    /**
     * Return a node
     *
     * @param uint|string $id Node Id - if null will generate one
     * @param mixed $data Any data to be stored with the node
     * @return ZF4_Graph_Node
     */
    public static function node($id = null, $data = null) {
        return new ZF4_Graph_Node($id, $data);
    }

    /**
     * Return an edge
     *
     * @param boolean $directed True for directed, False for non directed
     * @param ZF4_Graph_Node $headNode The head node
     * @param ZF4_Graph_Node $tailNode The tail node
     * @param string|int $id Id for Edge.  If null, will be generated internally
     * @param mixed $data Any data to be stored with the edge
     * @return ZF4_Graph_Edge
     */
    public static function edge($directed, ZF4_Graph_Node $headNode, ZF4_Graph_Node $tailNode, $id = null, $data = null) {

        return new ZF4_Graph_Edge($directed, $headNode, $tailNode, $id, $data);
    }

    /**
     * Get the next internally generated node id
     * 
     * Called by ZF4_Graph_Node constructor
     *
     * @return int
     * @access private
     * @see ZF4_Graph_Node
     */
    public static function getNextNodeId() {
        return self::$_nodeId++;
    }

}