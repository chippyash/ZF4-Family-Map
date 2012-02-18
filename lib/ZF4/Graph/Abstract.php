<?php
/**
 * ZF4 Library
 *
 * @category 	ZF4_Lib
 * @package  	Graph
 * @subpackage 	Abstract
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
 * Abstract Graph
 *
 * @category 	ZF4_Lib
 * @package  	Graph
 * @subpackage 	Abstract
 */
abstract class ZF4_Graph_Abstract {
	
	/**
	 * Graph id
	 *
	 * @var string|int
	 */
	public $id;
	
	/**
	 * Graph nodes
	 *
	 * @var array
	 */
	protected $_nodes;
	/**
	 * Graphe edges
	 *
	 * @var array
	 */
	protected $_edges;
	/**
	 * Index of edges for a node
	 *
	 * @var array
	 */
	private $_nodeEdges = array();
	
	/**
	 * The renderer to use
	 *
	 * @var ZF4_Graph_Renderer_Abstract
	 */
	protected $_renderer;
	
	/**
	 * Constructor
	 * 
	 * Always calls _init() method
	 *
	 * @final 
	 * @param int|string $id
	 * @param ZF4_Graph_Render_Interface $renderer Renderer to use
	 */
	final public function __construct($id, $renderer = null) {
		$this->id = $id;
		if (null != $renderer) $this->setRenderer($renderer);
		$this->_init();
	}
	
	/**
	 * Do any local initialisation
	 * Called by constructor
	 * 
	 * Typically used to load up the graph or set a default renderer etc
	 *
	 */
	protected function _init(){}
	
/**
 * CRUD - NODES
 */
	/**
	 * Add a node or nodes to the graph
	 *
	 * @param ZF4_Graph_Node|array $nodes a single node or an array of nodes
	 * @return ZF4_Graph_Abstract Fluent Interface
	 */
	public function addNode($nodes) {
		if (!is_array($nodes)) $nodes = array($nodes);
		foreach ($nodes as $node) {
			$this->_nodes[$node->id] = $node;
		}
		return $this;
	}
	
	/**
	 * Delete node or nodes from graph
	 *
	 * @param ZF4_Graph_Node|array $nodes a single node or an array of nodes
	 * @return boolean  True on success else false if one or more deletes failed
	 */
	public function delNode($nodes) {
		if (!is_array($nodes)) $nodes = array($nodes);
		$ret = true;
		foreach ($nodes as $node) {
			if (isset($this->_nodes[$node->id])) {
				unset($this->_nodes[$node->id]);
			} else {
				$ret = false;
			}
		}
		return $ret;
	}
	
	/**
	 * Return count of nodes
	 *
	 * @return int
	 */
	public function nodeCount() {
		return count($this->_nodes);
	}
	
	/**
	 * return a node
	 *
	 * @param int|string $id
	 * @return ZF4_Graph_Node
	 * @throws ZF4_Graph_Exception if node id is invalid
	 */
	public function getNode($id) {
		if (isset($this->_nodes[$id])) {
			return $this->_nodes[$id];
		} else {
			throw new ZF4_Graph_Exception('Invalid node id');
		}
	}
	
/**
 * CRUD - EDGES
 */
	
	/**
	 * Add an edge or edges to the graph
	 *
	 * @param ZF4_Graph_Edge|array $edges a single edge or an array of edges
	 * @return ZF4_Graph_Abstract Fluent Interface
	 */
	public function addEdge($edges) {
		if (!is_array($edges)) $edges = array($edges);
		foreach ($edges as $edge) {
			$this->_edges[$edge->id] = $edge;
			//index the edges for the nodes
			$edgeNodes = $edge->getNodes();
			if (!isset($this->_nodeEdges[$edgeNodes['head']->id])) {
				$this->_nodeEdges[$edgeNodes['head']->id] = array();
			}
			if (!isset($this->_nodeEdges[$edgeNodes['tail']->id])) {
				$this->_nodeEdges[$edgeNodes['tail']->id] = array();
			}
			$this->_nodeEdges[$edgeNodes['head']->id][$edge->id] = $edge->id;
			$this->_nodeEdges[$edgeNodes['tail']->id][$edge->id] = $edge->id;
		}
		return $this;
	}
	
	/**
	 * Delete edge or edges from graph
	 *
	 * @param ZF4_Graph_Edge|array $edges a single edge or an array of edges
	 * @return boolean  True on success else false if one or more deletes failed
	 */
	public function delEdge($edges) {
		if (!is_array($edges)) $edges = array($edges);
		$ret = true;
		foreach ($edges as $edge) {
			if (isset($this->_edges[$edge->id])) {
				unset($this->_edges[$edge->id]);
			} else {
				$ret = false;
			}
		}
		return $ret;
	}
	
	/**
	 * Return count of edges
	 *
	 * @return int
	 */
	public function edgeCount() {
		return count($this->_edges);
	}
	
	/**
	 * return an edge
	 *
	 * @param int|string $id
	 * @return ZF4_Graph_Edge
	 * @throws ZF4_Graph_Exception if edge id is invalid
	 */
	public function getEdge($id) {
		if (isset($this->_edges[$id])) {
			return $this->_edges[$id];
		} else {
			throw new ZF4_Graph_Exception('Invalid edge id');
		}
	}
	
	/**
	 * Return the set of edges for a given node
	 *
	 * @param ZF4_Graph_Node $node
	 * @return array|null Array of [ZF4_Graph_Edge...] or null
	 */
	public function getEdgesForNode(ZF4_Graph_Node $node) {
		if (isset($this->_nodeEdges[$node->id])) {
			$ret = array();
			foreach ($this->_nodeEdges[$node->id] as $edgeId) {
				$ret[] = $this->_edges[$edgeId];
			}
			return $ret;
		} else {
			return null;
		}
	}
	
/**
 * RENDERING
 */
	/**
	 * Render the graph using the currently set renderer
	 *
	 * @param ZF4_Graph_Node $node  Start node for rendering
	 * @param int $depth graph depth to render - depends on renderer
	 * @param array $params Any parameters to send to the renderer
	 * @return mixed - depends on the renderer
	 * @throws ZF4_Graph_Exception if no renderer is available
	 */
	public function render(ZF4_Graph_Node $node, $depth, array $params = null) {
		if (null != $this->_renderer) {
			return $this->_renderer->render($this,$node,$depth,$params);
		} else {
			throw new ZF4_Graph_Exception('No renderer set for rendering');
		}
	}
	
	/**
	 * Set the renderer for the graph
	 *
	 * @param ZF4_Graph_Renderer_Interface $renderer
	 * @return ZF4_Graph_Abstract Fluent Interface
	 */
	public function setRenderer($renderer) {
		if (!$renderer instanceof ZF4_Graph_Renderer_Interface) {
			throw new ZF4_Graph_Exception('Renderer does not conform to ZF4_Graph_Renderer_Interface');
		}
		$this->_renderer = $renderer;
		return $this;
	}
	
	/**
	 * Return the currently set renderer
	 *
	 * @return null|ZF4_Graph_Renderer_Abstract
	 */
	public function getRenderer() {
		return $this->_renderer;
	}
}