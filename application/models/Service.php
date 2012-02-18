<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Service
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
 * Service model
 * 
 * Services have an enrollment type.  These are currently:
 * free - no enrollment is required - the original service type
 * admin - only administrators can enroll members on this service
 * member - only members can enroll themselves on this service
 * any - anybody can enroll a member for this service
 * staff - only the service manager can enroll members for this service
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Service
 */
class Application_Model_Service extends ZF4_Db_Table_Model {

	/**
	 * Constructor
	 *
	 *
	 * @param int|string $id	Service id
	 * @throws Application_Model_Exception_InvalidService if invalid user identifier
	 */
	public function __construct($id = null) {
		$id = ($id == 0 ? null : $id);  //trap zero index
		try {
			parent::__construct('service',null, $id);
		} catch (ZF4_Db_Table_Exception $e) {
			throw new Application_Model_Exception_InvalidService();
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
    	$model = new Application_Model_Service();
    	$srvcs = $model->getForSelect('name');
    	$srvcs = array(0=>'ALL') + $srvcs;
    	//construct the selectors
    	$xhtml = "<table><tbody>";
    	$xhtml .= "<tr><td><label for='srvcSrvc'>Choose services</label></td></tr>";
    	$xhtml .= "<tr><td>";
    	$check = true;
    	foreach ($srvcs as $id=>$srvc) {
    		$xhtml .= "<input type='checkbox' name='srvcSrvc' value='{$id}' ";
    		if ($check) {
    			$xhtml .= "checked='checked' class='chkAll' id='chkAllSrvc' onClick='setCheckAll(this)' ";
    			$check = false;
    		} else {
    			$xhtml .= "class ='chkSingle' rel='chkAllSrvc' onClick='setCheckSingle(this)' ";
    		}
    		$xhtml .= "/><span class='mapSelCheck'>{$srvc}</span>";
    	}    	
    	$xhtml .= "</td></tr>";
		$xhtml .= "</tbody></table>";
		
		return $xhtml;
    }    
    
    /**
     * Return an array of enrollment types
     *
     * @return array [type=>Type]
     */
    public function getEnrolTypes() {
    	$values = explode(',',$this->_getEnumValues('enrolType'));
    	$keys = $values;
    	foreach ($values as &$value) {
    		$value = ucfirst($value);
    	}
    	return array_combine($keys,$values);
    }
    
    /**
     * Get enrolled members
     *
     * @return array|null
     */
    public function getEnrolled() {
    	$en = new Application_Model_Enrolled();
    	$members = $en->getForSelect('prsnId',array('srvcId = ' . $this->id,"status='enrolled'"));
    	if (count($members)>0) {
	    	$members = implode(',',$members);
	    	$cust = new Application_Model_Customer();
	    	$options = $cust->getForSelect("concat_ws(' ',`uid`,`style`,`fName`,`lName`)",array("id in ({$members})"));
	    	return $options;
    	} else {
    		return null;
    	}
    }

}
