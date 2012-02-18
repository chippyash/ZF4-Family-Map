<?php

/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Customer
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
 * Customer model
 * 
 * Handles all interaction with customer information
 * Within this system customers are known as members
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Customer
 */
class Application_Model_Customer extends Application_Model_Person {

    protected $_validTypes = array('member', 'pupil');
    protected $_invalidTypes = array('staff', 'doctor', 'health visitor', 'carer');

    /**
     * Inserts a new row.
     * 
     * Extends ancestor to ensure that staff flag is unset
     * Also handles categories
     * Adds a uid if not found
     * Adds a member pin number
     * Adds member to system logon in role 'Member'
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */
    public function insert(array $data) {
    	$this->_checkMembership($data);
    	if (isset($data['g'])) unset($data['g']);
		if (!isset($data['uid']) || empty($data['uid'])) {
			//allocate a new customer/member id
			$data['uid'] = self::getNextMbrId();
		}
    	//process the address
    	if (isset($data['hNum'])) {
    		$data['geoId'] = $this->_getAddress($data);
    	} else {
    		$data['geoId'] = 0;
    	}
    	//add a pin number
    	$data['pin'] = $this->genPin();
    	//process categories		
		$cats = null;
    	if (isset($data['cats']) && !empty($data['cats'])) {
    		$cats = $data['cats'];
    	}
   		if (isset($data['cats'])) unset($data['cats']);
    	$ret = parent::insert($data);
    	
    	if ($ret != 0) {
    		//add a system logon
   			$customer = new Application_Model_Customer(intval($ret));
    		
    		$user = new Application_Model_User();
    		$userId = $user->insert(
    			array(
    				'uName' => $customer->uid,
    				'uEmail' => $customer->email,
    				'payrollId' => $customer->uid,
    				'prsnId' => $customer->id
    			)
    		);
    		if ($userId != 0) {
    			//set the password
    			$tUser = new ZF4_User(intval($userId));
    			$tUser->updatePw($data['pin']);
    			//add role membership
    			$tUser->getModel()->addRole('Member');
    		}
    	}
    	if ($ret != 0 && null != $cats) {
    		//update categories
    		$customer->updateCategories(explode(',',$cats));
    	}
    	return $ret;
    }	
    
    /**
     * Handle categories update
     *
     * @param array $data
     * @param string|array $where
     */
    public function update(array $data, $where) {
        $this->_checkMembership($data);
        if (isset($data['g']))
            unset($data['g']);
        //save the categories for later processing
        $cats = null;
        if (isset($data['cats']) && !empty($data['cats'])) {
            $cats = $data['cats'];
        }
        if (isset($data['cats']))
            unset($data['cats']);
        //process the address - ignoreAddress flag sent in from member edit form
        if (!isset($data['ignoreAddress'])) {
	        if (isset($data['hNum'])) {
	            $data['geoId'] = $this->_getAddress($data);
	        } else {
	            $data['geoId'] = 0;
	        }
        } else {
        	unset($data['ignoreAddress']);
        }
        $ret = parent::update($data, $where);
        if (null != $cats) { //update categories irrespective of parent update
            $id = intval(str_replace('id=', '', $where));
            $customer = new Application_Model_Customer($id);
            $customer->updateCategories(explode(',', $cats));
        }
        return $ret;
    }

    /**
     * check that person type always contains at least 'member'
     *
     * @param array $data
     */
    protected function _checkMembership(array &$data) {
        if (isset($data['pType'])) {
            if (is_array($data['pType'])) {
                $data['pType'] = implode(',', $data['pType']);
            }
        } elseif (!isset($data['pType'])) {
            $data['pType'] = 'member';
        }
        if (strstr($data['pType'], 'member') === false) {
            $data['pType'] .= ",member";
        }
    }

    /**
     * Get address location
     *
     * @param array $data
     * @return int
     */
    protected function _getAddress(array &$data) {
        $geo = new Application_Model_Geodata();
        try {
            $geo->fetchByAddress($data['hNum'], $data['pCode']);
        } catch (ZF4_Db_Table_Exception_InvalidId $e) {
            //no record found so we need a new address
            $geoId = $geo->insert(array('hNum' => $data['hNum'], 'pCode' => $data['pCode']));
            $geo = new Application_Model_Geodata(intval($geoId));
            //try to find location
            $geo->setLocation();
        }
        $geoId = intval($geo->id);
        unset($data['hNum']);
        unset($data['pCode']);
        return $geoId;
    }

    /**
     * Add a category for this customer
     *
     * @param int $catId
     */
    public function addCategory($catId) {
        $model = new Zend_Db_Table(array('name' => 'person_cat'));
        $ret = $model->insert(array('catId' => intval($catId), 'prsnId' => intval($this->id)));
        return $ret;
    }

    /**
     * Delete categories for this customer
     *
     * @return Application_Model_Customer Fluent Interface
     */
    public function delCategories() {
        $model = new Zend_Db_Table(array('name' => 'person_cat'));
        $model->delete('prsnId=' . intval($this->id));
        return $this;
    }

    /**
     * Add new categories for this customer
     *
     * @param array $cats  array of category ids
     * @return Application_Model_Customer Fluent Interface
     */
    public function updateCategories(array $cats) {
        $this->delCategories();
        foreach ($cats as $cat) {
            $c = intval($cat);
            if ($c != 0) {  //trap zero category
                $this->addCategory($c);
            }
        }
        return $this;
    }

    /**
     * Get a member/customer id
     * 
     * The organisation tag is for the currently logged on user
     *
     * Returns id in format <OrgTag>nnnnnnn where <OrgTag> is the organisation tag
     * e.g WLC0000001
     * 
     * return string a new 10 character membership/customer id
     */
    public static function getNextMbrId($orgId = null) {
        if (null == $orgId) {
            $user = ZF4_User::getSessionIdentity();
            $orgId = $user['orgId'];
        }
        return Zend_Db_Table_Abstract::getDefaultAdapter()->fetchOne("select getNextMbrId({$orgId}) as id");
    }

    /**
     * Returns a list of items that customers can be selected by
     * NB - Not categories
     * 
     * Supports the map->indexAction()
     *
     * @return html
     */
    public static function getQuerySelect() {
        //Post code out codes
        $model = new Application_Model_Person();
        $user = ZF4_User::getSessionIdentity();
        $custH = new Application_Model_Customer();
        $mask = $custH->getValidMask();
        $select = $model->select()
                        ->setIntegrityCheck(false)
                        ->from(array('p' => 'person'), array())
                        ->join(array('g' => 'geoData'), 'p.geoId=g.id', array('outCode' => new Zend_Db_Expr("left(g.pCode,locate(' ',g.pCode)-1)")))
                        ->where("bin(p.pType+0 & {$mask})")
                        ->where("g.pCode is not null")
                        ->where("g.pCode != ''")
                        ->where("p.orgId=?", intval($user['orgId']))
                        ->group('outCode');
        $postCodes = $model->fetchAll($select)->toArray();
        array_unshift($postCodes, array('outCode' => 'all'));
        //ethnicity
        $select = $model->select()
                        ->from($model, array('ethnicity'))
                        ->distinct()
                        ->where("bin(pType+0 & {$mask})")
                        ->where("orgId=?", intval($user['orgId']));
        $ethnicity = $model->fetchAll($select)->toArray();
        array_unshift($ethnicity, array('ethnicity' => 'all'));
        //language
        $select = $model->select()
                        ->from($model, array('lang'))
                        ->distinct()
                        ->where("bin(pType+0 & {$mask})")
                        ->where("orgId=?", intval($user['orgId']));
        $rows = $model->fetchAll($select)->toArray();
        $langs = array();
        $languages = self::getLanguages();
        foreach ($rows as $key => $lang) {
            if (isset($languages[$lang['lang']])) {
                $langs[$lang['lang']] = $languages[$lang['lang']];
            }
        }
        array_unshift($langs, 'ALL');

        //gender
        $genders = array('all', 'male', 'female', 'undefined');

        //Age ranges
        $ageRanges = self::getAgeSelector();
        array_unshift($ageRanges, 'ALL');

        //expectant
        //$expectants = array('all','no','yes');
        //construct the selectors
        $xhtml = "<table><tbody>";
        $xhtml .= "<tr><td><label for='mbrGender'>Gender</label></td></tr>";
        $xhtml .= "<tr><td>";
        $check = true;
        foreach ($genders as $gender) {
            $xhtml .= "<input type='checkbox' name='mbrGender' value='{$gender}' ";
            if ($check) {
                $xhtml .= "checked='checked' class='chkAll' id='chkAllGender' onClick='setCheckAll(this)' ";
                $check = false;
            } else {
                $xhtml .= "class ='chkSingle' rel='chkAllGender' onClick='setCheckSingle(this)' ";
            }
            $xhtml .= "/><span class='mapSelCheck'>" . strtoupper($gender) . "</span>";
        }
        $xhtml .= "</td></tr>";

        $xhtml .= "<tr><td><label for='mbrExpectant'>Age range</label></td></tr>";
        $xhtml .= "<tr><td>";
        $check = true;
        foreach ($ageRanges as $key => $age) {
            $xhtml .= "<input type='checkbox' name='mbrAge' value='{$key}' ";
            if ($check) {
                $xhtml .= "checked='checked' class='chkAll' id='chkAllAge' onClick='setCheckAll(this)' ";
                $check = false;
            } else {
                $xhtml .= "class ='chkSingle' rel='chkAllAge' onClick='setCheckSingle(this)' ";
            }
            $xhtml .= "/><span class='mapSelCheck'>" . $age . "</span>";
        }
        $xhtml .= "</td></tr>";

        $xhtml .= "<tr><td><label for='mbrPCode'>Post Code Areas</label></td></tr>";
        $xhtml .= "<tr><td>";
        $check = true;
        foreach ($postCodes as $postCode) {
            $xhtml .= "<input type='checkbox' name='mbrPCode' value='{$postCode['outCode']}' ";
            if ($check) {
                $xhtml .= "checked='checked' class='chkAll' id='chkAllPCode' onClick='setCheckAll(this)' ";
                $check = false;
            } else {
                $xhtml .= "class ='chkSingle' rel='chkAllPCode' onClick='setCheckSingle(this)' ";
            }
            $xhtml .= "/><span class='mapSelCheck'>" . strtoupper($postCode['outCode']) . "</span>";
        }
        $xhtml .= "</td></tr>";

        $xhtml .= "<tr><td><label for='mbrEthnicity'>Ethnicity</label></td></tr>";
        $xhtml .= "<tr><td>";
        $check = true;
        foreach ($ethnicity as $ethnic) {
            $xhtml .= "<input type='checkbox' name='mbrEthnicity' value='{$ethnic['ethnicity']}' ";
            if ($check) {
                $xhtml .= "checked='checked' class='chkAll' id='chkAllEthnicity' onClick='setCheckAll(this)' ";
                $check = false;
            } else {
                $xhtml .= "class ='chkSingle' rel='chkAllEthnicity' onClick='setCheckSingle(this)' ";
            }
            $xhtml .= "/><span class='mapSelCheck'>" . strtoupper($ethnic['ethnicity']) . "</span>";
        }
        $xhtml .= "</td></tr>";

        $xhtml .= "<tr><td><label for='mbrLang'>Mother Tongue</label></td></tr>";
        $xhtml .= "<tr><td>";
        $check = true;
        foreach ($langs as $key => $lang) {
            $xhtml .= "<input type='checkbox' name='mbrLang' value='{$key}' ";
            if ($check) {
                $xhtml .= "checked='checked' class='chkAll' id='chkAllLang' onClick='setCheckAll(this)' ";
                $check = false;
            } else {
                $xhtml .= "class ='chkSingle' rel='chkAllLang' onClick='setCheckSingle(this)' ";
            }
            $xhtml .= "/><span class='mapSelCheck'>" . $lang . "</span>";
        }
        $xhtml .= "</td></tr>";

        $xhtml .= "<tr><td><label for='mbrPupil'>Pupils or Others?</label></td></tr>";
        $xhtml .= "<tr><td>";
        $xhtml .= "<input type='checkbox' name='mbrPupil' value='0' ";
        $xhtml .= "checked='checked' class='chkAll' id='chkAllPupil' onClick='setCheckAll(this)' ";
        $xhtml .= "/><span class='mapSelCheck'>All</span>";
        $xhtml .= "<input type='checkbox' name='mbrPupil' value='pupil' ";
        $xhtml .= "class='chkSingle' rel='chkAllPupil' onClick='setCheckSingle(this)' ";
        $xhtml .= "/><span class='mapSelCheck'>Pupils</span>";
        $xhtml .= "<input type='checkbox' name='mbrPupil' value='member' ";
        $xhtml .= "class='chkSingle' rel='chkAllPupil' onClick='setCheckSingle(this)' ";
        $xhtml .= "/><span class='mapSelCheck'>Others</span>";
        $xhtml .= "</td></tr>";

        $xhtml .= "</tbody></table>";

        return $xhtml;
    }
    
	/**
	 * Generate a pin number
	 *
	 * @param int $length	pin length
	 * @return string		The pin
	 */
	public function genPin($length = 6) {
		 // start with a blank password
		  $password = "";

		  // define possible characters
		  $possible = '0123456789ABCDEFGHJKLMNOPQRSTVWXYZ';

		  // set up a counter
		  $i = 0;

		  // add random characters to $password until $length is reached
		  while ($i < $length) {

		    // pick a random character from the possible ones
		    $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);

		    // we don't want this character if it's already in the password
		    if (!strstr($password, $char)) {
		      $password .= $char;
		      $i++;
		    }

		  }
		  // done!
		  return $password;
	}
}
