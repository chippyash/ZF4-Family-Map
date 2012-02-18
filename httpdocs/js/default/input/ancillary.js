
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
	addCaption:"Add Other Professional",
	editCaption:"Edit Other Professional",
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
	 url:'/input/ancillary?format=json'
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
	_lastSel = '#dmAncillary';
	$(_lastSel).addClass('dmSel');	

	var styleOpts = {'Mr':'Mr','Mrs':'Mrs','Miss':'Miss','Ms':'Ms','Dr':'Dr'};
	var ptypeOpts = {'doctor':'Doctor','health visitor':'Health Visitor','carer':'Carer'};	
	jQuery("#dataList").jqGrid({
		url:'/input/ancillary?format=json',
		datatype: "json",
		colNames:['Id','OrgId','OP Id','Type','Style','First Name','Mid. Name','Last Name'],
		colModel:[
			{name:'id',index:'id', width:10,editable:false,hidden:true},
			{name:'orgId',index:'orgId', width:10,editable:false,hidden:true},
			{name:'uid',index:'uid', width:80,sortable:true,editable:true,editrules:{required:true}},
			{name:'pType',index:'pType', width:60,editable:true,hidden:false,editrules:{required:true},edittype:"select",editoptions:{value:ptypeOpts}},			
			{name:'style',index:'style', width:40,editable:true,edittype:"select",editoptions:{value:styleOpts}},
			{name:'fName',index:'fName', width:150,editable:true,editrules:{required:true}},
			{name:'mName',index:'mName', width:150,editable:true},
			{name:'lName',index:'lName', width:150,editable:true,editrules:{required:true}}
		],
		rowNum:30,
		rowList:[10,20,30,40,50],
		pager: '#dataListPager',
		sortname: 'lName',
		viewrecords: true,
		sortorder: "asc",
		caption:"Other Professional Maintenance",
		autowidth:true,
		height:350,
		scroll:false,
		hidegrid:false,
		shrinkToFit:false
	});

	jQuery("#dataList").jqGrid('navGrid','#dataListPager',
	   {edit:true,add:true,del:false},
	   _editOptions,
	   _addOptions,
	   _delOptions
	);
	
	//set additional actions form attributes
	$('#dmDownload').attr('action','/input/ancillary/format/csv');	

	
});
