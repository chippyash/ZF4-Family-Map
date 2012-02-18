<?php
/**
 * ZF4 Library
 * 
 * Google Maps integration
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Info
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
 * Utility class for GMap info window templates
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Info
 */
class ZF4_GMap_Info_Template {

	/**
	 * default template to use
	 */
	private $_template = "<div id='{node}' class='googleInfo'><h3>{name}</h3>{info}</div>";

	/**
	 * Constructor
	 *
	 * @param string $template html declaration for info window dom container 
	 */
	public function __construct($template = null) {
		if (!is_null($template)) $this->_template = $template;
	}
	
	/**
	 * Set the template
	 *
	 * @param string $template html declaration for info window dom container 
	 */
	public function setTemplate($template) {
		if (!is_null($template)) $this->_template = $template;
	}
	
	/**
	 * retrieve teh template
	 *
	 * @return string
	 */
	public function getTemplate() {
		return $this->_template;
	}
	
	/**
	 * Render the template
	 * Parameters must contain at least node,name & info
	 *
	 * @param array $params parameters to parse into template
	 * @return string Html string
	 * @throws ZF4_GMap_Exception if parameters not set correctly
	 */
	public function render($params) {
		if (!is_array($params)) {
			throw new ZF4_GMap_Exception("Parameters must be array to method: " . __FUNCTION__,Zend_Log::ERR );
		}
		if (!isset($params['node']) || !isset($params['name']) || !isset($params['info'])) {
			throw new ZF4_GMap_Exception("Required parameters not set for method: " . __FUNCTION__,Zend_Log::ERR );			
		}
		$html = $this->_template;
		foreach ($params as $key=>$value) {
			$html = str_replace("{" . $key ."}",$value,$html);
		}
		return $html;
	}
}