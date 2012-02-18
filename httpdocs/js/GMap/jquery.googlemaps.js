/**
 * jquery.googlemaps v1.0.0
 *
 * Copyright David Hong 2009
 * http://davidhong.id.au/jquery/google/maps/
 * Amended by Ashley Kitson, Zinc Digital Business Systems Ltd
 * for additional functionality to support ZWare Business Framework
 * ZWare_GMap component.
 * Amendments copyyright Zinc Digital Business Systems Ltd, 2009
 *
 * Simplified Google Maps API integrated into jQuery
 *
 * SIMPLE USAGE:
 * 
 * $("google-map-canvas").googlemap({
 *     controls: false,
 *     labels: true,
 *     addresses: [
 *         {'addr':"1 ABC St, NSW Australia",'name':null,lat:null,long:null},
 *         {'addr':null,'name':'some name',lat:-3,long:234}
 *     ]
 * });
 * 
 **/
  
(function($) {
    
    // fireEvent(opts, fn, self, arg)
    //     opts:    (json) jQuery options for this plugin
    //     fn:      (function) function to run
    //     self:    (object) this
    //     arg:     (object) argument to feed to function (fn)
    //
    // note: fn should always return true on successful runs, otherwise return
    //       false
    function fireEvent(opts, fn, self, arg) {        
        if ($.isFunction(fn)) { 
            try {  
                return fn.call(self, arg);
            } catch (error) {
                if (opts.debug) {
                    alert("Error calling googlemaps." + fn + ": " + error);
                } else {
                    throw error;    
                }
                return false;
            }                     
        }
        return true;            
    }
                
    var current = null;    
  	//google map instance
    var GMap = null;

   
    function Googlemap(root, conf) {
        // current instance
        var self = this;
        if (!current) {
            current = self;
        }
        
        // internal variables
        var map;
        var geo;
        var bounds;
        var markers;
        //var index = 0;
        
        // configuration (comments show default values)
        var latitude    = conf.latitude;    // -35
        var longitude   = conf.longitude;   // 150
        var zoom        = conf.zoom;        // 4
        var controls    = conf.controls;    // true
        var labels      = conf.labels;      // true
        var html        = conf.html;        // null
        var anchor      = conf.anchor;      // null
        var addresses   = conf.addresses;   // null
        var debug       = conf.debug;       // false
        var autozoom	= conf.autozoom;	// true
        var autocenter	= conf.autocenter;	// true
        var dispPopText = conf.dispPopText; // false
        var mapType	    = conf.mapType		// normal
        var markerType  = conf.marker		// default icon = 0
        var infoType	= conf.infoType		// information window setting
        
        // methods
        $.extend(self, {
            // plugin specific
            getVersion: function() { return [1, 0, 0]; },
            getRoot: function() { return root; },
            
            // google maps specific
            getMap: function() { return map; },
            getGeo: function() { return geo; },
            getAddresses: function() { return addresses; },
            getBounds: function() { return bounds; },
            //getIndex: function() { return index; },
            getMarkers: function() { return markers; },
            addOverlay: function(id) { map.addOverlay(id); },
            
            // api
            isBrowserCompatible: function() {
                if ($.isFunction(GBrowserIsCompatible))
                    return GBrowserIsCompatible();
                
                return false;
            },
            initialise: function() {
                self.trace("initialising: " + this);
                if (self.isBrowserCompatible()) {
                    map         = map || new GMap2(document.getElementById($(root)[0].id));
                    geo         = geo || new GClientGeocoder();
                    bounds      = bounds || new GLatLngBounds();
                    markers     = markers || new Array();
                    
                    GMap = map;
                    
                    //set initial map state
                    self.trace("map types avail are: ");
                    self.trace(map.getMapTypes);
                    if (mapType == 'normal') {
                    	mapType = G_NORMAL_MAP;
                    } else if (mapType == 'satellite') {
                    	mapType = G_SATELLITE_MAP;                    	
                    } else if (mapType == 'hybrid') {
                    	mapType = G_HYBRID_MAP;
                    } else {
                    	mapType = G_PHYSICAL_MAP;
                    }
                    	map.setMapType(mapType);
                    
                    GEvent.addListener(map, "load", function() {
                        self.trace("google map loaded!");
                    });
                    
                    // set the map center
					self.setcenter();
					                    
                    // mark addresses on the map
                    if (addresses) {
                        if (addresses.length > 0) {
                            var i = 0;
                            while (i < addresses.length) {
                                self.geocode(i++);
                            }
                        }
                    }
                    
                    // add controls
                    if (controls) {
                        map.setUIToDefault();
                    }
                }
            },

            //set the center of the map to the first location if possible
            //otherwise use the default latitude/longitude to center to
            setcenter: function() {
            	//self.trace("autocenter = " + autocenter);
            	//self.trace("addresses.length = " + addresses.length);
            	if (autocenter && addresses && addresses.length > 0) {
            		//set up the geo decoder
            		geo = (geo == null) ? new GClientGeocoder() : geo;
            		
            		//if the locations doesn't have lat & long then get it
            		if (addresses[0]['lat'] == null && addresses[0]['long'] == null) {
	            		geo.getLocations(addresses[0], function(response) {
	            			var statuscode = response.Status.code;
	            			if (statuscode == G_GEO_SUCCESS) {
	                           	latitude = response.Placemark[0].Point.coordinates[1];
	                           	longitude = response.Placemark[0].Point.coordinates[0];
	                           	//save back to address
	                           	addresses[0]['lat'] = latitude;
	                           	addresses[0]['long'] = longitude;
	            			}
	            		});
            		} else {
            			//use the lat and long already in the address
            			latitude = addresses[0]['lat'];
            			longitude = addresses[0]['long'];
            		}
            	}
            	self.trace("Center on Lat " + latitude + ", Long " + longitude);
                map.setCenter(new GLatLng(latitude, longitude), zoom);
            },
            
            // Work out coords for addresses
            // geocode(index, address, html, anchor) :
            //     index:     (number) index of the marker (obsolete when label == false)
            //     address:   (string) human readable address to query
            //     html:      [array] what to display on marker's "click" event
            //     anchor:    [array] simulate marker's "click" event outside the map via a link
            geocode: function(index) {
            	
                //create markers array if doesn't exist
                markers = markers || new Array();
                if (addresses && index >= 0) {
                    self.trace("processing address: [" + addresses[index]['name'] + "] (" + index + ")");
                    
                    // safer way of geocoding - avoids G_GEO_TOO_MANY_QUERIES
                    var thisAddress = addresses[index];
                    if (thisAddress['lat'] == null && thisAddress['long'] == null) {
                    	var pos = self.getGeoInfo(thisAddress, index);
                    	if (pos != null) {
                    		thisAddress['lat'] = pos.lat;
                    		thisAddress['long'] = pos.lng;
                    	} else {
                    		return;  //can't process - turn debug on
                    	}
                    } else {
                    	var pos = {'lat':thisAddress['lat'],
                    			   'lng':thisAddress['long']};
                    }
                    if (thisAddress['lat'] == null && thisAddress['long'] == null) {
                    	self.trace("Unable to get coords for " + thisAddress.name);
                    } else {
                    	//create the marker
                    	var point = new GLatLng(pos.lat, pos.lng, true);
                        // extend bounds
                        bounds = bounds || new GLatLngBounds();
                        bounds.extend(point); self.trace("bounds extended");
                        
                        // marker
                        var marker = self.createMarker(index, point);
                        self.trace(marker); self.trace("marker created");
                        
                        // marker events
						GEvent.addListener(marker, "dblclick", function() {
							zoom = 15;
							map.setCenter(marker.getLatLng(), zoom);
						});
                        
                        // add marker to array and display
                        markers[index] = marker;
                        map.addOverlay(marker);
                        
                        //add infoWindow details
                        self.addInfoDetail(marker, index);
                        
                        // onMarkerLoaded
        				if (fireEvent(conf, self.onMarkerLoaded, self, index) === false) {
        					return self;
        				}
                    	
                    }
                }
            },
            
            //add info window detail for a marker so that when it is clicked the
            //information is displayed
            // marker - the marker object
            // index  - index into current address
            addInfoDetail: function (marker, index) {
            	if (infoType == 0) {
            		return; //no info to be shown
            	} else if (infoType == 1) {
            		//html string
            		marker.bindInfoWindowHtml(addresses[index].desc);
            	} else if (infoType == 2) {
            		//DOM node
            		marker.bindInfoWindow(addresses[index].desc);
            	} else if (infoType == 3) {
            		//DOM node
            		marker.bindInfoWindowTabsHtml(addresses[index].desc);
            	} else if (infoType == 4) {
            		//DOM node
            		marker.bindInfoWindowTabs(addresses[index].desc);
            	}
            },
            
            //get lat and long info for an address
            //  $thisAddress an item from addresses array
            //  $index address index - only used for retries
            getGeoInfo: function(thisAddress, index) {
            	//create google geocoder if doesn't exist
                geo = (geo == null) ? new GClientGeocoder() : geo;
                var pos = null; //return parameter
                geo.getLocations(thisAddress.addr, function(response) {                        
                	var statuscode = response.Status.code;
                    
                	if (statuscode == G_GEO_SUCCESS) {
                        // success!
                        self.trace(response.Placemark);
                        pos = {'lat':response.Placemark[0].Point.coordinates[1],
                               'lng':response.Placemark[0].Point.coordinates[0]};
                        return pos;
                	} else {
                        if (statuscode == G_GEO_TOO_MANY_QUERIES) {
                            // retry again after a short while
                            var delay = 600;
                            self.trace("index " + index + " will begin retry in " + delay + "ms")
                            setTimeout(function() {
                                self.geocode(index);
                            }, delay);
                        } else {
                            self.trace("unknown error code: " + statuscode);
                            return null;
                        }
                	}
                });
            },
            
            // onMarkerLoaded(index)
            //     internal function : DO NOT MODIFY
            onMarkerLoaded: function(index) {
                // set map bounds and zoom level to optimal level so all marker can fit
                return self.optimiseZoomLevel();
            },
            
            // optimiseZoomLevel()
            //  can be switched off by setting autozoom config to false
            optimiseZoomLevel: function(index) {
                if (bounds && (addresses.length == markers.length)) {
					zoom = map.getBoundsZoomLevel(bounds);
                    if (autozoom) {
						map.setZoom(zoom);
                    }
                    map.setCenter(bounds.getCenter());
                }
                
                return true;
            },
            
            // createMarker(index, point)
            //     index:    (number) index of the marker (also used to generate a letter)
            //     point:    (GLatLng) latitude and longitude of the marker
            createMarker: function(index, point) {
            	var markerOptions = { 
                    bouncy: markerType.bouncy,
                    dragCrossMove: markerType.dragCrossMove,
                    clickable: markerType.clickable,
                    draggable: markerType.draggable,
                    bounceGravity: markerType.bounceGravity,
                    autoPan: markerType.autoPan,
                    hide: markerType.hide
                };
                if (labels) {
                	markerOptions.title = addresses[index].name;
                }
                if (markerType.zIndexProcess != '') {
                	markerOptions.zIndexProcess = markerType.zIndexProcess;
                }
                if (markerType == 0 || markerType.icon == 0) {
                	var ret = self.createDefaultMarker(index, point, markerOptions);
                } else if (markerType.icon == 1) {
                	var ret = self.createAlphaMarker(index, point, markerOptions);
                } else if (markerType.icon == 2) {
                	var ret = self.createPinMarker(index, point, markerOptions);
                } else if (markerType.icon == 3) {
                	var ret = self.createPinCycleMarker(index, point, markerOptions);
                }
                
                return ret;
            },
            
            createDefaultMarker: function(index,point,markerOptions) {
            	return new GMarker(point, markerOptions);
            },
            
            createPinMarker: function(index,point,markerOptions) {
                var baseIcon = new GIcon(G_DEFAULT_ICON);
                baseIcon.shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png";
                baseIcon.iconSize = new GSize(20, 34);
                baseIcon.shadowSize = new GSize(37, 34);
                baseIcon.iconAnchor = new GPoint(9, 34);
                baseIcon.infoWindowAnchor = new GPoint(9, 2);
                baseIcon.image = "http://labs.google.com/ridefinder/images/mm_20_"+ markerType.colour +".png"
                markerOptions.icon = new GIcon(baseIcon);
            	return new GMarker(point, markerOptions);
            },
            
            createPinCycleMarker: function(index,point,markerOptions) {
                var baseIcon = new GIcon(G_DEFAULT_ICON);
                var colours = ['purple','yellow','blue','white','green','red','black','orange','gray','brown'];
                baseIcon.shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png";
                baseIcon.iconSize = new GSize(20, 34);
                baseIcon.shadowSize = new GSize(37, 34);
                baseIcon.iconAnchor = new GPoint(9, 34);
                baseIcon.infoWindowAnchor = new GPoint(9, 2);
                var colour = colours[index % 10];
                baseIcon.image = "http://labs.google.com/ridefinder/images/mm_20_"+ colour +".png"
                markerOptions.icon = new GIcon(baseIcon);
            	return new GMarker(point, markerOptions);
            },
            
            createAlphaMarker: function(index, point,markerOptions) {
            	// create an alpha icon for all of our markers that specifies the
                // shadow, icon dimensions, etc.
                var baseIcon = new GIcon(G_DEFAULT_ICON);
                baseIcon.shadow = "http://www.google.com/mapfiles/shadow50.png";
                baseIcon.iconSize = new GSize(20, 34);
                baseIcon.shadowSize = new GSize(37, 34);
                baseIcon.iconAnchor = new GPoint(9, 34);
                baseIcon.infoWindowAnchor = new GPoint(9, 2);
                
                // lettered marker which starts at "A" and wraps at "Z"
                var range = "Z".charCodeAt(0) - "A".charCodeAt(0) + 1;
                var letter = String.fromCharCode("A".charCodeAt(0) + (index % range));
                var letteredIcon = new GIcon(baseIcon);
                letteredIcon.image = "http://www.google.com/mapfiles/marker" + letter + ".png";
                
                markerOptions.icon = letteredIcon;
                
                return new GMarker(point, markerOptions);
            },
            
            // trace(arg, [args...]) : print everything in the arguments array
            trace: function() {
                if (!debug) return;
                
                var caller = arguments.caller || "self";
                for (i = 0; i < arguments.length; i++) {
                    var argument = arguments[i]; // print object as it is
                    var line = argument;
                    try {
                        // Firefox, Safari, Opera
                        console.debug(line);
                    } catch (error) {
                        // fails gracefully on IE, Chrome
                        alert(line);
                    }
                }
            }
            
            
        });
        
        function load() {
            self.initialise();
            return self;
        }
        
		load();

    }

   
    // jQuery plugin implementation
	jQuery.prototype.googlemap = function(conf) {
        // already constructed --> return API
        var api = this.eq(typeof conf == 'number' ? conf : 0).data("googlemap");
        if (api) { return api; }	
        
        var opts = {
            latitude: 0,		//latitude to center map on
            longitude: 0,		//longitude to center map on
            zoom: 4,			//zoom level if autozoom = false
            labels: true,		//display labels on markers
            controls: true, 	//display map controls (google default set)
            html: null,			//?
            anchor: null,		//?
            addresses: null,	//list of addresses to place markers for
            debug: false,		//output debug trace
            autozoom: true,		//zoom map to fit all address points
            autocenter: true,	//auto centre map on first address else use supplied lat and long
            dispPopText: false,	//display name information in addresses on markers
            mapType: 'normal',	//initial map type
            marker: 0,  		//marker icon type
            infoType: 0,		//information window type default = none
            overlays: []		//array of overlays to add to map
        };
        
        $.extend(opts, conf);		
		
		this.each(function() {
			var el = new Googlemap($(this), opts);
			$(this).data("googlemap", el);	
		});
		
		//return the google map instance, not the jQuery object
		return GMap;
    };
    
})(jQuery);

