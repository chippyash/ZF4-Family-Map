
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
	editCaption:"Edit Overlay",
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
	 url:'/input/ovl?format=json',
	 onInitializeForm: function(formId){
	 	//convert colour select to colorpicker
		$('select#colour').colourPicker({ico:'/images/colourpicker/colourPicker.gif'});
	 },
	 onclickSubmit: function(params,postData) {
	 	//add colour to post data
	 	return {colour:'#'+$('input[name="colour"]').val()}
	 },
	 afterShowForm:function(formId){
	 	//get current colour and put into colour selector
	 	var c = $('#dataList').jqGrid('getCell',$('#id_g').val(),'colour');
	 	$('input[name="colour"]').val(c).css('background-color','#'+c);
	 }
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
	_lastSel = '#dmOvl';
	$(_lastSel).addClass('dmSel');	
	//fetch colour selections
	var colourOpts = {};
	$.ajax({
		url:'/input/sel',
		data:{'format':'json','sel':'colours'},
		async:false,
		success:function(response) {
			if (response.success) {
				colourOpts = response.data
			} else {
				dlgError(response.msg);
			}
		},
		dataType:'json'
	});

	jQuery("#dataList").jqGrid({
		url:'/input/ovl?format=json',
		datatype: "json",
		colNames:['Id','Assignment','Name','Colour','Opacity','Org'],
		colModel:[
			{name:'id',index:'id', width:10,editable:false,hidden:true},
			{name:'tag',index:'tag', width:100,editable:true,
				edittype:'select',
				editoptions:{value:"none:None;red:Red;green:Green;blue:Blue"}},
			{name:'name',index:'name', width:300,editable:true,
				editrules:{required:true}},
			{name:'colour',index:'colour', width:100,editable:true,
				editrules:{required:true},edittype:'select',
				editoptions:{value:colourOpts},},
			{name:'opacity',index:'opacity', width:100,editable:true,
				editrules:{required:true,minValue:0,maxValue:1,number:true}},
			{name:'orgId',index:'orgId',editable:false,hidden:true}
		],
		rowNum:30,
		rowList:[10,20,30,40,50],
		pager: '#dataListPager',
		sortname: 'name',
		viewrecords: true,
		sortorder: "asc",
		caption:"Overlay Maintenance",
		autowidth:true,
		height:350,
		scroll:false,
		hidegrid:false,
		shrinkToFit:false
	});

	jQuery("#dataList").jqGrid('navGrid','#dataListPager',
	   {edit:true,add:false,del:true},
	   _editOptions,
	   _addOptions,
	   _delOptions
	);
	//hide csv download
	$('#dmDownload').hide();	

	
});
