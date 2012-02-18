/* js for map index page */
var _dlgSaveQuery;
var _dlgError;
var _dlgDrawSave;
var _dlgDrawColour;
var _dlgOverlay;
var _ovlExtra = [];
$(document).ready(function(){
	setGoButton('start');
	$('.chkAll').each(function(idx,ele){setCheckAll(ele)});
	//set titles for overlay buttons
	$('div#panelCtrl img.mapOvlBtn').each(
		function() {
			$(this).attr('title','Switch on '+$(this).attr('alt'));
		}
	);
	$('#mapCtrlDraw').attr('title','Switch on '+$('#mapCtrlDraw').attr('alt'));
	//set up accordian for left panel
	$('#panelLeft').accordion();
	$('h1.ui-accordion-header').unbind(); //don't allow user to click headers
	//set up dialogs
	_dlgSaveQuery = $('#uiSaveQuery').dialog({autoOpen:false,modal:true,buttons:{"Save":function(){_saveQuery(this);}}});
	_dlgError = $('#uiError').dialog({autoOpen:false,modal:true,resizeable:false,buttons:{"Ok":function(){$(this).dialog('close');}}})
	_dlgOverlay = $('#uiOverlay').dialog({autoOpen:false,modal:true,resizeable:false,title:'Toggle overlay display',buttons:{"Toggle":function(){_toggleOverlay(this);}}})
	if ($('span#canDraw').html() == '1') {
		_dlgDrawSave = $('#uiDrawSave').dialog({autoOpen:false,modal:true,title:'Save Layer',buttons:{"Save":function(){_drawSave(this,'save');}}});
		$('span#canDraw').remove();
		$('.drawBtn').fadeTo(1,0.6).hover(
			function(){$(this).fadeTo(200,1);},
			function(){$(this).fadeTo(200,0.6);}
		);
		//remove when testing is done
		//$('#panelLeft').accordion('activate',1);
		_dlgDrawColour = $('#uiColour').dialog({autoOpen:false,modal:true,buttons:{"Set":function(){_colourSend(this);}}});
		//set up colour picker
		$('select[name="colPick"]').colourPicker({ico:'/images/jquery.colourPicker.gif',title: 'Select a colour from the list'});
	} else {
		$('#uiDrawSave').remove();
	}
	//set the icon shadow
	_popShadow = new google.maps.MarkerImage('/images/GMap/numeric/shadow.png',
		new google.maps.Size(51.0,37.0),
		new google.maps.Point(0,0),
		new google.maps.Point(17,37.0)
	);
		
});

//display an error message
function dlgError(msg) {
	$('#uiError p.#errMsg').html(msg);
	_dlgError.dialog('open');
}

//query button state manipulation
function setGoButton(state) {
	if (state =='start') {
		$('#goBtn').hover(
			function(){$(this).fadeTo(500,1);},
			function(){$(this).fadeTo(500,0.6);}
		);
		$('#goBtn').fadeTo(500,0.6).attr('rel','on');
		$('img#saveBtn').hover(
			function(){$(this).fadeTo(500,1);},
			function(){$(this).fadeTo(500,0.6);}
		);
		$('img#saveBtn').fadeTo(500,0.6).hide();
	}
	if (state == 'run') {
		$('#goBtn').attr('src','/images/icons/runarrowoff.png').attr('rel','off').attr('title','Cannot run query');
	}
	if (state == 'new') {
		$('#goBtn').attr('src','/images/icons/runarrow.png').attr('rel','on').attr('title','Run query');
	}
}
//check box events
//an All check box was clicked
function setCheckAll(ele) {
	var relId = $(ele).attr('id');
	$(ele).fadeTo(1,1).next('span').css('color','black');
	$('input[rel="'+relId+'"]').removeAttr('checked').fadeTo(1,0.5).next('span').css('color','grey');
	setGoButton('new');
	return true;
}
//a single element check box was clicked
function setCheckSingle(ele) {
	var relId = $(ele).attr('rel');
	$('#'+relId).removeAttr('checked').fadeTo(1,0.5).next('span').css('color','grey');;
	$('input[rel="'+relId+'"]').fadeTo(1,1).next('span').css('color','black');
	setGoButton('new');
	return true;
}
//run a filter and display results
function runFilter() {
	//check to see if are runnable
	if ($('#goBtn').attr('rel') == 'off') return;
	//collect data
	var gender = Array();
	var age = Array();
	var pcode = Array();
	var ethnicity = Array();
	var lang = Array();
	var cat = Array();
	var srvc = Array();
	var pupil = Array();
	$('input:checked').each(function(idx,ele){
		var name = $(ele).attr('name');
		var val = $(ele).val();
		switch(name) {
			case 'mbrGender':
				gender.push(val);
				break;
			case 'mbrAge':
				age.push(val);
				break;
			case 'mbrPCode':
				pcode.push(val);
				break;
			case 'mbrEthnicity':
				ethnicity.push(val);
				break;
			case 'mbrLang':
				lang.push(val);
				break;
			case 'mbrPupil':
				pupil.push(val);
				break;
			case 'catCat':
				cat.push(val);
				break;
			case 'srvcSrvc':
				srvc.push(val);
				break;
			default:
				break;
		}
	});
	//set waiting image
	$('img#mapWaiting').toggleClass('noshow');
	$('div#gMap').toggleClass('noshow');
	//run query
	$.post(
		'/map/map',
		{'format':'json','gender':gender,'age':age,'pcode':pcode,'cat':cat,'srvc':srvc,'ethnicity':ethnicity,'lang':lang,'pupil':pupil},
		function(result){
			$('img#mapWaiting').toggleClass('noshow');
			$('div#gMap').toggleClass('noshow');
			if (result.success) {
				_mapDisplayPoints(result.data);
				$('img#saveBtn').show();
			} else {
				dlgError(result.msg);
				_mapClearMarkers();
				$('img#saveBtn').hide();
			}
			setGoButton('run');
		},
		'json'
	);
}
//run a saved query
function runSaved() {
	$('img#saveBtn').hide();
	var val = $('#saveSelect option:selected').val();
	//set waiting image
	$('img#mapWaiting').toggleClass('noshow');
	$('div#gMap').toggleClass('noshow');
	//run query
	$.post(
		'/map/run',
		{'format':'json','id':val},
		function(result){
			$('img#mapWaiting').toggleClass('noshow');
			$('div#gMap').toggleClass('noshow');
			if (result.success) {
				_mapDisplayPoints(result.data);
			} else {
				dlgError(result.msg);
				_mapClearMarkers();
			}
			setGoButton('run');
		},
		'json'
	);	
}
//save the current query
function saveFilter() {
	$('img#saveBtn').hide();
	_dlgSaveQuery.dialog('open');
}
//runs when user clicks save button on save query dialog
function _saveQuery(dlg) {
	var nm = $('#sqname').val();
	if (nm == '') {
		dlgError('Name is required');
		return false;
	}
	var desc = $('#sqdesc').val();
	$(dlg).dialog('close');
	$.post(
		'/map/save',
		{'format':'json','name':nm,'desc':desc},
		function(result){
			if (result.success) {
				var options = $('#saveSelect').attr('options');
				options[options.length] = new Option(result.data.name,result.data.id);
			} else {
				dlgError(result.msg);
			}
		},
		'json'
		
	);
	return true;
}

/* Post map initialisation */
function gmapPostInitialise() {
	$('body').unload('GUnload()');
	GMAPSavePosition('gmap_map1');
	//set up flags for additional overlays
	for (x in _GMAP.layers.gmap_map1) {
		if(_GMAP.layers.gmap_map1[x].type=='polygon'){_ovlExtra['ovl'+_GMAP.layers.gmap_map1[x].id]=false;}
	}
}


/** map control buttons **/
function mapControl(ele) {
	var id = $(ele).attr('id');
	switch (id) {
		case 'mapCtrlTarget':
			_mcTarget();
			break;
		case 'mapCtrlOvl1':
			_mcOverlay(ele,'red');
			break;
		case 'mapCtrlOvl2':
			_mcOverlay(ele,'green');
			break;
		case 'mapCtrlOvl3':
			_mcOverlay(ele,'blue');
			break;
		case 'mapCtrlOvl4':
			_dlgOverlay.dialog('open');
			break;
		case 'mapCtrlDraw':
			_mcOvlDraw();
			break;
		case 'mapCtrlPrint':
			_mcPrint();
			break;
	}
}
//display/hide additional overlay
function _toggleOverlay(ele) {
	_mcOverlay(ele,'ovl'+$('#ovlPick').val());
	_dlgOverlay.dialog('close');
}

//pause-click the target button
function _mcPauseTgtBtn(msecs) {
	$('#mapCtrlTarget').attr('src','/images/icons/btn_target_down.png');
	setTimeout("_mcEndTgtPause();",msecs); 
}
function _mcEndTgtPause(){$('#mapCtrlTarget').attr('src','/images/icons/btn_target_up.png');}

//centre the map to its start position
function _mcTarget() {
	_mcPauseTgtBtn(500);
	GMAPRestorePosition('gmap_map1'); 
}

//switch overlays on/off
var _mcBtns = {
	red : {on:'btn_red_splat_down.png',off:'btn_red_splat_up.png'},
	green : {on:'btn_green_splat_down.png', off:'btn_green_splat_up.png'},
	blue : {on:'btn_blue_splat_down.png', off:'btn_blue_splat_up.png'}
};
function _mcOverlay(ele, ovl) {
	if (ovl.match(/^ovl/) == null) {
		//process standard three overlays
		var state = $(ele).attr('rel');
		if (state == 'on') {
			try {
				GMAPHideLayer(ovl);
			} catch (x) {
				return;
			}
			var img = '/images/icons/' + _mcBtns[ovl].off;
			$(ele).attr('rel','off');
			$(ele).attr('title','Switch on '+$(ele).attr('alt'));
		} else {
			try {
				GMAPShowLayer(ovl);
			} catch (x) {
				return;
			}
			var img = '/images/icons/' + _mcBtns[ovl].on;
			$(ele).attr('rel','on');
			$(ele).attr('title','Switch off '+$(ele).attr('alt'));
		}
		$(ele).attr('src',img);
	} else {
		try {
			if (_ovlExtra[ovl]) {
				GMAPHideLayer(ovl);
				_ovlExtra[ovl] = false;
			} else {
				GMAPShowLayer(ovl)
				_ovlExtra[ovl] = true;
			}
		} catch (x) {
			return;
		}
	}
}

/** Start the drawing functionality **/
function _mcOvlDraw() {
	var state = $('#mapCtrlDraw').attr('rel');
	if (state == 'on') {
		var img = '/images/icons/crayon_up.png';
		$('#mapCtrlDraw').attr('rel','off');
		$('#mapCtrlDraw').attr('title','Switch on '+$('#mapCtrlDraw').attr('alt'));
		$('#panelLeft').accordion('activate',0);
		$('#map1').GmapDraw('reset');
	} else {
		var img = '/images/icons/crayon_down.png';
		$('#mapCtrlDraw').attr('rel','on');
		$('#mapCtrlDraw').attr('title','Switch off '+$('#mapCtrlDraw').attr('alt'));
		$('#panelLeft').accordion('activate',1);
		$('#map1').GmapDraw('init',{
			gmapObj:_GMAP.gmap_map1,
	  		saveFunc: '_drawSave',
	  		updateFunc: '_drawUpdate',
	  		colourFunc: '_drawColour',
			buttons:{	
				btnNew  : 'drcNew', 
		  		btnClose: 'drcClose', 
		  		btnClear: 'drcClear', 
		  		btnEdit : 'drcEdit', 
		  		btnDel  : 'drcDelete', 
		  		btnSave : 'srcSave',
		  		btnColour:'drcColour',
				}
			}
		);
	}
	$('#mapCtrlDraw').attr('src',img);

	//clear all current overlays and markers
	_mapClearMarkers();
	
}

var _drawingStore;
//called when user clicks save on the overlay controls
//also called when user click submit on save dialog
function _drawSave(drawing, op) {
	if (!op) { op = 'start';}
	if (op == 'start') {
		//store drawing
		_drawingStore = drawing;
		//display save dialog
		_dlgDrawSave.dialog('open');
	} else {
		//check return from save dialog
		if (op == 'save') {
			$.post('/map/ovlsave',
				{'format':'json','drawing':_drawingStore,'name':$('#drname').val(),'overlay':$('#drovl').val()},
				function(response){
					if (!response.success) {
						alert('Save failed!');
					}
					_dlgDrawSave.dialog('close');
				},
				'json'
			);
		} else if (op =='cancel') {
			return false;
		}
	}
}

//called when overlay drawing updates
function _drawUpdate(op,data) {
	switch (op) {
		case 'new':
			var shape = data.shape;
			break;
		case 'del':
			var shape = data.shape;
			break;
		case 'edit':
			var shape = data.shape;
			var mode = data.mode; //true = in edit, false = not in edit
			break;
		case 'save':
			if (data.success) {
				
			} else {
				
			}
			break;
	}
}

//display colour picker
function  _drawColour() {
	_dlgDrawColour.dialog('open');
}
//set colour
function _colourSend(dlg) {
	$('#map1').GmapDraw('dr_colour',$('input[name=colPick]').val());
	$(dlg).dialog('close');
}

/** print the map */
//pause-click the target button
function _mcPausePrnBtn(msecs) {
	$('#mapCtrlPrint').attr('src','/images/icons/printer_down.png');
	setTimeout("_mcEndPrnPause();",msecs); 
}
function _mcEndPrnPause(){$('#mapCtrlPrint').attr('src','/images/icons/printer_up.png');}
//print button
function _mcPrint() {
	_mcPausePrnBtn(500);
	window.open('/mapprint.html');
}
/**
 * Data display functionality 
 */
var _mkrmng = Array(); //marker manager
var _popImg = {};     //population marker images
var _popShadow ; //shadow - set in ready() function

/* Display returned points on map 
 *
 * data returns as array of arrays [id,info,title,lat,lng]
 */
function _mapDisplayPoints(data) {
//	GMAPRefreshLayer('members','gmap_map1',data);
	
	_mapClearMarkers();
	for (var i in data) {
		if (!_popImg[data[i].pop]) {
			_popImg[data[i].pop] = new google.maps.MarkerImage(
				'/images/GMap/numeric/yellow' + data[i].pop + '.png'
			);
		}
		_mkrmng[i] = new google.maps.Marker({
			position	: new google.maps.LatLng(data[i].lat,data[i].lng),
			title		: data[i].title,
			map			: _GMAP.gmap_map1,
			icon		: _popImg[data[i].pop],
			shadow		: _popShadow,
			id			: i
		});
		_attachInfo(_mkrmng[i],i,data[i].info);
	}
	//see http://groups.google.com/group/google-maps-js-api-v3/browse_thread/thread/30b3f94096ae3e6b/7952f688abc30c69?lnk=raot&fwc=1
	function _attachInfo(mkr,x,info) {
		var infoWindow = new google.maps.InfoWindow({
			content : info,
			position: mkr.position
		});
		google.maps.event.addListener(mkr,'click',function() {
	      		infoWindow.open(_GMAP.gmap_map1);
	    	});
	}
}

function _mapClearMarkers() {
	if (_mkrmng.length > 0) {
		for (i in _mkrmng) {
			_mkrmng[i].setMap(null);
			_mkrmng[i] = null;
		}
	}
	_mkrmng = Array();
}
