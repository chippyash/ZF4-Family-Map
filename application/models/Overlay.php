<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Overlay
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
 * Overlay model
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Overlay
 */
class Application_Model_Overlay extends ZF4_Db_Table_Model {

	/**
	 * Constructor
	 *
	 *
	 * @param int $id	overlay
	 * @throws Application_Model_Exception_InvalidRecord if invalid record
	 */
	public function __construct($id = null) {
		$id = ($id == 0 ? null : $id);  //trap zero index
		try {
			parent::__construct('overlay',null, $id);
		} catch (ZF4_Db_Table_Exception $e) {
			throw new Application_Model_Exception_InvalidRecord();
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
     * Return list of overlay details
     *
     * @return array
     */
    public function getStdOverlays() {
    	$user = ZF4_User::getSessionIdentity();
    	$model = new Application_Model_Overlay();
    	$select = $model->select()->from($model)
    					->where('orgId = ?',intval($user['orgId']));
    					//->where('tag != ?','none');
    	$rows = $model->fetchAll($select);
    	$retArr = array();
    	foreach ($rows as $row) {
    		if (in_array($row->tag,array('red','green','blue'))) {
    			$tag = $row->tag;
    		} else {
    			$tag = 'ovl' . $row->id;
    		}
    		$retArr[$tag] = $row->toArray();
    	}
    	return $retArr;
    }
   
    public static function getQuerySelect(){
    	$ovls = self::getStdOverlays();
    	array_unshift($ovls,array('name'=>'ALL','id'=>'ALL'));
    	//construct the selectors
    	$xhtml = "<table><tbody>";
    	$xhtml .= "<tr><td><label for='ovlOvl'>Choose overlays</label></td></tr>";
    	$xhtml .= "<tr><td>";
    	$check = true;
    	foreach ($ovls as $ovl) {
    		$xhtml .= "<input type='checkbox' name='ovlOvl' value='{$ovl['id']}' ";
    		if ($check) {
    			$xhtml .= "checked='checked' class='chkAll' id='chkAllOvl' onClick='setCheckAll(this)' ";
    			$check = false;
    		} else {
    			$xhtml .= "class ='chkSingle' rel='chkAllOvl' onClick='setCheckSingle(this)' ";
    		}
    		$xhtml .= "/><span class='mapSelCheck'>{$ovl['name']}</span>";
    	}    	
    	$xhtml .= "</td></tr>";
		$xhtml .= "</tbody></table>";
		
		return $xhtml;    	
    }
    
    /**
     * Check to see if incoming data has tag field set
     * If it does then remove other tags in data set
     * as we can only have one record per colour tag name
     *
     * @param array $data
     */
    protected function _checkTag(array $data) {
    	if (isset($data['tag']) && in_array($data['tag'],array('red','green','blue'))) {
    		//unset all other fields with the colour tag as there can only be one
    		//have to do it via general db handler else we'll trigger method in this class!
    		if (!isset($data['orgId'])) {
    			$user = ZF4_User::getSessionIdentity();
    			$orgId = $user['orgId'];
    		} else {
    			$orgId = $data['orgId'];
    		}
    		$db= Zend_Db_Table_Abstract::getDefaultAdapter();
    		$sql = "update overlay set tag='none' where tag='{$data['tag']}' and orgId={$orgId}";
    		$db->exec($sql);
    	}
    }
    
    /**
     * Extend ancestor to check tag
     *
     * @param array $data
     * @return int
     */
    public function insert(array $data) {
    	$db= Zend_Db_Table_Abstract::getDefaultAdapter();
    	$ret = 0;
    	try {
    		$db->beginTransaction();
    		$this->_checkTag($data);
    		$ret = parent::insert($data);
    		$db->commit();
    	} catch (Exception $e) {
    		$db->rollback();
    	}
    	return $ret;
    }
    
    /**
     * Extend ancestor to check tag
     *
     * @param array $data
     * @param string|array $where
     * @return int
     */
    public function update(array $data, $where) {
    	$db= Zend_Db_Table_Abstract::getDefaultAdapter();
    	$ret = 0;
    	try {
    		$db->beginTransaction();
    		$this->_checkTag($data);
    		$ret = parent::update($data, $where);
    		$db->commit();
    	} catch (Exception $e) {
    		$db->rollback();
    	}
    	return $ret;
    }
}
