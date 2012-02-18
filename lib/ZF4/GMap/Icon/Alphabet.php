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
 * Defines an Alphabet Icon the GMap interface
 * Corresponds to ZF4_GMap::ICON_ALPHABET
 *
 * Each instantiation of this object moves the alphabet index on one
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Icon
 */
class ZF4_GMap_Icon_Alphabet extends ZF4_GMap_Abstract_Icon {
    
    /**
     * url template to letter image file
     *
     * @var string
     */
    const IMG_URL_TPL = "'http://www.google.com/mapfiles/marker%s.png'";
    
    /**
     * url to shadow file
     *
     * @var string
     */
    const IMG_SHD_TPL = "'http://www.google.com/mapfiles/shadow50.png'";

    /**
     * Public variables are as for the GIcon properties
     */

    /**
     * Counter used to increment alphabet
     *
     * @var integer
     */
    private static $_index = 0;

    /**
     * Google api declation for size of icon
     *
     * @var string
     */
    public $iconSize = "new GSize(20, 34)";

    /**
     * Google api declation for size of shadow
     *
     * @var string
     */
    public $shadowSize = "new GSize(37, 34)";

    /**
     * Google api declation for icon anchor point
     *
     * @var string
     */
    public $iconAnchor = "new GPoint(9, 34)";

    /**
     * Google api declaration for info window anchor point
     *
     * @var string
     */
    public $infoWindowAnchor = "new GPoint(9, 2)";

    /**
     * Constructor
     *
     * @param boolean $noLang No language support if true, default true
     */
    public function __construct(array $params = array(), $noLang = true) {
        //set the correct letter image file to use
        $range = ord('Z') - ord('A') + 1;
        $letter = chr(ord('A') + (self::$_index % $range));
        $params['image'] = sprintf(self::IMG_URL_TPL, $letter);
        //set up other standard parameters for image
        $params['shadow'] = self::IMG_SHD_TPL;
        $params['iconSize'] = array(20,34);
        $params['shadowSize'] = array(37,34);
        $params['anchor'] = array(9,34);
        $params['infoWindowAnchor'] = array(9,2);
        
        parent::__construct($params, $noLang);

        self::$_index++; //inc the letter counter
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
        $params = $this->toArray(true);
        unset($params['id']);
        $json = "{";
        foreach ($params as $key => $value) {
            $json .= "{$key}:{$value},";
        }
        $json = rtrim($json, ',');
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
//		$jscript = "new GIcon({$iconObj})";
        $jscript = "new google.maps.MarkerImage({$iconObj})";
        return $jscript;
    }

    /**
     * Is this icon type cyclical
     * i.e. does it change on each incarnation
     *
     * @return boolean
     */
    public function isCyclical() {
        return true;
    }

}