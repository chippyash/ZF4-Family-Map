<?php
/**
 * ZF4 Library
 *
 * @category 	ZF4_Lib
 * @package  	Graph
 * @subpackage 	Linear
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
 * A Linear graph.
 * 
 * A Linear graph is defined as a set of points that progress from
 * first point to last point.  Each point can have a title
 *
 * The default renderer is ZF4_Graph_Renderer_Linear
 * 
 * @category 	ZF4_Lib
 * @package  	Graph
 * @subpackage 	Linear
 */
class ZF4_Graph_Graph_Linear extends ZF4_Graph_Abstract {
	
	/**
	 * Initialise the default renderer
	 *
	 */
	protected function _init() {
		$this->setRenderer(new ZF4_Graph_Renderer_Linear());	
	}
	
	/**
	 * Add data points to the graph
	 * 
	 * If titles are given then the size of the points and title arrays must be the same
	 * Nodes are created with data = array[title,value]
	 * 
	 * NB - we do not add edges as they are implicit
	 *
	 * @param array $points array of numeric data points
	 * @param array $titles array of titles.
	 * @return ZF4_Graph_Graph_Linear
	 * @throws ZF4_Graph_Exception if titles given and count does not match points
	 */
	function addPoints(array $points, array $titles = null) {
		if (is_array($titles)) {
			if (count($titles) != count($points)) {
				throw new ZF4_Graph_Exception('Number of titles does not match points');
			}
		} else {
			$titles = array_fill(0,count($point),'');
		}
		/**
		 * 
		 */
		foreach ($points as $key=>$point) {
			$this->addNode(new ZF4_Graph_Node($key,array('title'=>$titles[$key], 'value'=>$point)));
		}
		return $this;
	}
	
	/**
	 * Returns points array
	 *
	 * @return array Array of points = [[title,value],..]
	 */
	function getPoints() {
		$ret = array();
		foreach ($this->_nodes as $node) {
			$ret[] = $node->getData();
		}
		return $ret;
	}
	
/**
 * Overide edge functionality
 */
	/**
	 * Add an edge or edges to the graph
	 * OVERIDE Ancestor - Does nothing
	 *
	 * @param ZF4_Graph_Edge|array $edges a single edge or an array of edges
	 * @return ZF4_Graph_Abstract Fluent Interface
	 */
	public function addEdge($edges) { return $this; }
	
}