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
 * Abstract Icon
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Icon
 */
abstract class ZF4_GMap_Abstract_Icon extends ZF4_Object_Virtual implements ZF4_GMap_Interface_Icon {
    
    /**
     * Icon parameters are as for the google.maps.MarkerImage properties
     */

    public $url;
    public $size;
    public $anchor;
    public $origin;
    public $scaledSize;
    public $shadow;
    public $shadowSize;
    
    /**
     * Initially defines the element type a parameter
     * Is converted into the element type on construction
     * 
     * @var array 
     */
    protected $_options = array(
        'url' => 'url',
        'size' => 'size',
        'anchor' => 'point',
        'origin' => 'point',
        'scaledSize' => 'size',
        'shadow' => 'url',
        'shadowSize' => 'size'    
    );
               
    /**
     * Constructor
     * 
     * Extend your ancestor before calling this to check for requried parameters
     * for the icon type.  
     * 
     * This does not do any checking for availability but simply sets what is available
     * It does check the parameter types and throws exception on wrong types
     *
     * @param array $params array of icon parameters
     * 	string url [optional] url of icon image
     * 	array size [optional] [width,height] size of icon image in px
     * 	array anchor [optional] [x,y] anchor point
     * 	array origin [optional] [x,y] origin point
     * 	array scaledSize [optional] [width, height] size to scale image: ratio
     *  string shadow [optional] url to shadow image
     *  array shadowSize [optional] [width,height] size of shadow image in px
     * 
     * @param boolean $noLang No language support if true, default true
     */
    public function __construct(array $params = array(), $noLang = true) {
        //set up each option - this will do validation for each element
        foreach($this->_options as $key => $type) {
            if (array_key_exists($key, $params)) {
                $class = 'ZF4_GMap_Element_' . ucfirst($type);
                $this->_options[$key] = new $class($key, $params[$key]);
                $this->$key = $params[$key];
            } else {
                //remove the option
                unset($this->_options[$key]);
            }
        }
        parent::__construct($noLang);
    }

    /**
     * Return the icon as a JSON object string
     *
     * @return string
     */
    public function toJson() {
        $json = '{';
        foreach ($this->_options as $key=>$element) {
            $json .= '"' . $key . '":' . $element->toJson() . ',';
        }
        $json = rtrim($json,',');
        $json .= '}';
        return $json;
    }

    /**
     * Return google api declaration for icon
     *
     * @return string
     */
    public function toJScript() {
        $iconObj = $this->toJson();
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
        return false;
    }

}