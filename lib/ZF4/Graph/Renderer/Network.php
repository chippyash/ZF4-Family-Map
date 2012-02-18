<?php
/**
 * ZF4 Library
 *
 * @category 	ZF4_Lib
 * @package  	Graph
 * @subpackage 	Renderer
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
 * A network graph renderer
 * 
 * Simply returns an array of edges and nodes
 *
 * @category 	ZF4_Lib
 * @package  	Graph
 * @subpackage 	Renderer
 */
class ZF4_Graph_Renderer_Network implements ZF4_Graph_Renderer_Interface {
	
	/**
	 * The graph we are manipulating
	 *
	 * @var ZF4_Graph_Abstract
	 */
	protected $_graph;
	
	/**
	 * Nodes we have rendered
	 *
	 * @var array
	 */
	protected $_nodes = array();
	
	/**
	 * Edges we have rendered
	 *
	 * @var array
	 */
	protected $_edges = array();
	
	/**
	 * Render a graph
	 * 
	 * Returns The nodes and edges as an array [nodes,edges]
	 *
	 * @param ZF4_Graph_Abstract $graph  Graph to render
	 * @param ZF4_Graph_Node $node Start node
	 * @param int $depth Render to depth - degree of separation - 1 is just the node, 2 is node + neigbours etc
	 * @param array $params Addition parameters to send to renderer
	 * @return array An array of [nodes,edges]
	 * @throws ZF4_Graph_Exception If graph type is not instance of Network Graph
	 */
	public function render(
		ZF4_Graph_Abstract $graph, 
		ZF4_Graph_Node $node, 
		$depth, 
		array $params = null) {
			if ($graph instanceof ZF4_Graph_Graph_Network ) {
				$this->_graph = $graph;
				$this->_nodes[$node->id] = $node; //add the first node - ensure we don't get duplicates
				$this->_render($node,$depth); //find all other nodes and edges
				$ret = array('nodes'=>$this->_nodes,'edges'=>$this->_edges); //the return value
				//blank nodes and edges in case we get called again
				$this->_nodes = array();
				$this->_edges = array();
				return $ret;
			} else {
				throw new ZF4_Graph_Exception('Invalid graph type for Network renderer');
			}
		}
		
		protected function _render(ZF4_Graph_Node $node,$depth) {
			$depth --;
			if ($depth == 0) return;  //end operation
			$nodeEdges = $this->_graph->getEdgesForNode($node);
			if ($nodeEdges == null) return;
			foreach ($nodeEdges as $edge) {
				$edgeNodes = $edge->getNodes();
				//add new nodes - ensuring we don't get duplicates
				$this->_nodes[$edgeNodes['head']->id] = $edgeNodes['head'];
				$this->_nodes[$edgeNodes['tail']->id] = $edgeNodes['tail'];
				//add edge - ensuring we don't get duplicates
				$this->_edges[$edge->id] = $edge;
				//recurse down the nodes
				$this->_render($edgeNodes['head'],$depth);
				$this->_render($edgeNodes['tail'],$depth);
			}
		}
}