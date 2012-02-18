/*
 * jquery.gmapdraw.js
 * A Drawing plugin for google maps V3 api
 * 
 * @copyright (c) Copyright by Ashley Kitson & ZF4 Business Limited, UK 2010
 * jquery.gmapdraw.js is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @see http://zf4.biz
 * @author Ashley Kitson
 * @license GPL V3
 *
 */

(function( $ ){
	$.fn.GmapDraw = function(cmd,opts) {
		//lock to the jQuery container
		var _container = $(this);
		
	  	//plugin initial options
	  	var _options = {
	  		pointer : 'crosshair',
	  		gmapObj : null, //required - the map object
	  		debug	: true,
	  		saveFunc: null,  //required - function to call to save drawing - function(drawing)
	  		updateFunc: null, //required - function to call when new shape is added/deleted etc
	  		colourFunc: null, //optional - function that gets called to allow user to choose colour when colour button clicked - returns colour number
	  		buttons : {
		  		btnNew  : null, //optional - button id to start new shape
		  		btnClose: null, //optional - button id to close shape
		  		btnClear: null, //optional - button id to clear the drawing
		  		btnEdit : null, //optional - button id to go into edit mode
		  		btnDel  : null, //optional - button id to delete last line
		  		btnSave : null, //optional - button id to save the drawing,
		  		btnColour:null, //optional - button id to change colour of current shape
	  		},
	  		markers : {
		  		loc  : '/images/GMap/mkralpha',	//optional - location of marker icons
		  		start: 'blue_Marker%s.png',	//optional - name of marker %s is standin for Alpha letter
		  		size : new google.maps.Size(20, 34),
		  		origin: null,
				anchor: null,
				scaledSize : null
	  		},
			polyLine : {
				strokeColor 	: '#000000',
				strokeWeight	: 2,
				strokeOpacity	: 0.5
			}	
	  	};
	  	
	  	//plugin storage
	  	var _store = {
	  		ns	: 'gmapdraw', //namespace
	  		set	: function(key,value) {  //set some value
	  			var st = _container.data(this.ns);
	  			if (!st) { st = {};}
	  			st[key] = value;
	  			_container.data(this.ns,st);
	  		},
	  		get : function(key,defvalue) {  //get some value
	  			if (! defvalue) { defvalue = null;}
	  			var st = _container.data(this.ns);
	  			if (!st || st[key] == null || st[key] == 'undefined') { 
	  				return defvalue;
	  			} else {
	  				return st[key];
	  			}
	  		}
	  	}
	  	
 	
	  	//inner methods that can be called
	  	var _methods = {
	  		//PUBLIC methods
	  		init			: function(opts){ //initialise map for drawing
				this._checkmap();
			  	if (opts) { $.extend(true, _options, opts); }
			  	this._setButtons();
			  	_store.set('saveFunc',_options.saveFunc); //store the 'save' function
			  	_store.set('updateFunc',_options.updateFunc); //store the 'update' function
			  	_store.set('colourFunc',_options.colourFunc); //store the 'colour' function
			  	//set up google event listeners
				var listeners = {
					click: google.maps.event.addListener(_options.gmapObj, 'click', this._addLatLng),
					zoom_changed : google.maps.event.addListener(_options.gmapObj,'zoom_changed',this._mapZoom)
				}
    			_store.set('listeners',listeners);
    			_store.set('gmap',_options.gmapObj);
    			_store.set('markers',_options.markers);
    			_store.set('polyLine',_options.polyLine);
			  	this._setPointer(_options.pointer);
			  	this.dr_new();
	  		},
	  		
	  		reset			: function() { 	  //reset map - no drawing
	  			this._checkmap();
	  			this._setPointer('pointer');
	  			this._resetButtons();
	  			//remove all shapes from map
	  			var c = _store.get('currshape');
	  			var s = _store.get('shapepoints');
	  			for (x = c; x>=0; x--) {
	  				s[c].shape.setMap(null);
	  				if (s[c].mkr != null) s[c].mkr.setMap(null);
	  				s[c] = {};
	  			}
	  			_store.set('shapepoints',s);
	  			_store.set('currshape',-1);
	  			
	  			//remove google map event listeners
	  			var listeners = _store.get('listeners');
	  			google.maps.event.removeListener(listeners.click);
	  			google.maps.event.removeListener(listeners.zoom_changed);
	  		},
	  		
	  		dr_new			: function() {	//start a new shape
	  			this._checkmap();
	  			var c = _store.get('currshape',-1);
	  			c ++;
	  			_store.set('currshape',c);
	  			var defPolyOpts = _store.get('polyLine');
	  			var polyOpts = {
	  				path			: new google.maps.MVCArray(),
	  				strokeColor 	: defPolyOpts.strokeColor,
	  				strokeOpacity	: 1,
	  				strokeWeight	: defPolyOpts.strokeWeight,
	  				map				: _store.get('gmap')
	  			};
	  			var shape = {
	  				type : 'polyline', //every shape starts life as a poly line
	  				mkr  : null,       //initial marker
	  				shape: new google.maps.Polyline(polyOpts)	//PolyLine object
	  			};
	  			var s =_store.get('shapepoints',{});
	  			s[c] = shape;
	  			_store.set('shapepoints',s);
	  		},
	  		
	  		dr_close		: function() {
	  			//close the current shape and convert to polygon
	  			this._checkmap();
	  			var c = _store.get('currshape');
	  			var s = _store.get('shapepoints');
	  			var defPolyOpts = _store.get('polyLine');
	  			var path = s[c].shape.getPath();
	  			var point = path.getAt(0);
	  			path.push(point); //store into polyline object
	  			//remove the polyline object
	  			s[c].shape.setMap(null);
	  			//replace with polygon
	  			s[c].type = 'polygon';  //change shape type tag
	  			var polyOpts = {
	  				path			: path,
	  				strokeColor 	: defPolyOpts.strokeColor,
	  				fillColor		: defPolyOpts.strokeColor,
	  				strokeOpacity	: defPolyOpts.strokeOpacity,
	  				strokeWeight	: defPolyOpts.strokeWeight,
	  				map				: _store.get('gmap')
	  			};
	  			s[c].shape = new google.maps.Polygon(polyOpts);
	  			_store.set('shapepoints',s);	  			
	  		},
	  		
	  		dr_clear		: function() {  //clear the current shape
	  			this._checkmap();
	  			var c = _store.get('currshape');
	  			var s = _store.get('shapepoints');
	  			//remove shape from map
	  			s[c].shape.setMap(null);
	  			//remove the marker
	  			s[c].mkr.setMap(null);
	  			//decrement the shape counter
	  			c --;
	  			_store.set('currshape',c);
	  			this.dr_new(); //start new shape?
	  			this._updateClient('del',{shape:c});
	  		},
	  		
	  		dr_edit			: function() {  //set/clear line edit mode
	  			this._checkmap();
	  			var c = _store.get('currshape');
	  			var inEdit = _store.get('editmode',false);
	  			inEdit = !inEdit;
	  			_store.set('editmode',inEdit);
	  			if (inEdit) {
	  				this._updateClient('edit',{shape:c,mode:true});
	  			} else {
	  				this._updateClient('edit',{shape:c,mode:false});
	  			}
	  			alert("Edit not currently supported\nPlease draw carefully!");
	  		},
	  		
	  		dr_del			: function() { //delete last line
	  			this._checkmap();
	  			var c = _store.get('currshape');
	  			var s = _store.get('shapepoints');
	  			var path = s[c].shape.getPath().pop();
	  		},
	  		
	  		dr_save			: function() { //save the drawing
	  			this._checkmap();
	  			var func = _store.get('saveFunc');
	  			if (! func) {
	  				$.error('Save function not specified');
	  			}
	  			var drawing = _store.get('shapepoints');
	  			var save = {};
	  			var x = _store.get('currshape');
	  			for (var i = x; i>=0; i--) {
	  				if(drawing[i].type == 'polygon') {
	  					//only save polygons
	  					var a = drawing[i].shape.getPath();
	  					var b = [];
	  					//just get the coords
	  					a.forEach(function(item) {
	  						b.push([item.lat(),item.lng()]);
	  					});
	  					//shape.e.strokeColor is a hack cus google API doesn't support getOptions()
	  					save[i] = {points:b,colour:drawing[i].shape.e.strokeColor};
	  				}
	  			}
	  			var ev = func+"(save);";
	  			eval(ev);
	  		},
	  		
	  		dr_colour	: function(colour) { //change colour of current shape
	  			this._checkmap();
	  			var p = _store.get('polyLine');
	  			p.strokeColor = '#'+colour;
	  			_store.set('polyLine',p);
	  			
	  		},
	  		
	  		//PRIVATE methods
	  		_colour		: function() {
	  			this._checkmap();
	  			var func = _store.get('colourFunc');
	  			if (! func) {
	  				$.error('Colour function not specified');
	  			}

	  			var ev = func+"();";
	  			eval(ev);
	  		},
	  		
	  		_checkmap 		: function() {	  //check map is a google map
			  	if (!_container.hasClass('googleMapCanvas')) {
			  		$.error('Element is not a Google map');
			  	}
			  	return true;
	  		},
	  		
	  		_updateClient	: function(op,data) { //call client update function
	  			var func = _store.get('updateFunc');
	  			if (! func) {
	  				$.error('Update function not specified');
	  			}
	  			var ev = func+"(op,data);";
	  			eval(ev);
	  		},
	  		
	  		_setPointer 	: function(ptr) { //set the pointer display
	  			//set the pointer for the map div
	  			_store.get('gmap').setOptions({draggableCursor:ptr});
	  			
	  		},
	  		
	  		_setButtons		: function() {	  //set up any buttons with click events
	  			//for each btn, set the click method and set up storage
	  			if (_options.buttons.btnNew) {
	  				$('#'+_options.buttons.btnNew).bind('click',function(){$(_container.selector ).GmapDraw("dr_new");});
	  			}
	  			if (_options.buttons.btnClose) {
	  				$('#'+_options.buttons.btnClose).bind('click',function(){$(_container.selector ).GmapDraw("dr_close");});
	  			}
	  			if (_options.buttons.btnClear) {
	  				$('#'+_options.buttons.btnClear).bind('click',function(){$(_container.selector ).GmapDraw("dr_clear");});
	  			}
	  			if (_options.buttons.btnEdit) {
	  				$('#'+_options.buttons.btnEdit).bind('click',function(){$(_container.selector ).GmapDraw("dr_edit");});
	  			}
	  			if (_options.buttons.btnDel) {
	  				$('#'+_options.buttons.btnDel).bind('click',function(){$(_container.selector ).GmapDraw("dr_del");});
	  			}
	  			if (_options.buttons.btnSave) {
	  				$('#'+_options.buttons.btnSave).bind('click',function(){$(_container.selector ).GmapDraw("dr_save");});
	  			}
	  			if (_options.buttons.btnColour) {
	  				$('#'+_options.buttons.btnColour).bind('click',function(){$(_container.selector ).GmapDraw("_colour");});
	  			}

	  			_store.set('buttons',_options.buttons);
	  		},
	  		
	  		_resetButtons	: function() {    //clear any button click events
	  			//for each btn, clear the click method
	  			var buttons = _store.get('buttons');
	  			if (buttons.btnNew) {
	  				$('#'+buttons.btnNew).unbind('click');
	  			}
	  			if (buttons.btnClose) {
	  				$('#'+buttons.btnClose).unbind('click');
	  			}
	  			if (buttons.btnClear) {
	  				$('#'+buttons.btnClear).unbind('click');
	  			}
	  			if (buttons.btnEdit) {
	  				$('#'+buttons.btnEdit).unbind('click');
	  			}
	  			if (buttons.btnDel) {
	  				$('#'+buttons.btnDel).unbind('click');
	  			}
	  			if (buttons.btnSave) {
	  				$('#'+buttons.btnSave).unbind('click');
	  			}
	  			if (buttons.btnColour) {
	  				$('#'+buttons.btnColour).unbind('click');
	  			}
	  		},
	  		
	  		// GOOGLE MAP LISTENERS
	  		//add a point
	  		_addLatLng		: function(point) {
	  			//store information
	  			var c = _store.get('currshape');
	  			var s = _store.get('shapepoints');
	  			var path = s[c].shape.getPath();
	  			var i = path.length;
	  			path.push(point.latLng); //store into polyline object
	  			_store.set('shapepoints',s);
	  			if (i == 0) {
	  				//if first point then add marker
	  				var mkrOpt = _store.get('markers');
	  				s[c].mkr = new google.maps.Marker({
	  					clickable:false,
	  					icon:new google.maps.MarkerImage(
		  					mkrOpt.loc+ '/' + mkrOpt.start.replace('%s',String.fromCharCode(65+c)),
		  					(mkrOpt.size) ? mkrOpt.size : null,
							(mkrOpt.origin) ? mkrOpt.origin : null,
							(mkrOpt.anchor) ? mkrOpt.anchor : null,
							(mkrOpt.scaledSize) ? mkrOpt.scaledSize : null
	  					),
	  					position:point.latLng,
	  					map:_store.get('gmap'),
	  					visible:true,
	  					flat:true
	  				});
		  			_store.set('shapepoints',s);
		  			var func = _store.get('updateFunc');
		  			if (func) {
		  				var ev = func+"('new',{shape:c});";
		  				eval(ev);
		  			}
	  			} 
	  			
	  		},
	  		
	  		_mapZoom		: function() {
	  			var map = _store.get('gmap')
	  			var z = map.getZoom();
	  			_store.set('zoom',z);
	  		},
	  		
	  		//GOOGLE MAP Drawing

		
	  		_drawLine		: function(start,end) {
	  			
	  		}
	  	};
		
	  	//main program
	  	//check command and options
	  	if ((!cmd && !opts) || (cmd == null && opts)) {
	  		//no command or options - we are initializing
	  		cmd = 'init';
	  		opts = {};
	  	} else if (typeof cmd == 'object') {
	  		//just options passed in, so we are still initialising
	  		opts = cmd;
	  		cmd = 'init';
	  	}
	  	if (!opts) {
	  		opts = {}
	  	}
	    //run the required command
	    try {
		    if (_methods[cmd]) {
		    	return _methods[cmd](opts);
		    } else {
		    	$.error('Method ' + cmd + ' does not exist in jquery.gmapdraw');
		    }
	    } catch(x) {
	    	if (_options.debug) {alert(x);}
	    	throw(x);
	    }
    	
  	};
})( jQuery );
