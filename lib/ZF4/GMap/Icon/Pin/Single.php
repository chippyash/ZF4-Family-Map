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
 * Defines an Pin-Single Icon for the GMap interface
 * Corresponds to ZF4_GMap::ICON_PIN_SINGLE
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Icon
 */
class ZF4_GMap_Icon_Pin_Single 
	extends ZF4_Object_Virtual implements ZF4_GMap_Interface_Icon {

	/**
	 * Public variables are as for the GIcon properties
	 */

	/**
	 * Available colours
	 *
	 * @var array
	 */
	protected $_colours = array('purple','yellow','blue','white','green','red','black','orange','gray','brown');

	/**
	 * url template to letter image file
	 *
	 * @var string
	 */
	public $image = "'http://labs.google.com/ridefinder/images/mm_20_%s.png'";
	/**
	 * url to shadow file
	 *
	 * @var string
	 */
	public  $shadow = "'http://labs.google.com/ridefinder/images/mm_20_shadow.png'";
	/**
	 * Google api declation for size of icon
	 *
	 * @var string
	 */
	public $iconSize = "new google.maps.Size(20, 34)";
	/**
	 * Google api declation for size of shadow
	 *
	 * @var string
	 */
	public $shadowSize = "new google.maps.Size(37, 34)";
	/**
	 * Google api declation for icon anchor point
	 *
	 * @var string
	 */
	public $iconAnchor = "new google.maps.Point(9, 34)";
	/**
	 * Google api declaration for info window acnchor point
	 *
	 * @var string
	 */
	public $infoWindowAnchor = "new google.maps.Point(9, 2)";

	/**
	 * Constructor
	 *
	 * @param array $params required parameters for Pin_Single icon
	 * 				string $colour colour of pin to use
	 * @param boolean $noLang No language support if true, default true
	 * @throws ZF4_GMap_Exception if invalid colour string given
	 */
	public function __construct($params = null, $noLang = true) {
		parent::__construct($noLang);
		extract($params);
		//check colour
		$colour = strtolower($colour);
		if (!$this->isColour($colour)) {
			throw new ZF4_GMap_Exception("Invalid colour ({$colour}) given to " . __CLASS__, Zend_Log::ERR );
		}
		//set the correct colour image file to use
		$this->image = sprintf($this->image,$colour);
	}

    /**
     * Return json encoded public variables of this object
     *
     * Overides ancestor
     *
     * @param int $opt IGNORED
     * @return string
     */
    public function toJson($opt = 0) {
		$params = $this->toArray();
		unset($params['id']);
		$json = "{";
		foreach ($params as $key => $value) {
			$json .= "{$key}:{$value},";
		}
		$json = rtrim($json,',');
		$json .= "}";
		return $json;
	}

	/**
	 * Return google api declaration for icon
	 *
	 * @return string
	 */
	public function toJScript() {
		$iconObj = $this->toJson();
		$jscript = "new google.maps.markerImage({$iconObj})";
		return $jscript;
	}

	/**
	 * Return array of allowable colour names
	 *
	 * @return array
	 */
	public function getColours() {
		return $this->_colours;
	}

	/**
	 * Does this icon type support a particular colour?
	 *
	 * @param string $colour
	 * @return boolean
	 */
	public function isColour($colour) {
		return (in_array($colour,$this->_colours));
	}

		/**
	 * Is this icon type cyclical
	 * i.e. does it change on each incarnation
	 *
	 * @return boolean
	 */
	public function isCyclical() {return false;}
}