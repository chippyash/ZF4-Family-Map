<?php
/**
 * ZF4 Library
 *
 * Google Maps integration
 *
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Marker
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
 * Defines a Map marker for the GMap interface
 *
 * Usage:
 * $map = new ZF4_GMap_Map('googleMap',$params);  //create a new map
 * $marker = new ZF4_GMap_Marker()
 * $loc = new ZF4_GMap_Location($params);			//add a location
 * $view->GMap()->addMap($map);	//add the map to map handler
 * 
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Marker
 */
class ZF4_GMap_Marker extends ZF4_Object_Virtual {

	/**See google GMarker for options **/
	public $clickable = true;
	public $draggable = false;
	public $visible = true;
	public $icon = ZF4_GMap::MARKER_DEFAULT;
	public $cursor = '';
	public $flat = false;
	
	/**
	 * Constructor
	 *
	 * @param null|int $icon Icon set to use for this marker see ZF4_GMap::MARKER_..
	 * @param null|array $options array of options to pass to marker
	 * @param boolean $noLang No language support if true, default true
	 */
	public function __construct($icon = null, $options = null, $noLang = true) {
		parent::__construct($noLang);

		//set default icon type if required
		if (is_null($icon)) {
			$icon = ZF4_GMap::MARKER_DEFAULT ;
		}
		//create the icon object to use
		$this->icon = ZF4_GMap_Icon::factory($icon,$options, $noLang);
		//clear out any icon specific options
		$options = ZF4_GMap_Icon::cleanOptions($options);

		//set options if required
		if (is_array($options)) {
			foreach ($options as $key=>$value) {
				$this->$key = $value;
			}
		}
	}

	/**
	 * Return public params suitable for jScript
	 *
	 * @return unknown
	 */
	protected function getCleanParams() {
		$params = $this->toArray(true);
		unset($params['id']);
		unset($params['icon']);
		foreach ($params as $key => &$value) {
			if (is_bool($value)) {
				$value = ($value ? 'true' : 'false');
			} elseif (is_string($value)) {
				$value = "'{$value}'";
			}
		}
		return $params;
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
		$params = $this->getCleanParams();
		$json = "{";
		foreach ($params as $key => $value) {
			$json .= "{$key}:{$value},";
		}
		$json = rtrim($json,',');
		$json .= "}";
		return $json;
	}

	/**
	 * Have we retrieved the first icon from ourself if icon is cyclical
	 *
	 * @var boolean
	 */
	private static $_firstIcon = false;

	/**
	 * Create Google API javascript to create marker
	 *
	 * @param ZF4_GMap_Map $map
	 * @param ZF4_GMap_Location $location
	 * @return string
	 */
	public function toJScript($map,$location) {
		$sep = ZF4_GMap::lineSeperator();
		//map object name
		$mapObj = ZF4_GMap::MAP_GLOBAL_VAR . "." . ZF4_GMap::MAP_VAR_PREFIX . $map->id;
		//get icon declaration
		if ($this->icon->isCyclical() && self::$_firstIcon) {
			//create a new icon in series
			$iconType = get_class($this->icon);
			$iconOpts = $this->icon->toArray(true);
			unset($iconOpts['id']);
			$icon = new $iconType($iconOpts);
		} else {
			//simply retrieve the current icon object
			$icon = $this->icon;
			self::$_firstIcon = true; //set flag for cyclical icons
		}

		//standard marker options
		$params = $this->getCleanParams();
		$markerOptions = '{'
			. "clickable:{$params['clickable']},"
			. "draggable:{$params['draggable']},"
			. "position:new google.maps.LatLng({$location->lat}, {$location->lng}, true),"
			. "visible:{$params['visible']},"
			. "map:{$mapObj}";
		//add hover over 'tooltip' title
		if ($map->labels) {
        	$markerOptions .= ",title:'{$location->id}'";
        }
        
		$markerOptions .= "}";
		$jscript = "var opts = {$markerOptions};" . $sep;
		$tmp = $icon->toJScript();
		if ($tmp != "new google.maps.MarkerImage()") {
			$jscript .= "opts.icon = " . $tmp .";" . $sep;
		}
		
		$jscript .= "var mkr = new google.maps.Marker(opts);" . $sep;
		//bind information window
		$infoTag = '';
		switch ($map->infoType) {
			case ZF4_GMap::INFO_HTML :
			case ZF4_GMap::INFO_TABBED_HTML :
			case ZF4_GMap::INFO_TABBED_DOM  :
				$infoTag = "'{$location->desc}'";
				break;
			case ZF4_GMap::INFO_DOM :
				$infoTag = "document.getElementById('" . ZF4_GMap::DOM_DIV_PREFIX . ZF4_GMap::$infoCounter . "')";
				ZF4_GMap::$infoCounter ++;
				break;
			case ZF4_GMap::INFO_NONE :
			default:
				break;
		}

		$markerName = ZF4_GMap::MAP_GLOBAL_VAR . ".markers." . ZF4_GMap::MAP_VAR_PREFIX . $map->id;

		if ($map->infoType != ZF4_GMap::INFO_NONE) {
			$jscript .= "var infowindow = new google.maps.InfoWindow({content:{$infoTag}});" . $sep;
			$jscript .= "google.maps.event.addListener(mkr, 'click', function() {infowindow.open({$mapObj},mkr);});" . $sep;
		}

		$jscript .= "{$markerName}.push(mkr);" . $sep;

		return $jscript;
	}
}