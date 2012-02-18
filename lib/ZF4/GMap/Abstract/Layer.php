<?php

/**
 * ZF4 Library
 * 
 * Google Maps integration
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Layer
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
 * Abstract Layer
 *
 * Usage:
 * $map = new ZF4_GMap_Map('googleMap',$params);  //create a new map
 * $layer = new ZF4_GMap_Layer()
 * $map->addlayer($layer)
 * 
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Layer
 */
abstract class ZF4_GMap_Abstract_Layer extends ZF4_Object_Virtual implements ZF4_GMap_Interface_Layer {
    /* public attributes */

    /**
     * Name of layer
     *
     * @var string
     */
    public $id;

    /**
     * Locations to be displayed on this layer
     *
     * @var array
     */
    protected $_locations = array();

    /**
     * Default icon pin to use
     *
     * @var ZF4_GMap_Icon_Abstract
     */
    protected $_defIcon;

    /**
     * The map that this layer is attached to
     *
     * @var ZF4_GMap_Map
     */
    protected $_map;

    /**
     * Hide the layer when it is added to map
     *
     * @var boolean
     */
    protected $_hideOnStart = false;

    /**
     * Set hide-on-start flag
     * If set true then layer will not be displayed when map is initialised
     *
     * @param boolean $hidden
     */
    public function setHidden($hidden = true) {
        $this->_hideOnStart = $hidden;
    }

    /**
     * Get the hide-on-start flag
     *
     * @return boolean
     */
    public function getHidden() {
        return $this->_hideOnStart;
    }

    /**
     * Constructor
     * 
     * Options:
     *  hidden		boolean	Whether layer is hidden on initialisation of map or not - default false
     * 
     * @param string $name  Layer name
     * @param ZF4_GMap_Map $map The map that this layer is attached to
     * @param ZF4_GMap_Icon_Abstract $icon Default icon pin to use - if null will use the one set on the map
     * @param array $options
     * @param boolean $noLang if true do not use language support - default = true
     */
    public function __construct($name, ZF4_Gmap_Map $map, ZF4_GMap_Abstract_Icon $icon = null, array $options = array(), $noLang = true) {
        parent::__construct($noLang);
        //set up attributes
        $this->id = str_replace(' ', '_', $name);
        $this->_hideOnStart = (isset($options['hidden']) ? $options['hidden'] : false);
        $this->_defIcon = ($icon != null ? $icon : $map->getIcon());
        $this->_map = $map;
    }

    /**
     * render the javascript required to add layer to a map
     * 
     * @param ZF4_GMap_Map $map Handle to the map that the layer is being attached to
     * @param int $idx Layer index
     * @return string javascript
     */
    public function toJScript(ZF4_GMap_Map $map, $idx) {
        $mapName = ZF4_GMap::MAP_VAR_PREFIX . $map->id;
        $jscript = "{$idx}:";
        $jscript .= $this->_toJscriptThis($mapName);
        return $jscript;
    }

    /**
     * Render the javascript to add this layer to the output
     *
     * @param string $mapName name of map that layers are being added on
     * @return string javascript
     */
    abstract protected function _toJscriptThis($mapName);

    /**
     * Add a location to the layer
     *
     * @param ZF4_GMap_Location $loc  The location
     * @param ZF4_GMap_Icon_Interface $icon The marker pin to use - if null will use the default pin if loc doesn't have one
     * @return ZF4_GMap_Layer_Abstract Fluent Interface
     */
    public function addLocation(ZF4_GMap_Location $loc, ZF4_GMap_Interface_Icon $icon = null) {
        if ($icon != null)
            $loc->setIcon($icon);
        if ($loc->getIcon() == null)
            $loc->setIcon($this->_defIcon);
        $this->_locations[] = $loc;
    }

    /**
     * Return the locations for this layer
     *
     * @return aray of ZF4re_GMap_Location
     */
    public function getLocations() {
        return $this->_locations;
    }

    /**
     * get the default icon for this layer
     *
     * @return ZF4_GMap_Icon_Abstract
     */
    public function getIcon() {
        return $this->_defIcon;
    }

    /**
     * set the default icon for this layer
     *
     * @return ZF4_GMap_Abstract_Layer Fluent Interface
     */
    public function setIcon($icon) {
        $this->_defIcon = $icon;
        return $this;
    }

}