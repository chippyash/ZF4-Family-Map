<?php
/**
 * ZF4 Library
 * 
 * Google Maps integration
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Decoder
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
 * GMap address decoder
 * 
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Decoder
 */
class ZF4_GMap_Decoder {
	
	const GURL = 'http://maps.googleapis.com/maps/api/geocode/json';
	/**
	 * Google API key to use
	 *
	 * @var string
	 */
	protected $_key = '';
	
	/**
	 * Request client
	 *
	 * @var Zend_Http_Client
	 */
	private $_client;
	
	/**
	 * Constructor
	 *
	 * @param string $APIkey  Google API key
	 */
	public function __construct($APIkey = 'not required') {
		$this->_key = $APIkey;
	}
	
	/**
	 * return the request client
	 *
	 * @return Zend_Http_Client
	 */
	protected function _getClient() {
		if (empty($this->_client)) {
			$this->_client= new Zend_Http_Client(self::GURL);
		}
		return $this->_client;
	}
	
	/**
	 * Get a geo location based on an address
	 * 
	 * Will return false if no information retrieved or an error occurred
	 *
	 * @param string $address
	 * @return False|stdClass Standard class element with ->lat, ->lng, ->locType, ->viewport info
	 */
	public function getLocation($address) {
		$address = str_replace(' ','+',htmlentities($address));
		$httpResponse = $this->_getClient()
			   ->setParameterGet('sensor','false')
			   ->setParameterGet('address',$address)
			   ->request();
		if ($httpResponse->getMessage() == 'OK') {
			$response = Zend_Json::decode($httpResponse->getBody(),Zend_Json::TYPE_OBJECT);
			if ($response->status == 'OK') {
				$response = $response->results[0];
				$location = $response->geometry->location;
				$location->locType = $response->geometry->location_type;
				$location->viewport = $response->geometry->viewport;
				return $location;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}