<?php
/**
 * ZF4 Library
 * 
 * Google Maps integration
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Icon
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
 * GMap Icon interface
 *
 * A GMap Icon corresponds to a google marker image
 * 
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Icon
 */
interface ZF4_GMap_Interface_Icon {

	/**
	 * Constructor
	 *
	 * @param array $params parameters required for icon
	 * @param boolean $noLang No language support if true, default true
	 */
	public function __construct(array $params = array(), $noLang = true);
	
	/**
	 * Return the icon as a JSON object string
	 *
	 * @return string
	 */
	public function toJson();
	
	/**
	 * Return google api declaration for icon
	 *
	 * @return string
	 */
	public function toJScript();
	
	/**
	 * Is this icon type cyclical
	 * i.e. does it change on each incarnation
	 *
	 * @return boolean
	 */
	public function isCyclical() ;
}