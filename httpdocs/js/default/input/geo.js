
/** Change the data table that is being edited **/
var _lastSel;
function dmSel(ele,table) {
	if (_lastSel != 'undefined') {
		$(_lastSel).removeClass('dmSel');
	}
	$(ele).addClass('dmSel');	
	_lastSel = ele;
	window.location='/input/' + table;	
}

var editPopupWidth = 500;
var _gridOptions = {
	left: (window.innerWidth/2)-(editPopupWidth/2),
	width: editPopupWidth,
	top : 200,
	height: 'auto',
	dataheight: 'auto',
	modal: false,
	drag: false,
	resize: false,
	mtype : "POST",
	clearAfterAdd : true,
	closeAfterEdit : true,
	closeOnEscape:true,
	reloadAfterSubmit : true,
	recreateForm : false,
	jqModal : true,
	addedrow : "first",
	topinfo : '',
	savekey: [false,13],
	navkeys: [false,38,40],
	viewPagerButtons : true,
	addCaption:"Add Geodata",
	editCaption:"Edit Geodata",
	afterSubmit: function(response,postdata) {
		var json=response.responseText;
		var result=eval("("+json+")");
		if (result.success) {
			return [true,'',result.data.id];
		} else {
			return [false,result.msg,postdata.id];
		}
	 },
	 errorTextFormat: function(response) {
		var msg = response.responseText;
		return msg.msg;
	 },
	 url:'/input/geo?format=json'
}
var _addOptions = $.extend({},_gridOptions,{
	editData:{'oper':'add'}
});
var _editOptions = $.extend({},_gridOptions,{
	editData:{'oper':'edit'}
});

var _delOptions = $.extend({},_gridOptions,{
	delData:{'oper':'del'}
});

//onLoad
$(document).ready(function(){
	//turn on table selector
	_lastSel = '#dmGeo';
	$(_lastSel).addClass('dmSel');	
	var stsOpts = {'new':'New','found':'Found','failed':'Failed'};
	jQuery("#dataList").jqGrid({
		url:'/input/geo?format=json',
		datatype: "json",
		colNames:['Id','First Line','Post Code','Lat','Long','Status'],
		colModel:[
			{name:'id',index:'id', width:10,editable:false,hidden:true},
			{name:'hNum',index:'hNum', width:150,editable:true,editrules:{required:true}},
			{name:'pCode',index:'pCode', width:140,editable:true,editrules:{required:true}},
			{name:'lat',index:'lat', width:150,editable:true,editrules:{required:true}},
			{name:'lng',index:'lng', width:150,editable:true,editrules:{required:true}},
			{name:'sts',index:'sts', width:50,editable:true,editrules:{required:true},edittype:"select",editoptions:{value:stsOpts}}
		],
		rowNum:30,
		rowList:[10,20,30,40,50],
		pager: '#dataListPager',
		sortname: 'id',
		viewrecords: true,
		sortorder: "desc",
		caption:"Geodata Maintenance",
		autowidth:true,
		height:350,
		scroll:false,
		shrinkToFit:false,
		hidegrid:false,
		ondblClickRow : function(rowId,iRow,iCol,evnt) {
			//display pin edit dialog
			_currLoc = $(this).jqGrid('getCell',rowId,'id');
			$('div#locEdit').dialog('open');
		}
	});

	jQuery("#dataList").jqGrid('navGrid','#dataListPager',
	   {edit:true,add:false,del:false},
	   _editOptions,
	   _addOptions,
	   _delOptions
	);
	//set additional actions form attributes
	$('#dmDownload').replaceWith('<p>Double click location to edit on map</p>');
	//set up location edit dialog
	$('div#locEdit').dialog({
		autoOpen:false,
		open : _locOpen,
		height : 650,
		width : 650,
		buttons: {'Save':function(){_locSave(this);}}
	});
});

/** location edit functionality **/
//open map when dialog opens;
var _currLoc;
function _locOpen() {
	$.getJSON(
		'/input/locedit/format/json/id/' + _currLoc,
		function(response,txtSuccess,xhr){
			if (response.success) {		
				_locEditInitialise(response.data.lat,response.data.lng);						
			}
		}
	);	
}
//save the location
function _locSave(dlg) {
	var pos = $('input#locvalue').val().split(',');
	$(dlg).dialog('close');
	$.ajax({
		type:'POST',
		url:'/input/geo?format=json',
		data:{'oper':'edit','id':_currLoc,'lat':pos[0],'lng':pos[1],'sts':'found'},
		success:function(response,txtSts,xmlReq){
			if (response.success) {
				$("#dataList").trigger("reloadGrid");
			} else {
				alert(response.msg);
			}
		},
		async:true
	});
	
}

//save current marker position
function _locEditPosition(latLng) {
	$('input#locvalue').val([
    latLng.lat(),
    latLng.lng()
  ].join(','));
}
//initialise the location edit
function _locEditInitialise(lat,lng) {
  var latLng = new google.maps.LatLng(lat,lng);
  var map = new google.maps.Map(document.getElementById('locMap'), {
    zoom: 13,
    center: latLng,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  });
  var marker = new google.maps.Marker({
    position: latLng,
    title: 'Location',
    map: map,
    draggable: true
  });
  
  // Update current position info.
  _locEditPosition(latLng);

  google.maps.event.addListener(marker, 'drag', function() {
    _locEditPosition(marker.getPosition());
  });
}
