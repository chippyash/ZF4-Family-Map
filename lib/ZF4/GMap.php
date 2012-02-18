<?php

/**
 * ZF4 Library
 * 
 * Google Maps integration
 *
 * @category 	ZF4
 * @package  	GMap
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
 * GMap Global Class holding constants and static convenience methods.
 * Google Maps integration
 * 
 * @category 	ZF4
 * @package  	GMap
 */
class ZF4_GMap {
    /**
     * Current supported Google Maps 'RELEASE' version
     * 
     * @const string
     */

    const DEFAULT_GMAP_VERSION = "3.1";

    /**
     * @see http://code.google.com/apis/maps/documentation/
     * @const string Base path to Maps CDN
     */
    //const CDN_BASE_GOOGLE = 'http://www.google.com/jsapi';
    const CDN_BASE_GOOGLE = 'http://maps.google.com/maps/api/js';
    /**
     * url for ZF4 jquery googlmaps library
     */
    const CDN_BASE_ZF4MAPS = "/zf4asset/js/GMap/jquery.googlemaps.js";
    /**
     * url for Marker Manager library
     */
    const CDN_BASE_MARKERMNG = "/zf4asset/js/GMap/markermanager.js";

    /**
     * Default map marker icon
     * use Google default icons
     */
    const ICON_DEFAULT = 0;
    /**
     * Alphabetic A-Z markers
     * Good for <27 markers as it will
     * cycle back to A if more
     */
    const ICON_ALPHABET = 1;
    /**
     * Use a single coloured pin marker
     * Set options['colour'] = a colour (purple,yellow,blue,white,green,red,black,orange,gray,brown)
     * when createing marker object to set the colour
     */
    const ICON_PIN_SINGLE = 2;
    /**
     * Use coloured pins
     * Good for <11 markers as they will
     * cycle around the colours if more
     */
    const ICON_PIN_CYCLE = 3;
    /**
     * Use a custom marker icon (set)
     * A marker will need to have an icon set defined for it
     */
    const ICON_CUSTOM = 4;

    /**
     * Database driven layer type
     * Remember to provide options
     */
    const LAYER_DB = 0;
    /**
     * Google GGeoXml layer type
     */
    const LAYER_GEOXML = 1;
    /**
     * Google GLayer layer type
     */
    const LAYER_CUSTOM = 2;
    /**
     * Google GTraffic layer type
     */
    const LAYER_TRAFFIC = 3;
    /**
     * Google Polygon layer type
     */
    const LAYER_POLYGON = 4;

    /**
     * Specify that no information window will be presented
     */
    const INFO_NONE = 0;
    /**
     * Specify that info window for markers is derived
     * from an html string passed in for location::description
     */
    const INFO_HTML = 1;
    /**
     * Specify that info window for markers is derived
     * from a dom node passed in for location::description
     */
    const INFO_DOM = 2;
    /**
     * Specify that info window for markers is derived
     * from pipe delimited html string passed in for location::description
     */
    const INFO_TABBED_HTML = 3;
    /**
     * Specify that info window for markers is derived
     * from pipe delimited dom node passed in for location::description
     */
    const INFO_TABBED_DOM = 4;
    /**
     * Global varaiable decalred to hold map objects
     */
    const MAP_GLOBAL_VAR = "_GMAP";
    /**
     * prefix for the javascript map variable
     *
     */
    const MAP_VAR_PREFIX = 'gmap_';
    /**
     * prefix for a layer variable
     *
     */
    const MAP_LAYER_PREFIX = "glayer_";
    /**
     * prefix for an info dom element
     */
    const DOM_DIV_PREFIX = "gmapInfo";
    /**
     * Map type = normal
     */
    const MAPTYPE_NORMAL = 'ROADMAP';
    /**
     * Map type = satellite
     */
    const MAPTYPE_SATELLITE = 'SATELLITE';
    /**
     * Map type = satellite
     */
    const MAPTYPE_HYBRID = 'HYBRID';
    /**
     * Map type = terrain
     */
    const MAPTYPE_TERRAIN = 'TERRAIN';
    /**
     * Map control type - Default - depends on window size
     */
    const MAPCTRL_STYLE_DEFAULT = 'DEFAULT';
    /**
     * Map control type - Drop dowm
     */
    const MAPCTRL_STYLE_DROPDOWN = 'DROPDOWN_MENU';
    /**
     * Map control type - Standard horizontal bar
     */
    const MAPCTRL_STYLE_HORIZONTAL = 'HORIZONTAL_BAR';
    /**
     * Map control position - Center bottom
     */
    const MAPCTRL_POS_BOT = 'BOTTOM';
    /**
     * Map control position - Center top
     */
    const MAPCTRL_POS_TOP = 'TOP';
    /**
     * Map control position - Left
     */
    const MAPCTRL_POS_LEFT = 'LEFT';
    /**
     * Map control position - Right
     */
    const MAPCTRL_POS_RIGHT = 'RIGHT';
    /**
     * Map control position - Bottom left
     */
    const MAPCTRL_POS_BOTLEFT = 'BOTTOM_LEFT';
    /**
     * Map control position - Bottom right
     */
    const MAPCTRL_POS_BOTRIGHT = 'BOTTOM_RIGHT';
    /**
     * Map control position - Top left
     */
    const MAPCTRL_POS_TOPLEFT = 'TOP_LEFT';
    /**
     * Map control position - Top right
     */
    const MAPCTRL_POS_TOPRIGHT = 'TOP_RIGHT';

    /**
     * Counter used to count up number of DOM information div windows being used
     *
     * @var int
     */
    public static $infoCounter = 0;

    /**
     * Google Maps enable a view instance
     *
     * @param  Zend_View_Interface $view
     * @param string $key Google Map Key
     */
    public static function enableView(Zend_View_Interface $view, $key = null) {
        if (false === $view->getPluginLoader('helper')->getPaths('ZF4_GMap_View_Helper')) {
            $view->addHelperPath('ZF4/GMap/View/Helper', 'ZF4_GMap_View_Helper');
        }
        if (!is_null($key)) {
            self::setKey($key);
        }

        $view->GMap()->enable();   //enable GMap
        $view->GMap()->setView($view);  //store the view
    }

    /**
     * Google map key - REQUIRED
     *
     * @see http://www.google.com/maps/api_signup
     * @var string
     */
    protected static $_googleMapKey = null;

    /* Get the current Google Map Key
     *
     * @return string
     */

    public static function getKey() {
        return self::$_googleMapKey;
    }

    /**
     * Set the google map key
     *
     * @param string $key
     */
    public static function setKey($key) {
        self::$_googleMapKey = $key;
    }

    /**
     * Set the Google Map Key from a file
     * File contents = 
     * $googleMapKey = "...";
     *
     * @param string $key
     * @throws ZF4_GMap_Exception if key file does not exist or is invalid
     */
    public static function setKeyFromFile($file) {
        if (file_exists($file)) {
            include($file);
            if (isset($googleMapKey)) {
                self::setKey($googleMapKey);
            } else {
                throw new ZF4_GMap_Exception("Google Key file has invalid contents", Zend_Log::ERR);
            }
        } else {
            throw new ZF4_GMap_Exception("Google Key file '{$file}' does not exist", Zend_Log::ERR);
        }
    }

    //debug state
    protected static $_debug = false;

    /**
     * set the javascript console debug state
     *
     * @param boolean $flag
     */
    public static function setDebug($flag = true) {
        self::$_debug = $flag;
    }

    /**
     * get javascript console debug state
     *
     * @return boolean
     */
    public static function getDebug() {
        return self::$_debug;
    }

    /**
     * Insert a Firebug console message if debug = true
     *
     * @param string $jquery string to insert message into
     * @param string $msg item,object or string to insert into jquery stream
     */
    public static function jsDebug(&$jquery, $msg) {
        if (self::getDebug()) {
            $jquery .= "console.debug({$msg});";
        }
    }

    /**
     * Store line seperator
     *
     * @var char
     */
    private static $_lineSep = null;

    /**
     * Gets line seperator dependent on system state
     * If stage=dev OR debug = true then return PHP_EOL
     * Else return ''
     *
     * @return char
     */
    public static function lineSeperator() {
        if (is_null(self::$_lineSep)) {
            if (Zend_Registry::get(ZF4_Defines::REGK_APPSTAGE) == ZF4_Defines::STAGE_DEV
                    || self::getDebug()) {
                self::$_lineSep = PHP_EOL;
            } else {
                self::$_lineSep = '';
            }
        }
        return self::$_lineSep;
    }

    /**
     * Caching flag
     *
     * @var boolean
     */
    private static $_useCache = false;

    /**
     * Cache timeout in seconds
     *
     * @var int
     */
    private static $_cacheTime = 7200;

    /**
     * Set caching flag
     *
     * @param bool $flag
     */
    public static function setCaching($flag = true, $cacheTime = 7200) {
        self::$_useCache = $flag;
        self::$_cacheTime = $cacheTime;
    }

    /**
     * get the caching flag state
     *
     * @return boolean true = caching is on
     */
    public static function getCaching() {
        return self::$_useCache;
    }

    /**
     * get the cache timeout (in seconds)
     *
     * @return int
     */
    public static function getCacheTime() {
        return self::$_cacheTime;
    }

}