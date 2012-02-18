<?php

/**
 * ZF4 Library
 *
 * Google Maps integration
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Map
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
 * Global GMap constants
 */
require_once("ZF4/GMap.php");

/**
 * Defines a map for the GMap interface
 *
 * Usage:
 * $map = new ZF4_GMap_Map('googleMap',$params);  //create a new map
 * $view->GMap()->addMap($map);	//add the map to map handler
 * $map->render();  			//at the place where you want the map
 * $view->GMap()->initMaps() 	//after you have rendered all maps
 *
 * You can set any css style parameters by setting a variable prefixed with style_
 * e.g. $map->style_height = 450
 * Style parameters will be added to the style tag for the div declaration
 * By default the div is given a default classname of googleMapCanvas
 * You can change this by setting $map->className
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Map
 */
class ZF4_GMap_Map extends ZF4_Object_Virtual {

    public $id = '';     //Domain holder tag id
    public $style_height = '450px'; //Height of map
    public $style_width = '300px'; //Width of map
    public $startLat = 0;   //map starting latitude
    public $startLng = 0;   //map starting longitude
    public $controls = true;  //add google map controls to map
    public $controlStyle = ZF4_GMap::MAPCTRL_STYLE_DEFAULT;  //control style
    public $controlPos = ZF4_GMap::MAPCTRL_POS_TOPRIGHT; //control position
    public $controlMapTypes = array(
        ZF4_GMap::MAPTYPE_NORMAL,
        ZF4_GMap::MAPTYPE_TERRAIN,
        ZF4_GMap::MAPTYPE_HYBRID,
        ZF4_GMap::MAPTYPE_SATELLITE); //allowable map types
    public $mapType = ZF4_GMap::MAPTYPE_NORMAL; //starting map type (normal, satellite, hybrid, terrain)
    public $labels = true;   //display marker labels
    public $zoom = 4;    //map zoom level if autozoom = false
    public $autozoom = true;  //auto zoom to enclose all points
    public $debug = false;   //allow debug messages.  Overides global debug state
    public $autocenter = true;  //auto centre map on first address else use supplied lat and long
    public $dispPopText = false; //display name information in addresses on markers
    public $infoType = 0;   //information window type
    public $searchBar = false;  //show google searchbar on map
    public $navigationControl = true; //show navigation control
    public $scaleControl = true;  //show scaling control

    /**
     * Class name to apply to a map div tag
     *
     * @var string
     */
    public $className = 'googleMapCanvas';

    /**
     * Layers on the map
     * Locations belong to a layer
     *
     * @var array
     */
    protected $_layers = array();

    /**
     * Locations on the map
     *
     * @var array of ZF4_GMap_Location
     */
    protected $_locations = null;

    /**
     * Default marker
     *
     * @var ZF4_GMap_Abstract_Icon;
     */
    protected $_defIcon = null;

    /**
     * Template to use for info windows
     *
     * @var ZF4_GMap_Info_Template
     */
    protected $_infoTemplate = null;

    /**
     * function cache
     *
     * @var Zend_Cache
     */
    protected $_funcCache = null;

    /**
     * Use language support
     *
     * @var boolean
     */
    private $_noLang = true;

    /**
     * Constructor
     *
     * @param string $domId ID name of DOM tag item that will hold the map
     * @param array $params set-up parameters for the map (you can also set them individually)
     * @param ZF4_GMap_Abstract_Icon|string  $icon Default map icon
     * @param int $infoType = ZF4_GMap::INFO_..
     * @param boolean $noLang if True do not use language support, default = true
     * @throws ZF4_GMap_Exception if domId not set
     */
    public function __construct($domId, $params = null, $icon = null, $infoType = 0, $noLang = true) {
        $this->_noLang = $noLang;
        parent::__construct($noLang);
        if (empty($domId)) {
            throw new ZF4_GMap_Exception("Empty domId not allowed", Zend_Log::ERR);
        }
        //set map id
        $this->id = $domId;

        //set default icon
        $this->setIcon($icon);

        //default debug state
        $this->debug = ZF4_GMap::getDebug();
        //info window type
        $this->infoType = $infoType;

        //info window template default
        $this->_infoTemplate = new ZF4_GMap_Info_Template();

        //transfer any parameters to public vars list
        if (!is_null($params)) {
            foreach ($params as $key => $value) {
                $this->$key = $value;
            }
        }
        //refactor mapType to be the correct google maps api declaration
        $t = strtoupper($this->mapType);
        //$this->mapType = "G_{$t}_MAP";
        $this->mapType = "google.maps.MapTypeId.{$t}";

        //set up the cache
        $frontendOptions = array(
            'lifetime' => ZF4_GMap::getCacheTime()
        );
        $backendOptions = array(
            'cache_dir' => ZF4_Defines::dirCache('gmap')
        );
        $this->_funcCache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }

    /**
     * Set the icon to use for subsequent addLocation and addLayer operations
     *
     * @param ZF4_GMap_Abstract_Icon|int|null $icon ZF4_GMap::ICON_.. icon type
     * @param array|null $options options required for a marker icon type
     */
    public function setIcon($icon = null, $options = null) {
        if (is_string($icon)) {
            $icon = ZF4_GMap_Factory_Icon::factory($icon, $options);
        } elseif (is_null($icon)) {
            $icon = ZF4_GMap_Factory_Icon::factory(ZF4_GMap::ICON_DEFAULT, $options);
        }
        $this->_defIcon = $icon;
    }

    /**
     * return current default icon
     *
     * @return ZF4_GMap_Abstract_Icon
     */
    public function getIcon() {
        return $this->_defIcon;
    }

    /**
     * Add a location to a map layer
     *
     * @param ZF4_GMap_Location $loc
     * @param int $lId	layer id to add location to - default is last added layer
     * @param ZF4_GMap_Icon_Interface  Pin to use - if null will use the layer default
     * @return Fluent_Interface
     */
    public function addLocation(ZF4_GMap_Location $loc, $lId = null, ZF4_GMap_Icon_Interface $pin = null) {
        $lId = intval(($lId == null ? count($this->_layers) - 1 : $lId));
        $pin = (null == $pin ? $this->_layers[$lId]->getIcon() : $pin);
        $this->_layers[$lId]->addLocation($loc, $pin);
        return $this;
    }

    /**
     * Add multiple locations to a layer
     *
     * @param array $locs array of ZF4_GMap_Location
     * @param ZF4_GMap_Icon_Interface  Pin to use - if null will use the layer default
     * @param int $lId	layer id to add location to - default is last added layer
     * @return Fluent_Interface
     */
    public function addMultiLocations(array $locs, ZF4_GMap_Icon_Interface $pin = null, $lId = null) {
        foreach ($locs as $loc) {
            $this->addLocation($loc, $pin, $lId);
        }
        return $this;
    }

    /**
     * Add a layer to the map
     *
     * @param ZF4_GMap_Layer|string|int $layer A layer or layertype to create
     * @param array $layerParams  If calling with $layer == string name or int, then parameters for layer
     * 							  Required for all layers;
     * 								id => layer name
     * 							  optional for $layer = string|int
     * 								icon => string|int|AWare_Gmap_Abstract_Icon [optional] Icon to use for layer
     * @param boolean $noLang Set true to not use language support - default = true
     * @return int Id of layer - you will need these for subsequent calls to addLocation()
     * @throws ZF4_GMap_Exception if invalid params
     */
    public function addLayer($layer, $layerParams = null, $noLang = true) {
        if (!isset($layerParams['id']))
            throw new ZF4_GMap_Exception('No layer id specified');
        $lId = count($this->_layers);
        if (is_string($layer) || is_int($layer)) {
            if (!isset($layerParams['icon'])) {
                $layerParams['icon'] = $this->getIcon();
            }
            $layerParams['map'] = $this;
            $layer = ZF4_GMap_Factory_Layer::factory($layer, $layerParams, $noLang);
        } elseif (!$layer instanceof ZF4_GMap_Layer_Interface) {
            throw new ZF4_GMap_Exception('Invalid layer');
        }
        if ($layer->getIcon() == null) {
            $layer->setIcon($this->getIcon());
        }
        $this->_layers[$lId] = $layer;
        return $lId;
    }

    /**
     * Get layers added to map
     *
     * @return array of ZF4_GMap_Layer
     */
    public function getLayers() {
        return $this->_layers;
    }

    /**
     * set information window template
     *
     * @param ZF4_GMap_Info_Template $template
     */
    public function setInfoTemplate(ZF4_GMap_Info_Template $template) {
        $this->_infoTemplate = $template;
    }

    /**
     * get the information window template
     *
     * @return ZF4_GMap_Info_Template
     */
    public function getInfoTemplate() {
        return $this->_infoTemplate;
    }

    /**
     * Render the map html source
     * Called by render()
     *
     * @return string
     */
    protected function _renderDirect() {
        //get any style params
        $params = $this->toArray();
        $style = "";
        foreach ($params as $key => $value) {
            if (strpos($key, 'style_') !== false) {
                $style .= str_replace('style_', '', $key) . ":" . $value . ";";
            }
        }
        if (!empty($style)) {
            $style = " style='{$style}'";
        }

        //construct html for canvas
        $html = "<div id='{$this->id}' class='{$this->className}'{$style}></div>";

        //construct information dom elements if required
        if ($this->infoType == ZF4_GMap::INFO_DOM) {
            $html .= "<div class='googleInfoWrap'>";
            $domId = ZF4_GMap::DOM_DIV_PREFIX;
            $domIdx = 0; //counter for information dom elements
            //markers first
            if (count($this->_locations) > 0) {
                foreach ($this->_locations as &$loc) {
                    $html .= $this->_infoTemplate->render(array('node' => $domId . $domIdx,
                        'name' => $loc['loc']->id,
                        'info' => $loc['loc']->desc,
                        'lat' => $loc['loc']->lat,
                        'lng' => $loc['loc']->lng,
                        'id' => 'm' . $domIdx));
                    $domIdx++;
                }
            }
            //layers
            if (count($this->_layers) > 0) {
                foreach ($this->_layers as $layer) {
                    $html .= $layer['layer']->renderInfoDom($domIdx, $this->_infoTemplate);
                }
            }
            $html .= "</div>";
        }
        return $html;
    }

    /**
     * Render the map html from cache
     * Called by render()
     *
     * @return string
     */
    protected function _renderCache() {
        $id = "_renderDirect";
        if (!($html = $this->_funcCache->load($id))) {
            $html = $this->_renderDirect();
            $this->_funcCache->save($html, $id);
        }
        return $html;
    }

    /**
     * Render the map item HTML
     * This just creates the HTML div container for the map
     *
     * @param boolean $echo set false if you want to return the string
     * @return string|boolean	HTML for map or TRUE
     */
    public function render($echo = true) {
        if (ZF4_GMap::getCaching()) {
            $html = $this->_renderCache();
        } else {
            $html = $this->_renderDirect();
        }
        //output as required
        if ($echo) {
            echo $html . PHP_EOL;
            return true;
        } else {
            return $html;
        }
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
        $json = "{";
        $tmp = str_replace('px', '', $this->style_width);
        $json .= "width:{$tmp},";
        $tmp = str_replace('px', '', $this->style_height);
        $json .= "height:{$tmp},";
        $tmp = ($this->controls ? 'true' : 'false');
        $json .= "controls:{$tmp},";
        $json .= "mapType:'{$this->mapType}',";
        $tmp = ($this->labels ? 'true' : 'false');
        $json .= "labels:{$tmp},";
        $json .= "zoom:{$this->zoom},";
        $tmp = ($this->autozoom ? 'true' : 'false');
        $json .= "autozoom:{$tmp},";
        $tmp = ($this->debug ? 'true' : 'false');
        $json .= "debug:{$tmp},";
        $json .= "latitude:{$this->startLat},";
        $json .= "longitude:{$this->startLng},";
        $tmp = ($this->autocenter ? 'true' : 'false');
        $json .= "autocenter:{$tmp},";
        $tmp = ($this->dispPopText ? 'true' : 'false');
        $json .= "dispPopText:{$tmp},";
        $tmp = ($this->navigationControl ? 'true' : 'false');
        $json .= "navigationControl:{$tmp}";
        $tmp = ($this->scaleControl ? 'true' : 'false');
        $json .= "scaleControl:{$tmp}";
        if (!is_null($this->html)) {
            $json .= "html:'{$this->html}'";
        }
        if (!is_null($this->anchor)) {
            $json .= "anchor:'{$this->anchor}'";
        }
        $json .= "infoType:{$this->infoType},";

        //add marker
        $tmp = $this->marker->toJson();
        $json .= "marker:{$tmp}";

        $json = rtrim($json, ',');
        $json .= "}";
        return $json;
    }

    /**
     * render the javascript directly
     *
     * @return string
     */
    protected function _toJScriptDirect() {
        $sep = (APPLICATION_ENV == 'production' ? '' : ZF4_GMap::lineSeperator());
        $mapIdName = ZF4_GMap::MAP_VAR_PREFIX . $this->id;
        $mapObj = ZF4_GMap::MAP_GLOBAL_VAR . "." . $mapIdName;
        //basic map instantiation
        $opts = '{';
        $opts .= "center:new google.maps.LatLng({$this->startLat}, {$this->startLng}),";
        $opts .= "mapTypeId:{$this->mapType},";
        $opts .= "mapTypeControl:" . ($this->controls ? 'true' : 'false') . ',';
        if ($this->controls) {
            $opts .= "mapTypeControlOptions:{mapTypeIds:";
            if (is_array($this->controlMapTypes)) {
                $opts .= '[';
                foreach ($this->controlMapTypes as $value) {
                    $opts .= "google.maps.MapTypeId." . $value . ',';
                }
                $opts = rtrim($opts, ',') . ']';
            } else {
                $opts .= $opts .= "google.maps.MapTypeId." . $this->controlMapTypes;
            }
            $opts .= ",position:google.maps.ControlPosition.{$this->controlPos},";
            $opts .= "style:google.maps.MapTypeControlStyle.{$this->controlStyle}";
            $opts .= "},";
        }
        $opts .= "scaleControl:" . ($this->scaleControl ? 'true' : 'false') . ',';
        $opts .= "zoom:{$this->zoom}";
        $opts .= '}';
        $jscript = "{$mapObj} = new google.maps.Map(document.getElementById('{$this->id}'),{$opts});" . $sep;
        /*
          if (isset($this->autozoom) && $this->autozoom) {
          //add bounds object
          $jscript .= ZF4_GMap::MAP_GLOBAL_VAR .".bounds = new google.maps.LatLngBounds();" . $sep;
          } else {
          $this->autozoom = false;
          }
          if (!isset($this->dispPopText) || !$this->dispPopText) {
          $jscript .= "{$mapObj}.disableInfoWindow();" . $sep;
          } else {
          $this->dispPopText = true;
          }
          if (isset($this->searchBar) && $this->searchBar) {
          $jscript .= "{$mapObj}.enableGoogleBar();" . $sep;
          } else {
          $this->searchBar = false;
          }
         */
        //add containers for layers

        if (count($this->_layers) > 0) {
            $layerName = ZF4_GMap::MAP_GLOBAL_VAR . ".layers." . $mapIdName;
            $jscript .= "{$layerName} = {";
            $x = 0;
            foreach ($this->_layers as $layer) {
                $jscript .= $layer->toJScript($this, $x) . ',';
                $x++;
            }

            $jscript = rtrim($jscript, ',') . "};" . $sep;
            $jscript .= "GMAPEnableLayers('{$mapIdName}');";
        }

        return $jscript;
    }

    /**
     * render javascript from cache
     *
     * @return string
     */
    protected function _toJScriptCache() {
        $id = "_toJScriptDirect";
        if (!($$jscript = $this->_funcCache->load($id))) {
            $jscript = $this->_toJScriptDirect();
            $this->_funcCache->save($jscript, $id);
        }
        return $jscript;
    }

    /**
     * Render the map as google map api javascript
     *
     * @return string
     *
     */
    public function toJScript() {
        if (ZF4_GMap::getCaching()) {
            $jscript = $this->_toJScriptCache();
        } else {
            $jscript = $this->_toJScriptDirect();
        }
        return $jscript;
    }

}
