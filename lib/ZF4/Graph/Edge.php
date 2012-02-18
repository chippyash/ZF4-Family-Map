<?php

/**
 * ZF4 Library
 *
 * @category 	ZF4_Lib
 * @package  	Graph
 * @subpackage 	Edge
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
 * A graph edge.  An edge connects two nodes and can have a direction
 * associated with it in addition to other arbitrary data
 * 
 * @category 	ZF4_Lib
 * @package  	Graph
 * @subpackage 	Edge
 */
class ZF4_Graph_Edge {

    /**
     * Edge id
     *
     * @var int|string
     */
    public $id;

    /**
     * Edge data
     *
     * @var mixed
     */
    protected $_data;

    /**
     * Is edge directed?
     *
     * @var boolean
     */
    protected $_directed = false;

    /**
     * head node of edge
     *
     * @var ZF4_Graph_Node
     */
    protected $_head;

    /**
     * tail node of edge
     *
     * @var ZF4_Graph_Node
     */
    protected $_tail;

    /**
     * Constructor
     *
     * @param boolean $directed True for directed, False for non directed
     * @param ZF4_Graph_Node $headNode The head node
     * @param ZF4_Graph_Node $tailNode The tail node
     * @param string|int $id Id for Edge.  If null, will be generated internally
     * @param mixed $data Any data to be stored with the edge
     */
    public function __construct($directed, ZF4_Graph_Node $headNode, ZF4_Graph_Node $tailNode, $id = null, $data = null) {
        $this->_data = $data;
        $this->_head = $headNode;
        $this->_tail = $tailNode;
        $this->_directed = $directed;
        if (null == $id) {
            $h = min(array($headNode->id, $tailNode->id));
            $t = max(array($headNode->id, $tailNode->id));
            $this->id = "{$h}|{$t}";
        } else {
            $this->id = $id;
        }
    }

    /**
     * Set data for a edge
     *
     * @param mixed $data
     * @return ZF4_Graph_Edge Fluent Interface
     */
    public function setData($data) {
        $this->_data = $data;
        return $this;
    }

    /**
     * Return data attached to edge
     *
     * @return mixed
     */
    public function getData() {
        return $this->_data;
    }

    /**
     * Return edge directed status
     *
     * @return boolean True = directed, False = undirected
     */
    public function isDirected() {
        return $this->_directed;
    }

    /**
     * Return array of nodes for this edge
     *
     * @return array ['head'=>headNode, 'tail'=>tailNode]
     */
    public function getNodes() {
        return array('head' => $this->_head, 'tail' => $this->_tail);
    }

}