<?php
/**
 * ZF4 Library
 *
 * @category 	ZF4_Lib
 * @package  	Graph
 * @subpackage 	Node
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
 * A node in the graph - A node is an item in the graph
 * A node can have associated data, e.g. a position
 * 
 * @category 	ZF4_Lib
 * @package  	Graph
 * @subpackage 	Node
 */
class ZF4_Graph_Node {
	
	/**
	 * Node id
	 *
	 * @var int|string
	 */
	public $id;
	
	/**
	 * Node data
	 *
	 * @var mixed
	 */
	protected $_data;
	
	/**
	 * Constructor
	 *
	 * @param string|int $id Node id - if null will be set internally
	 * @param mixed $data any data to store with the node
	 */
	public function __construct($id = null, $data = null) {
		$this->_data = $data;
		if (is_null($id)) {
			$this->id = ZF4_Graph::getNextNodeId();
		} else {
			$this->id = $id;
		}
	}
	
	/**
	 * Set data for a node
	 *
	 * @param mixed $data
	 * @return ZF4_Graph_Node Fluent Interface
	 */
	public function setData($data) {
		$this->_data = $data;
		return $this;
	}
	
	public function getData() {
		return $this->_data;
	}
	
}