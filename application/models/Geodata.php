<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Geodata
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
 * Geodata model
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Geodata
 */
class Application_Model_Geodata extends ZF4_Db_Table_Model {
	/**
	 * How many scan entries to look for at one time
	 * NB Google max limit is 2500 per day
	 */
	const MAX_LOOK = 100;
	/**
	 * Entry status - new
	 */
	const STS_NEW = 'new';
	/**
	 * Entry status - failed
	 */
	const STS_FAILED = 'failed';
	/**
	 * Entry status - found
	 */
	const STS_FOUND = 'found';
	
    /**
     * Google Map decoder
     *
     * @var ZF4_GMap_Decoder
     */
    private $_decoder;
	
    /**
     * Constructor
     *
     *
     * @param int $id	geodata record id
     * @throws Application_Model_Exception_InvalidRecord if invalid organisation
     * identifier
     */
    public function __construct($id = null) {
        try {
            parent::__construct('geoData',null, $id);
        } catch (ZF4_Db_Table_Exception $e) {
            throw new Application_Model_Exception_InvalidRecord();
        }
    }

    /**
     * Get the record by the address 2 part unique key
     *
     * @param string $hNum First line of address
     * @param string $pCode Postcode
     * @return Application_Model_Geodata Fluent Interface
     * @throws ZF4_Db_Table_Exception_InvalidId if record does not exist
     */
    public function fetchByAddress($hNum, $pCode) {
    	$row = $this->fetchRow(array("hNum='{$hNum}'","pCode='{$pCode}'"));
    	if (null !== $row) {
			$this->_setRecordData($row->toArray());
		} else {
			throw new ZF4_Db_Table_Exception_InvalidId();
		}
		return $this;
    }
    
    /**
     * Scan Google Maps for unfound geo data records
     * and get geo data for them
     * 
     * This will look for a maximum of self:MAX_LOOK records at a time
     * so that we don't break Google usage limits
     * 
     * To rescan failed lookups - set the $sts parameter = 'failed'
     *
     * @param string $sts Status flag of records to look for - default = 'new'
     * @param int $limit Max number records to process - can overide default self::MAX_LOOK
     * @return int Number of found records
     */
    public function scan($sts = self::STS_NEW, $limit = self::MAX_LOOK) {
    	$ret = 0;
    	//get new records - max limit = MAX_LOOK
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	$select = $db->select()->from('geoData',array('id','hNum','pCode'))
    			->where("sts='{$sts}'")
    			->limit($limit);
    	$rows = $db->fetchAll($select);
    	
    	if (count($rows)>0) {
    		//process each row
    		foreach ($rows as $row) {
    			$address = $row['hNum'] . ', ' . $row['pCode'];
    			if ($this->setLocation($address,$row['id'])) {
    				$ret ++;
    			}
    		}
    		
    	}
    	
    	return $ret;
    }

    /**
     * Return a decoder object
     *
     * @param string $key Google API key if required (not normally)
     * @return ZF4_GMap_Decoder
     */
    private function _getDecoder($key = 'not set') {
    	if (empty($this->_decoder)) {
    		$this->_decoder = new ZF4_GMap_Decoder($key);
    	}
    	return $this->_decoder;
    }
    
    /**
     * Sets the google map location for the address by querying Google Maps API
     *
     * @param string $address  Address to search for - if null then use current record
     * @param int $id Record id to update - if null then use current record
     * @param string $key Google API key - not normally needed
     * @return boolean True if found else False if not found
     */
    public function setLocation($address = null, $id = null, $key = 'not set') {
    	if (null == $id) $id = $this->id;
    	if (null == $address) $address = "{$this->hNum}, {$this->pCode}";
    	$location = $this->_getDecoder($key)->getLocation($address);
		if ($location === false) {
			$this->update(array('sts'=>self::STS_FAILED),"id={$id}");
			return false;
		} else {
			$this->update(array('sts'=>self::STS_FOUND,'lat'=>$location->lat,'lng'=>$location->lng),"id={$id}");
			return true;
		}
    }

}
