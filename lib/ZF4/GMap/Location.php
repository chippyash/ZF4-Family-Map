<?php

/**
 * ZF4 Library
 *
 * Google Maps integration
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Location
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
 * Defines a location for the GMap interface
 *
 * Usage:
 * $map = new ZF4_GMap_Map('googleMap',$params);  //create a new map
 * $loc = new ZF4_GMap_Location($name,$lat,$lng,$address,$description);//add a location
 * $map->addLocation($loc);
 * $view->GMap()->addMap($map);	//add the map to map handler
 * 
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Location
 */
class ZF4_GMap_Location extends ZF4_Object_Virtual {

    public $id = '';    //location name
    public $desc = null;  //location description
    public $addr = '';   //address of location or set lat & lng
    public $lat = 0;   //location latitude
    public $lng = 0;   //location longitude
    
    /**
     * pin/icon to use for location
     * 
     * @var ZF4_GMap_Interface_Icon 
     */
    public $pin = null;  
    
    //marker pin options
    public $clickable = true;
    public $draggable = false;
    public $visible = true;
    public $cursor = '';
    public $flat = false;

    /**
     * Set true to not use language translation
     *
     * @var boolean
     */
    protected $_noLang = true;

    /**
     * Constructor
     *
     * @param string $name Location name
     * @param string $description Info window description for location.  See settings for ZF4_GMap_Map infoType
     * @param double $lat latitude of location
     * @param double $lng longitude of location
     * @param string $address Address of location or use lat & long
     * @param ZF4_GMap_Interface_Icon $pin  Pin marker to use
     * @param boolean $noLang Do not use language translation if true, default = true
     * @throws ZF4_GMap_Exception if name not set
     * @throws ZF4_GMap_Exception if address or (lat & lng) not set
     */
    public function __construct($name, $description = null, $lat = null, $lng = null, $address = null, ZF4_GMap_Interface_Icon $pin = null, $noLang = true) {
        $this->_noLang = $noLang;
        parent::__construct($noLang);
        $this->pin = $pin; //marker pin to use for location
        if (empty($name)) {
            throw new ZF4_GMap_Exception("Empty name not allowed", Zend_Log::ERR);
        }
        if (is_null($address) && is_null($lat) && is_null($lng)) {
            throw new ZF4_GMap_Exception("One of address, or lat & lng must be provided", Zend_Log::ERR);
        }
        if (!is_null($address) && is_null($lat) && is_null($lng)) {
            $decoder = new ZF4_GMap_Decoder();
            $coords = $decoder->getLocation($address);
            if ($coords !== false) {
                $lat = $coords->lat;
                $lng = $coords->lng;
            } else {
                throw new ZF4_GMap_Exception("Unable to get location from address", Zend_Log::ERR);
            }
        }
        //set public vars
        $this->id = $name;
        $this->desc = $description;
        $this->addr = $address;
        $this->lat = $lat;
        $this->lng = $lng;
    }

    /**
     * Return json encoded public variables of this object
     * - removes the marker pin options
     *
     * Overides ancestor
     *
     * @param int $opt IGNORED
     * @return string
     */
    public function toJson($opt = 0) {
        $params = $this->toArray();
        if ($opt == 0) { //get the location details
            $params['name'] = $this->id;
            unset($params['clickable']);
            unset($params['draggable']);
            unset($params['visible']);
            unset($params['cursor']);
            unset($params['flat']);
        } elseif ($opt == 1) { //get the pin details
            unset($params['id']);
            unset($params['desc']);
            unset($params['addr']);
            unset($params['lat']);
            unset($params['lng']);
            unset($params['pin']);
        }

        $json = Zend_Json::encode($params);
        return $json;
    }

    /**
     * Render the google script to add a location to a map
     * We are actually adding a marker in Google terms.
     *
     * @param ZF4_GMap_Map $map The map to render for
     * @param ZF4_GMap_Marker $marker The pin to use
     * @param int location index
     */
    public function toJScript(ZF4_Gmap_Map $map, ZF4_GMap_Marker $marker = null, $idx) {
        $jscript = '';
        //create pin if required
        if ($this->pin != null) {
            $jscript .= $this->pin->toJScript();
        }
        $marker = new ZF4_GMap_Marker($this->pin, null, $this->_noLang);
        //create marker
        $jscript .= $marker->toJScript($map, $this, $idx);
        return $jscript;
    }

    /**
     * Set the pin icon
     * @param ZF4_GMap_Interface_Icon $icon 
     * @return Fluent Interface
     */
    public function setIcon(ZF4_GMap_Interface_Icon $icon) {
        $this->_pin = $icon;
        return $this;
    }
    
    /**
     * Get the pin icon
     * @return ZF4_GMap_Interface_Icon
     */
    public function getIcon() {
        return $this->_pin;
    }
}