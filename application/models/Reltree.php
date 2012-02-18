<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Reltree
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
 * Relationship Tree model
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Reltree
 */
class Application_Model_Reltree extends ZF4_Object_Tree_Free  {
	
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
	 * Construct a relationship tree for a person
	 *
	 * @param int $pId person id
	 * @param int $depth relationship depth
	 */
	public function __construct($pId = 0, $depth = 3) {
		$identity = new Application_Model_Person($pId);
		$this->_createPerson($identity,$depth);
		$this->_build($identity,$depth);
	}
	
	/**
	 * Build a relationship tree
	 * RECURSIVE
	 * 
	 * Width first scanning
	 *
	 * @param Application_Model_Person $person
	 * @param int $depth
	 */
	protected function _build(Application_Model_Person $person, $depth) {
		$depth --;
		if ($depth==0) return;
		$relationships = $person->getRelationships();
		$pClone = clone $person;
		$collect = array();
		//relate each relation
		foreach ($relationships as $relationship) {
			if ($relationship['direction'] == 'forward') {
				$related = new Application_Model_Person(intval($relationship['prsnIdB']));
				if (!$this->_checkRelation($pClone,$related)) {
					$this->_createPerson(
						$related,
						$depth,
						$relationship,
						$pClone
					);
				}
				$collect[] = clone $related;
			} else {
				//backward relationship
				$related = new Application_Model_Person(intval($relationship['prsnIdA']));
				if (!$this->_checkRelation($related,$pClone)) {
					$this->_createPerson(
						$related,
						$depth,
						$relationship,
						$pClone
					);
				}
				$collect[] = clone $related;
			}
		}
		//recurse for each relative
		foreach ($collect as $relative) {
			$this->_build($relative,$depth);
		}
		
	}
	
	/**
	 * Check to see if a relationship already exists
	 *
	 * @param Application_Model_Person $head
	 * @param Application_Model_Person $tail
	 * @return boolean
	 */
	protected function _checkRelation(Application_Model_Person $head,Application_Model_Person $tail) {
		$headNode = $this->search($head->id);
		if ($headNode == false || !isset($headNode->children[$tail->id])) {
			return false;
		} else {
			return true;
		}
		
	}
	
	/**
	 * Create a person in the tree
	 *
	 * @param Application_Model_Person $person
	 * @param int $depth 
	 * @param array $relationship
	 * @param Application_Model_Person $parent
	 */
	protected function _createPerson(Application_Model_Person $person, 
									$depth, 
									array $relationship = null,
									Application_Model_Person $parent = null
									) {
		$data = $person->getRecordData();
		$data['rel'] = $relationship;
		$parentKey = (null == $parent ? null : $parent->id);
		$this->insert($person->id,$data,$parentKey);
	}
	

	/**
	 * Render tree as nodes for Roamer
	 *
	 * @return string xml
	 */
	public function renderNodes() {
		$index = $this->getIndex();
		$xml = '';
		$pattern = '<node id="%d" label="%s" tooltip="%s" label_position="bottom" graphic_type="image" graphic_image_url="/constellation_roamer/images/%s" />';
		foreach ($index as $key=>$node) {
			$ind = $node->data;
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
			$xml .= sprintf($pattern,$key,$ind['fName'],$tooltip,$icon);
		}
		return $xml;
	}
	
	protected $_edgePattern = '<edge id="%s" head_node_id="%d" tail_node_id="%d" edge_line_color="#%s" edge_line_thickness="%d" tooltip="%s" bidirectional="%s" arrowhead="true"/>';
	
	/**
	 * render tree as edges for Roamer
	 *
	 * @return string xml
	 */
	public function renderEdges() {
		$edgeArr = array();
		$this->_findEdges($this->getRoot(),$edgeArr);
		return $this->_renderEdges($edgeArr);
	}
	
	protected function _findEdges($node, &$edgeArr) {
		if ($node->children == null) return;
		foreach ($node->children as $child) {
			if ($node->key > $child->key) {
				$edgeId = "{$child->key}|{$node->key}";
			} else {
				$edgeId = "{$node->key}|{$child->key}";
			}
			if (!array_key_exists($edgeId,$edgeArr)) {
				$edgeArr[$edgeId] = array('head'=>$node->data,'tail'=>$child->data);
			}
			$this->_findEdges($child,$edgeArr);
		}
	}
	
	protected function _renderEdges($edges) {
		$xml = '';
		foreach ($edges as $edgeId=>$edge) {
			$head = $edge['head'];
			$tail = $edge['tail'];
			$bidirectional = ($tail['rel']['relDir'] == 'two-way' ? 'true' : 'false');
			$xml .= sprintf($this->_edgePattern,$edgeId,$head['id'],$tail['id'],$tail['rel']['relColour'],$tail['rel']['relValue'],$tail['rel']['name'],$bidirectional);
		}
		return $xml;
	}
}