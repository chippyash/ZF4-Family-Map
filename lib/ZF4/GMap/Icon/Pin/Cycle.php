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
 * Defines an Pin-Cycle Icon for the GMap interface 
 * Corresponds to ZF4_GMap::ICON_PIN_CYCLE
 * 
 * Each instantiation of this object moves the colour index on one
 * 
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Icon
 */
class ZF4_GMap_Icon_Pin_Cycle 
	extends ZF4_GMap_Icon_Pin_Single implements ZF4_GMap_Interface_Icon {

	/**
	 * Counter used to increment colours
	 *
	 * @var integer
	 */
	private static $_index = 0;
	
	/**
	 * Constructor
	 *
	 * @param array $params
	 * @param boolean $noLang No language support if true, default true
	 */
	public function __construct($params = null, $noLang = true) {
		ZF4_Object_Virtual::__construct($noLang); //skip direct parent constructor
		//set the correct colour image file to use
		$range = count($this->_colours);
		$this->image = sprintf($this->image,$this->_colours[self::$_index % $range]);
		self::$_index ++; //inc the colour index
	}
	
	/**
	 * Is this icon type cyclical
	 * i.e. does it change on each incarnation
	 *
	 * @return boolean
	 */
	public function isCyclical() {return true;}
}