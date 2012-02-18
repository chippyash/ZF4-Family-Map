/**
 * ZWare Google Maps Javascript Support
 * (c) Copyright ZWare Limited, UK 2010
 * @author Ashley Kitson
 *
 * @todo convert to jQuery syntax
 * This assumes that the _GMAP var has been set in the header by the ZWare GMap system
 */
/**
 * Enable all non hidden layers for a map
 * Usually only called from the header script
 */
function GMAPEnableLayers(map) {
	var layers = _GMAP.layers[map];
	//initiliase the markers store
	_GMAP.markers = _GMAP.markers || {};
	for (var i in layers) {
		GMAPInitLayer(layers[i],i);
		if (!layers[i].hidden) {
			GMAPShowLayer(layers[i].id);
		}
	}
}
/**
 * Initialise a layer
 * you need to call this before calling GMAPShowLayer(layerName)
 * 
 * @returns layerName
 */
function GMAPInitLayer(layer,lId) {
	_GMAP.markers[layer.id] = {};
	_GMAP.markers[layer.id].map = eval('_GMAP.'+layer.map);
	_GMAP.markers[layer.id].id = lId;
	_GMAP.markers[layer.id].mapName = layer.map;
	switch (layer.type) {
		case 'custom':
			return _GMapInitLayerCustom(layer);
			break;
		case 'polygon':
			return _GMapInitLayerPolygon(layer);
			break;
		default:
			$.error('No Layer type');
	}
}
//initialise a custom layer
function _GMapInitLayerCustom(layer) {
	//var defIcon = layer.defIcon;
	_GMAP.markers[layer.id].mkrs = [];
	for (var i in layer.locations) {
		var mkr = GMAPCreateMarker(layer.locations[i], null);
		//store marker on stack so it can be cleared out
		_GMAP.markers[layer.id].mkrs.push(mkr);	
	}
	return layer.id;
}
//initialise a polygon layer
function _GMapInitLayerPolygon(layer) {
	var paths;
	var colour;
	var points;
	var polygons = [];
	for (var x in layer.coords) {
		if (layer.coords[x].points) {
			points = layer.coords[x].points;
			colour = layer.coords[x].colour;
		} else {
			colour = layer.colour;
			points = layer.coords[x];
		}
		paths = [];
		points.forEach(function(point){paths.push(new google.maps.LatLng(point[0],point[1]));});
		var polyopts = {
			fillColor : colour,
			fillOpacity : layer.opacity,
			strokeColor : colour,
			strokeOpacity : layer.opacity,
			strokeWeight : 2,
			paths : paths
		};
		polygons.push(new google.maps.Polygon(polyopts));
	}
	_GMAP.markers[layer.id].polygon = polygons;
	return layer.id;
}

/** get a layer id given its name **/
function GMAPGetLayerId(layerName,mapName) {
	return _GMAP.markers[layerName].id;
}
/** Return the layer definition given it's id **/
function GMAPGetLayerDefinition(lId,mapName) {
	var layers = eval('_GMAP.layers.'+mapName);
	return layers[lId];
}
/** Destroy a layer **/
function GMAPDestroyLayer(layerName) {
	GMAPHideLayer(layerName);
	var oldMap = _GMAP.markers[layerName].map;
	delete _GMAP.markers[layerName];
	_GMAP.markers[layerName] = _GMAP.markers[layerName] || {};
	_GMAP.markers[layerName].mkrs = [];
	_GMAP.markers[layerName].map = oldMap;
	
}
/** Display a layer **/
function GMAPShowLayer(layerName) {
	var layerDef = GMAPGetLayerDefinition(_GMAP.markers[layerName].id,_GMAP.markers[layerName].mapName);
	switch (layerDef.type) {
		case 'custom':
			_GMAPShowLayerCustom(layerName);
			break;
		case 'polygon':
			_GMAPShowLayerPolygon(layerName);
			break;
		default:
			break;
	}
}
//show a custom layer
function _GMAPShowLayerCustom(layerName) {
	var markers = _GMAP.markers[layerName].mkrs;
	var map = _GMAP.markers[layerName].map;
	for (var i in markers) { 
		markers[i].setMap(map);
	}	
}
//show a polygon layer
function _GMAPShowLayerPolygon(layerName) {
	//_GMAP.markers[layerName].polygon.setMap(_GMAP.markers[layerName].map);
	_GMAP.markers[layerName].polygon.forEach(function(item){item.setMap(_GMAP.markers[layerName].map);});
}

/** Hide a layer **/
function GMAPHideLayer(layerName) {
	var layerDef = GMAPGetLayerDefinition(_GMAP.markers[layerName].id,_GMAP.markers[layerName].mapName);
		switch (layerDef.type) {
			case 'custom':
				_GMAPHideLayerCustom(layerName);
				break;
			case 'polygon':
				_GMAPHideLayerPolygon(layerName);
				break;
			default:
				break;
		}	
}
//hide a custom layer
function _GMAPHideLayerCustom(layerName) {
	var markers = _GMAP.markers[layerName].mkrs;
	for (var i in markers) { 
		markers[i].setMap(null);
	}
}
//hide a polygon layer
function _GMAPHideLayerPolygon(layerName) {
	//_GMAP.markers[layerName].polygon.setMap(null);
	_GMAP.markers[layerName].polygon.forEach(function(item){item.setMap(null);});
}

/** Refresh a layer with new markers **/
function GMAPRefreshLayer(layerName,mapName,locs) {
	GMAPDestroyLayer(layerName);
	//grab the default icon from the layer in case it's needed
	var layerDef = GMAPGetLayerDefinition(GMAPGetLayerId(layerName,mapName),mapName);
	var defIcon = layerDef.defIcon;
	var map = eval('_GMAP.'+mapName);
	//add new markers
	for (var i in locs) {
		if (locs[i].Icon == 'undefined' || locs[i].Icon == null) {
			locs[i].Icon = defIcon;
		}
		var mkr = GMAPCreateMarker(locs[i], null);
		//store marker on stack so it can be shown/hidden
		_GMAP.markers[layerName].mkrs.push(mkr);
	}
	GMAPShowLayer(layerName);
}
/** Save the current map position, bounds and zoom **/
function GMAPSavePosition(mapName) {
	var map = eval('_GMAP.'+mapName);
	map.saved = map.saved || {};
	map.saved.center = map.getCenter();
	map.saved.zoom = map.getZoom();
	map.saved.bounds = map.getBounds();
}
/** Restore map to previous saved position **/
function GMAPRestorePosition(mapName) {
	var map = eval('_GMAP.'+mapName);
	if (map.saved == 'undefined') return;
	map.setCenter(map.saved.center);
	map.setZoom(map.saved.zoom);
	map.fitBounds(map.saved.bounds);
}
/** 
 * Create and return a MarkerImage 
 * 
 * @param object icon ZWare.GMap.Icon description object
 * @return google.maps.MarkerImage
 **/
function GMAPCreateMarkerImage(icon) {
	if (icon.anchor != 'undefined' && icon.anchor != null) {
		icon.anchor = new google.maps.Point(icon.anchor[0],icon.anchor[1]);
	} else {
		icon.anchor = null;
	}
	if (icon.origin != 'undefined' && icon.origin != null) {
		icon.origin = new google.maps.Point(icon.origin[0],icon.origin[1]);
	} else {
		icon.origin = null;
	}
	if (icon.size != 'undefined' && icon.size != null) {
		icon.size = new google.maps.Size(icon.size[0],icon.size[1]);
	} else {
		icon.size = null;
	}
	if (icon.scaledSize != 'undefined' && icon.scaledSize != null) {
		icon.scaledSize = new google.maps.Size(icon.scaledSize[0],icon.scaledSize[1]);
	} else {
		icon.scaledSize = null;
	}
	return new google.maps.MarkerImage(icon.url,icon.size,icon.origin,icon.anchor,icon.scaledSize);
}

/**
 * Create a marker shape
 * @todo
 */
function GMAPCreateMarkerShape(shape) {
	return null;
}
/**
 * Create and return a google.maps.Marker
 *
 * @param ZWare.GMap.Location loc Z object
 * @param google.maps.Map map Map to put marker on - can be null
 *
 * @return google.maps.Marker
 */
function GMAPCreateMarker(loc, map) {
	var opts = {};
	opts.position = new google.maps.LatLng(loc.lat,loc.lng);
	opts.map = map;
	if (loc.defIcon !== 'undefined' && loc.defIcon != null) {
		var defIcon = GMAPCreateMarkerImage(loc.defIcon);
	} else {
		var defIcon = null;
	}
	if (loc.Icon != 'undefined' && loc.Icon != null) {
		opts.icon = GMAPCreateMarkerImage(loc.Icon);
	} else {
		if (loc.defIcon !== 'undefined' && loc.defIcon != null) {
			opts.icon = defIcon;
		}
	}
	if (loc.shadow != 'undefined' && loc.shadow != null) {
		opts.shadow = GMAPCreateMarkerImage(loc.shadow);
	}
	if (loc.title == 'undefined' || loc.title == null) {
		if (loc.id != 'undefined' && loc.id != null) {
			opts.title = loc.id;
		} else {
			opts.title = 'Undefined';
		}
	} else {
		opts.title = loc.title;
	}
	if (loc.flat != 'undefined' && loc.flat != null) {
		opts.flat = loc.flat;
	}
	if (loc.clickable != 'undefined' && loc.clickable != null) {
		opts.clickable = loc.clickable;
	}
	if (loc.draggable != 'undefined' && loc.draggable != null) {
		opts.draggable = loc.draggable;
	}
	if (loc.visible != 'undefined' && loc.visible != null) {
		opts.visible = loc.visible;
	}
	/* @todo
	if (loc.shape != 'undefined' && loc.shape != null) {
		opts.shape = GMAPCreateMarkerShape(loc.shape);
	}
	*/
	if (loc.zIndex != 'undefined' && loc.zIndex != null) {
		opts.zIndex = loc.zIndex;
	}
	if (loc.cursor != 'undefined' && loc.cursor != null && loc.cursor != '') {
		opts.cursor = loc.cursor;
	}

	return new google.maps.Marker(opts);
}