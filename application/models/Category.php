<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Category
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
 * Person Category model
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Category
 */
class Application_Model_Category extends ZF4_Db_Table_Model {

	/**
	 * Constructor
	 *
	 *
	 * @param int|string $id	Campaign id
	 * @throws Application_Model_Exception_InvalidCampaign if invalid campaign
	 */
	public function __construct($id = null) {
		$id = ($id == 0 ? null : $id);  //trap zero index
		try {
			parent::__construct('cat',null, $id);
		} catch (ZF4_Db_Table_Exception $e) {
			throw new Application_Model_Exception_InvalidCategory();
		}
	}
	
	/**
	 * Return the people in this category
	 *
	 * @return array
	 */
	public function getPeople() {
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$select = $db->select()
					 ->from(array('pc'=>'person_cat'),array())
					 ->where('catId=?',$this->id)
					 ->join(array('p'=>'person'),'pc.prsnId=p.id',array('id'=>'p.id','uid'=>'p.uid'));
		$rows = $db->fetchAll($select);
		return $rows;
	}
	
    /**
     * Inserts a new row.
     * 
     * Strips out relationship ids and adds or amends them to cat_relType
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */
    public function insert(array $data) {
        //manage relationship ids
        if (isset($data['relId'])) {
                $relationships = (is_array($data['relId']) ? $data['relId'] : array($data['relId']));
                unset($data['relId']);
        } else {
                $relationships = array();
        }
    	$rKey = parent::insert($data);
    	if ($rKey != 0) {
    		$this->_updateRelationships($relationships, $rKey);
    	}
    	return $rKey;
    }
    
    /**
     * Updates existing rows.
     * 
     * Extends ancestor to deal with relationship types
     *
     * @param  array        $data  Column-value pairs.
     * @param  array|string $where An SQL WHERE clause, or an array of SQL WHERE clauses.
     * @return int          The number of rows updated.
     */
    public function update(array $data, $where) {
		//manage relationship ids
		if (isset($data['relId'])) {
			$relationships = (is_array($data['relId']) ? $data['relId'] : array($data['relId']));
			unset($data['relId']);
		} else {
			$relationships = array();
		}
    	$ret = parent::update($data, $where);
   		$this->_updateRelationships($relationships, str_replace('id=','',$where));
    	return $ret;
    }
    
    /**
     * Update allowable relationship types for a category
     *
     * @param array $relationships relationship type ids
     * @param string $catId Category id
     */
    protected function _updateRelationships(array $relationships, $catId) {
    	$catId = intval($catId);
    	$model = new Zend_Db_Table('cat_relType');
    	$model->delete('catId=' . $catId);
    	if (empty($relationships)) return;
    	foreach ($relationships as $value) {
    		if (in_array($value,array('','&nbsp;',null))) continue;
    		$model->insert(array(
    			'catId' => $catId,
    			'relTypeId' => intval($value)
    		));
    	}
    }
    
	/**
	 * Get id->someNameColumn for use in form selectors
	 *
	 * If the object has a rowSts field then only active rows are returned
	 * 
	 * Extends ancestor to ensure only current organisation
	 *
	 * @param string $nameCol name column to use - default is the unique column for the table
	 * @param array $where additional where clauses
	 * @return array
	 */
    public function getForSelect($name = null,array $where = array()) {
    	$user = ZF4_User::getSessionIdentity();
    	array_push($where,'orgId=' . $user['orgId']);
    	return parent::getForSelect($name,$where);
    }    
    
    /**
     * Returns a list of categories that customers can belong to
     * 
     * Supports the map->indexAction()
     *
     * @return html
     */
    public static function getQuerySelect() {
    	$model = new Application_Model_Category();
    	$cats = $model->getForSelect('name');
        $cats[0] = 'ALL';
        ksort($cats);
    	//construct the selectors
    	$xhtml = "<table><tbody>";
    	$xhtml .= "<tr><td><label for='catCat'>Choose categories</label></td></tr>";
    	$xhtml .= "<tr><td>";
    	$check = true;
    	foreach ($cats as $id=>$cat) {
    		$xhtml .= "<input type='checkbox' name='catCat' value='{$id}' ";
    		if ($check) {
    			$xhtml .= "checked='checked' class='chkAll' id='chkAllCat' onClick='setCheckAll(this)' ";
    			$check = false;
    		} else {
    			$xhtml .= "class ='chkSingle' rel='chkAllCat' onClick='setCheckSingle(this)' ";
    		}
    		$xhtml .= "/><span class='mapSelCheck'>{$cat}</span>";
    	}    	
    	$xhtml .= "</td></tr>";
		$xhtml .= "</tbody></table>";
		
		return $xhtml;
    }    
    
        /**
     * Add a customer for this category
     *
     * @param int $prsnId
     */
    public function addCustomer($prsnId) {
    	$model = new Zend_Db_Table(array('name'=>'person_cat'));
    	$ret = $model->insert(array('prsnId'=>intval($prsnId), 'catId'=>intval($this->id)));
    	return $ret;
    }
}
