<?php
/**
 * WLC Family Map
 *
 * @category	Family_Map
 * @package  	View
 * @subpackage  Helper
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
 * Branding view helper
 *
 * @category	Family_Map
 * @package  	View
 * @subpackage  Helper
 */
class ZF4_View_Helper_Branding extends Zend_View_Helper_Abstract {
    
	/**
	 * Default branding overlay file name
	 */
	const IMG_OVERLAY = 'ovl.png';
	/**
	 * Default branding logo file name
	 */
	const IMG_LOGO = 'logo.jpg';
	
    /**
     *
     * @var ZF4_Db_Table_Model Organisation object
     */
    private $_org = null;

    /**
     * Have we tried to set the organisations yet?
     *
     * @var boolean
     */
    private $_tried = false;

    protected function _setOrg() {
    	if (!$this->_tried) {
	        if (is_null($this->_org)) {
	            $sess = new Zend_Session_Namespace(ZF4_User::SESS_KEY_USER);
	            $orgId = (isset($sess->user['orgId']) ? intval($sess->user['orgId']) : 0);
	            if ($orgId==0) {
	            	//see if we are on members logon page
	            	$request = Zend_Controller_Front::getInstance()->getRequest();
	            	$org = $request->getParam('org');
	            	if (!is_null($org)) {
	            		try {
	            			$this->_org = new Application_Model_Org($org);
	            		} catch (ZF4_Db_Table_Exception_InvalidId $e) {
	            			$this->_org = null;
	            		}
	            	}
	            }
	            //if the organisation id = zero then we haven't logged on yet
	            //so use the Application branding
	            if ($this->_org == null) {
		            if ($orgId != 0) {
		                $this->_org = new Application_Model_Org(intval($orgId));
		            } else {
		                $this->_org = new Application_Model_Org();
		                $this->_org->id = 0;
		                $this->_org->tag = 'APP';
		                $this->_org->name = 'Family Map';
		                $this->_org->address = '';
		                $this->_org->ctctName = '';
		                $this->_org->ctctTel = '';
		                $this->_org->ctcEmail = '';
		                $this->_org->mapCLat = 0;
		                $this->_org->mapCLong = 0;
		                $this->_org->mapFile = '';
		                $this->_org->ovlFile = self::IMG_OVERLAY ;
		                $this->_org->logoFile = self::IMG_LOGO ;
		                $this->_org->url = '';
		            }
	            }
	        }
    		$this->_tried = true;
    	}
    }
    /**
     * Returns the helper instance
     * 
     * @return ZF4_View_Helper_Branding
     */
    public function branding(){
    	$this->_setOrg();
        return $this;
    }

    /**
     * Returns all organisation data
     *
     * @return array
     */
    public function getAll() {
    	$this->_setOrg();
    	return $this->_org->getRecordData();
    }
    
    /**
     * Return the brand name
     *
     * @return string
     */
    public function name() {
    	$this->_setOrg();
        return $this->_org->name;
    }

    /**
     * Return the brand main site url
     *
     * @return string
     */
    public function url() {
    	$this->_setOrg();
        return $this->_org->url;
    }

    /**
     * Return the brand logo image location for html display
     *
     * The logo file must be place in httpdocs/images/brand/<ORG_TAG>/
     * 
     * @return string
     */
    public function logo() {
    	$this->_setOrg();
    	$img = (isset($this->_org->logoFile) ? $this->_org->logoFile : self::IMG_LOGO);
        return '/images/brand/' . $this->_org->tag . '/' . $img ;
    }

    /**
     * Return the brand overlay image location for html display
     *
     * The overlay file must be place in httpdocs/images/brand/<ORG_TAG>/
     * 
     * @return string
     */
    public function overlay() {
    	$this->_setOrg();
    	$img = (isset($this->_org->ovlFile) ? $this->_org->ovlFile : self::IMG_OVERLAY);
        return '/images/brand/' . $this->_org->tag . '/' . $img ;    	
    }

    /**
     * Return the full path to the google maps overlay file for this organisation
     *
     * The overlay file must be placed in httpdocs/uploads/brand/<ORG_TAG>/
     * 
     * @return string|boolean  The file location or false if none existant
     */
    public function map() {
    	$this->_setOrg();
    	if (!empty($this->_org->mapFile)) {
    		$file = ZF4_ROOT_PATH . '/uploads/brand/' . $this->_org->tag . '/' . $this->_org->mapFile;
    		if (file_exists($file)) {
    			return $file;
    		} else {
    			return false;
    		}
    	} else {
    		return false;
    	}
    }
    
    /**
     * Return the brand contact details
     *
     * @return array [name, address, ctctName, ctctTel, ctctEmail]
     */
    public function contactArray() {
    	$this->_setOrg();
        return array(
            'name'      => $this->_org->name,
            'address'   => $this->_org->address,
            'ctctName'  => $this->_org->ctctName,
            'ctctTel'   => $this->_org->ctctTel,
            'ctctEmail' => $this->_org->ctctEmail
        );
    }

    /**
     * Return brand Google Map details
     *
     * @return array [mapCLat, mapCLong, ovlFile]
     */
    public function mapArray() {
    	$this->_setOrg();
        return array(
            'mapCLat'     => $this->_org->mapCLat,
            'mapCLong'     => $this->_org->mapCLong,
            'mapFile'   => $this->map()
        );
    }
}
