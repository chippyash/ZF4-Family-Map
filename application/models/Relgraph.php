<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Relationship
 * @author 	Ashley Kitson
 * @copyright   ZF4 Business Limited and Woodnewton - a learning community, 2011, UK
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
 * Relationship Graph model
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Relationship
 */
class Application_Model_Relgraph {
	
	/**
	 * The relationship graph
	 *
	 * @var ZF4_Graphp_Graph_Network
	 */
	private $_graph;
	
	/**
	 * Construct a relationship tree for a person
	 * 
	 * We used a cached network graph of the entire organisation
	 * to speed up delivery of the graph
	 *
	 * @param int $pId person id
	 * @param int $depth relationship depth
	 */
	public function __construct() {
		//set up caching
		$user = ZF4_User::getSessionIdentity();
		$feOpts = array(
			'cached_entity'=>$this,
			'cached_methods'=>array('getGraph'),
			'lifetime'=>null,
			'automatic_serialization'=>true);
		$beOpts = array(
			'cache_dir'=>ZF4_Defines::dirCache('relation'),
			'file_name_prefix' => 'rels_' . $user['orgId']);
		$cache = Zend_Cache::factory('Class','File',$feOpts,$beOpts);
		$this->_graph = $cache->getGraph();
	}
	
	/**
	 * Build the organisations relationship graph
	 *
	 * @return ZF4_Graph_Graph_Network
	 */
	public function getGraph() {
		$graph = new ZF4_Graph_Graph_Network('relations',new Application_Model_Relationship_Renderer());
		$user = ZF4_User::getSessionIdentity();
		//build the nodes - we do this using sql cus it will be faster
		//nodes are people
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$select = $db->select()
				->from('person')
				->where('orgid=?',intval($user['orgId']));
		$rows = $db->fetchAll($select);
		$nodes = array();
		foreach ($rows as $row) {
			$nodes[intval($row['id'])] = new ZF4_Graph_Node(intval($row['id']),$row);
		}
		$graph->addNode($nodes);
		//get the relationship type details
		$select = $db->select()
				->from('relType')
				->where('orgid=?',intval($user['orgId']));
		$rows = $db->fetchAll($select);
		$relTypes = array();
		foreach ($rows as $row) {
			$relTypes[intval($row['id'])] = $row;
		}
		//build the edges - again use sql for speed
		//relationships are the edges
		$select = $db->select()
				->from('relation')
				->join('person','relation.prsnIdA = person.id',array())
				->where('orgid=?',intval($user['orgId']));
		$rows = $db->fetchAll($select);
		$edges = array();
		foreach ($rows as $row) {
			$direction = $relTypes[intval($row['relTypeId'])]['direction'];
			$edges[intval($row['id'])] 
				= new ZF4_Graph_Edge(
					($direction == 'one-way' ? true : false),
					$nodes[intval($row['prsnIdA'])],
					$nodes[intval($row['prsnIdB'])],
					intval($row['id']),
					$relTypes[intval($row['relTypeId'])]
					);
		}
		//construct the graph
		$graph->addNode($nodes)->addEdge($edges);
		return $graph;
	}
	
	/**
	 * Render graph to xml for the person to depth
	 *
	 * @param numeric $psrnId
	 * @param numeric $depth
	 * @return string
	 */
	public function render($psrnId,$depth) {
		$node = $this->_graph->getNode(intval($psrnId));
		return $this->_graph
			->setRenderer(new Application_Model_Relationship_Renderer())
			->render($node,intval($depth));
	}
}