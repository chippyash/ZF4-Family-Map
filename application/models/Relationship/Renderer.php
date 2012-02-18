<?php
/**
 * Family Map Models
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
 * Relationship Graph model renderer for Constellation Renderer
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Relationship
 */
class Application_Model_Relationship_Renderer extends ZF4_Graph_Renderer_Network  {
	
	/**
	 * Icons to be used for each person type
	 * These are relative to httpdocs/images/icons
	 *
	 * @var array
	 */
	protected $_icons = array(
		'pupil-male' => 'user.png',
		'pupil-female' => 'user_female.png',
		'pupil-undefined' => 'user-gray.png',
		'member-male' => 'user.png',
		'member-female' => 'user_female.png',
		'member-undefined' => 'user-gray.png',
		'staff-male' => 'user_green.png',
		'staff-female' => 'user_green.png',
		'staff-undefined' => 'user_green.png',
		'doctor-male' => 'user_suit.png',
		'doctor-female' => 'user_suit.png',
		'doctor-undefined' => 'user_suit.png',
		'health visitor-male' => 'user_orange.png',
		'health visitor-female' => 'user_orange.png',
		'health visitor-undefined' => 'user_orange.png',
		'carer-male' => 'user_red.png',
		'carer-female' => 'user_red.png',
		'carer-undefined' => 'user_red.png'
	);

	/**
	 * XML pattern for nodes
	 *
	 * @var string
	 */
	protected $_nodePattern = '<node id="%d" label="%s" tooltip="%s" label_position="bottom" graphic_type="image" graphic_image_url="/constellation_roamer/images/%s" />';
	/**
	 * XML pattern for edges
	 *
	 * @var string
	 */
	protected $_edgePattern = '<edge id="%s" head_node_id="%d" tail_node_id="%d" edge_line_color="#%s" edge_line_thickness="%d" tooltip="%s" bidirectional="%s" arrowhead="true"/>';

	/**
	 * Renders XML output for Constellation Roamer
	 *
	 * @param ZF4_Graph_Abstract $graph
	 * @param ZF4_Graph_Node Node of person to render from
	 * @param int $depth
	 * @return string xml
	 */
	public function render(
		ZF4_Graph_Abstract $graph, 
		ZF4_Graph_Node $node, 
		$depth, 
		array $params = null) {
			$data = parent::render($graph,$node,$depth);
			$nodes = $this->_renderNodes($data['nodes']);
			$edges = $this->_renderEdges($data['edges']);
			return $nodes . $edges;
	}
	
	/**
	 * Render the nodes to XML
	 *
	 * @param array $nodes
	 * @return string
	 */
	protected function _renderNodes(array $nodes) {
		$xml = '';
		foreach ($nodes as $node) {
			$ind = $node->getData();
			$name = "{$ind['style']} {$ind['fName']} {$ind['lName']}";
			$ind['pType'] = explode(',',$ind['pType']);
			if (count($ind['pType']) > 1) {
				if (in_array('pupil',$ind['pType'])) {
					$ind['pType'] = 'pupil';
				} elseif (in_array('member',$ind['pType'])) {
					$ind['pType'] = 'member';
				} else {
					$ind['pType'] = $ind['pType'][0];
				}
			} else {
				$ind['pType'] = $ind['pType'][0];
			}
			$icon = $this->_icons["{$ind['pType']}-{$ind['gender']}"];
			$tooltip = "UID: {$ind['uid']}\n"
					 . "Name: {$name}\n"
					 . "Email: {$ind['email']}\n"
					 . "Mobile: {$ind['mTel']}\n"
					 . "Tel: {$ind['oTel']}";
			$xml .= sprintf($this->_nodePattern,$node->id,$ind['fName'],$tooltip,$icon);
		}
		return '<nodes>' . $xml . '</nodes>';
	}
	
	/**
	 * Render the edges to XML
	 *
	 * @param array $edges
	 * @return string
	 */
	protected function _renderEdges($edges) {
		$xml = '';
		foreach ($edges as $edge) {
			$rel = $edge->getData();
			$nodes = $edge->getNodes();
			$bidirectional = ($rel['direction'] == 'two-way' ? 'true' : 'false');
			$xml .= sprintf($this->_edgePattern,$edge->id,$nodes['tail']->id,$nodes['head']->id,$rel['relColour'],$rel['relValue'],$rel['name'],$bidirectional);
		}
		return '<edges>' . $xml . '</edges>';
	}
	
}