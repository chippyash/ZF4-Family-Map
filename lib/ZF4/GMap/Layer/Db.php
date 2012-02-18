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
 * Defines a Map Layer derived from a database table for the GMap interface 
 *
 * Corresponds to ZF4_GMap::LAYER_DB
 * 
 * @category 	ZF4
 * @package  	GMap
 * @subpackage  Layer
 */
class ZF4_GMap_Layer_Db 
	extends ZF4_GMap_Layer_Custom {

	public $options = array();
	
	/**
	 * Constructor
	 * 
	 * $options array() contains
	 * 		'table'=>name of table in database to use
	 * 		'columns'=>array(
	 * 			'name' => name of column containing name info
	 * 			'lat'  => name of column containing latitude info
	 * 			'lng'  => name of column containing longitude info
	 * 			'info' => name of column containing infoWindow info
	 * 			)
	 * 		'where' => where statement to filter data
	 * 		'view'=> the view object
	 * 		'minLevel' => minimum zoom level to view markers at default = 6
	 * 		'maxLevel' => maximum zoom level to view markers at default = null
	 * 
	 * The 'columns' array can contain other column name pairs if required by
	 * a dom layout template. The 'id' column is always retrieved.
	 * 
	 * @param string $name  Layer name
	 * @param ZF4_GMap_Map $map The map that this layer is attached to
	 * @param ZF4_GMap_Icon_Abstract $icon Default icon pin to use - if null will use the one set on the map
	 * @param array $options
	 * @param boolean $noLang if true do not use language support - default = true
	 */
	public function __construct($name, ZF4_Gmap_Map $map, ZF4_GMap_Abstract_Icon $icon = null, array $options = array(), $noLang = true) {
		parent::__construct($name, $map, $icon, $options, $noLang);
		if (!isset($options['minLevel'])) $options['minLevel'] = 6;
		if (!isset($options['maxLevel'])) $options['maxLevel'] = 'null';
		//set up public attributes
		$this->options = $options;
	}
	
	/**
	 * data returned from database
	 *
	 * @var array
	 */
	protected $_data = null;
	
	/**
	 * Get the data from the database
	 * Store in local var to avoid repeated fetches
	 * 
	 * Will retrieve all columns specified in the $this->options['columns']
	 * array
	 *
	 * @return array of records
	 */
	protected function getDBData() {
		if (is_null($this->_data)) {
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$table = $this->options['table'];
			$cols = array('id');
			foreach ($this->options['columns'] as $key => $name) {
				$cols[] = "{$name} as {$key}";
			}
			
			$select = $db->select()
					->from($table, $cols);
			if (!empty($this->options['where'])) {
				$select->where($this->options['where']);
			}
			$this->_data = $db->fetchAssoc($select);
		}
		
		return $this->_data;
	}
	
		/**
	 * Render the javascript to add this layer to the output
	 *
	 * @param string $mapName name of map that layers are being added on
	 * @return string javascript
	 */
	protected function _toJscriptThis($mapName) {
		$layer = array(
			'id' => $this->id,
			'hidden' => $this->_hideOnStart,
			'type' => 'custom',
			'map' => $mapName,
			'defIcon' => $this->_defIcon->toArray(),
			'locations' => array()
		);
		
		$locations = $this->getDBData();
		if (count($locations) > 0) {
			foreach ($locations as $location) {
				$layer['locations'][] = array(
					'id'=>$location['name'],
					'lat'=>$location['lat'],
					'lng'=>$location['lng'],
					'info'=>$location['info'],
					'Icon'=>$layer['defIcon']
				);
			}
		}
		return Zend_Json::encode($layer);
	}
	
	/**
	 * render the javascript required to add overlay to a map
	 * 
	 * @param ZF4_GMap_Map $map Handle to the map that the layer is being attached to
	 * @param ZF4_Gmap_Marker $marker Handle to marker to use for layer.  Db layer requires this
	 */
	/*
	public function toJScript(ZF4_GMap_Map $map, ZF4_Gmap_Marker $marker = null) {
		$sep = ZF4_GMap::lineSeperator();
		$layerNum = ZF4_GMap_Layer::$layerNum;
		ZF4_GMap_Layer::$layerNum ++;
		$layName = ZF4_GMap::MAP_GLOBAL_VAR . "." .ZF4_GMap::MAP_LAYER_PREFIX . $layerNum;
		$GOverlayName = "db" . ZF4_GMap::MAP_LAYER_PREFIX . $layerNum;
		
		//create supporting functions
		$dbData = $this->getDBData();
		$data = "[";
		foreach ($dbData as $row) {
			$data .= json_encode($row) . ",";
		}
		$data = rtrim($data,",");
		$data .= "]";
		$mapName = ZF4_GMap::MAP_VAR_PREFIX . $map->id;
		$markerName = ZF4_GMap::MAP_GLOBAL_VAR . ".markers." . ZF4_GMap::MAP_VAR_PREFIX . $map->id;
		$mapObj = ZF4_GMap::MAP_GLOBAL_VAR . "." . $mapName;
		//$marker = $map->getMarker();
		$markerBase = $marker->toJson();
		$minLevel = $this->options['minLevel'];
		$maxLevel = $this->options['maxLevel'];
		if ($marker->icon->isCyclical()) {
			//create a set of icons to display
			//  get first icon stored in marker object
			$icons = '[';
			$icons .= $marker->icon->toJScript() .",";
			//prepare to create more icons
			$iconType = get_class($marker->icon);
			$iconOpts = $marker->icon->toArray(true);
			unset($iconOpts['id']);
			//create additional icons
			$x = count($dbData);
			for ($i = 1; $i<$x; $i++) {
				$iconObj = new $iconType($iconOpts);
				$icons .= $iconObj->toJScript() . ",";
			}
			$icons = rtrim($icons,',') . "]";
			$iconMake = "icons[i]";
		} else {
			//create a single icon
			$icons = $marker->icon->toJScript();
			$iconMake = "icons";
		}
		if ($map->autozoom) {
			$boundScript = ZF4_GMap::MAP_GLOBAL_VAR .".bounds.extend(pos);";
		} else {
			$boundScript = "";
		}
		//set info rendering bind string
		$infoCnt = 0;  //default counter - only required for ZF4_GMap::INFO_DOM
		
		switch ($map->infoType) {
			case ZF4_GMap::INFO_HTML :
//				$bindStr = "marker.bindInfoWindowHtml(dataDb[i].info);";
				$bindStr = "marker.setContent(dataDb[i].info);";
				break;
			case ZF4_GMap::INFO_DOM :
//				$bindStr = "marker.bindInfoWindow(document.getElementById('" . ZF4_GMap::DOM_DIV_PREFIX  . "' + (ic + i)));";
				$bindStr = "marker.setContent(document.getElementById('" . ZF4_GMap::DOM_DIV_PREFIX  . "' + (ic + i)));";
				$infoCnt = ZF4_GMap::$infoCounter;
				ZF4_GMap::$infoCounter += count($dbData);
				break;
			case ZF4_GMap::INFO_TABBED_HTML :
//				$bindStr = "marker.bindInfoWindowTabsHtml(dataDb[i].info);";
				$bindStr = "marker.setContent(dataDb[i].info);";
				break;
			case ZF4_GMap::INFO_TABBED_DOM  :
//				$bindStr = "marker.bindInfoWindowTabs(dataDb[i].info);";
				$bindStr = "marker.setContent(dataDb[i].info);";
				break;
			case ZF4_GMap::INFO_NONE :
				$bindStr = "";
			default:
				break;
		}
		
		$jscript = <<<EOT
function {$GOverlayName}() {
	var dataDb = {$data};
	var dataLen = dataDb.length;
	var icons = {$icons};
	var ic = {$infoCnt};
	_GMAP.markers.{$mapName} = _GMAP.markers.{$mapName} || [];
	_GMAP.markers.{$GOverlayName} = _GMAP.markers.{$GOverlayName} || [];
	if (_GMAP.markers.{$GOverlayName}.length == 0) {
		for (var i = 0; i < dataLen; ++i) {
			var lat = dataDb[i].lat;
			var lng = dataDb[i].lng;
//			var pos = new GLatLng(dataDb[i].lat,dataDb[i].lng, true);
			var pos = new google.maps.LatLng(dataDb[i].lat,dataDb[i].lng, true);
			var markerOptions = {$markerBase};
			{$boundScript}
			markerOptions.title = dataDb[i].name;
			markerOptions.icon = {$iconMake};
//			var marker = new GMarker(pos, markerOptions);
			var marker = new google.maps.Marker(pos, markerOptions);
			{$bindStr}
			_GMAP.markers.{$GOverlayName}.push(marker);
			{$markerName}.push(marker);
		}
	}
	{$layName}.addMarkers(_GMAP.markers.{$GOverlayName},{$minLevel},{$maxLevel});
	{$layName}.refresh();
}
EOT;
		$view = $this->options['view'];
		$view->GMap()->addJavascript($jscript);
	
		//create onload script
		$script = "{$layName} = new MarkerManager(_GMAP.{$mapName});" . $sep;
		if (!$this->options['hidden']) $script .= "{$GOverlayName}();" . $sep;
		if ($map->autozoom) {
			$script .= "{$mapObj}.setZoom({$mapObj}.getBoundsZoomLevel(" . ZF4_GMap::MAP_GLOBAL_VAR . ".bounds));" . $sep;
			$script .= "{$mapObj}.setCenter(" . ZF4_GMap::MAP_GLOBAL_VAR . ".bounds.getCenter());" . $sep;
		}
		return $script;
	}
	*/
	/**
	 * Render the snippet of javerscript to create an info window div
	 *
	 * @param int $startNode starting node for this layer
	 * @param ZF4_GMap_Info_Template $template template object to use
	 */
	/*
	public function renderInfoDom(&$startNode, ZF4_GMap_Info_Template $template) {
		$sep = ZF4_GMap::lineSeperator();
		$dbData = $this->getDBData();
		$divHtml = $sep;
		foreach ($dbData as $row) {
			$row['domName']  = ZF4_GMap::DOM_DIV_PREFIX . $startNode;
			$row['node'] = $startNode;
			$divHtml .= $template->render($row) . $sep;
			$startNode ++;
		}
		return $divHtml;
	}
	*/
}