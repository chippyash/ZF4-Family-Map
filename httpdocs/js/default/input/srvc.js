
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
	addCaption:"Add Service",
	editCaption:"Edit Service",
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
	 url:'/input/srvc?format=json'
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
	_lastSel = '#dmSrvc';
	$(_lastSel).addClass('dmSel');	
	var staffOpts = {};
	var enrolOpts = {};
	$.ajax({
		url:'/input/sel',
		data:{'format':'json','sel':'staff'},
		async:false,
		success:function(response) {
			if (response.success) {
				staffOpts = response.data
			} else {
				dlgError(response.msg);
			}
		},
		dataType:'json'
	});
	$.ajax({
		url:'/input/sel',
		data:{'format':'json','sel':'enroll'},
		async:false,
		success:function(response) {
			if (response.success) {
				enrolOpts = response.data
			} else {
				dlgError(response.msg);
			}
		},
		dataType:'json'
	});

	jQuery("#dataList").jqGrid({
		url:'/input/srvc?format=json',
		datatype: "json",
		colNames:['Id','OrgId','Name','Description','Enrol Type','Limit','Public description','Lead Staff'],
		colModel:[
			{name:'id',index:'id', width:10,editable:false,hidden:true},
			{name:'orgId',index:'orgId', width:10,editable:false,hidden:true},
			{name:'name',index:'name', width:150,editable:true,editoptions:{required:true}},
			{name:'desc',index:'desc', width:300,editable:true},
			{name:'enrolType',index:'enrolType', width:35,sortable:true,editable:true,edittype:"select",editoptions:{value:enrolOpts}},
			{name:'eLimit',index:'eLimit', width:35,sortable:false,editable:true,editoptions:{defaultValue:-1}},
			{name:'extInfo',index:'extInfo', sortable:false,editable:true,edittype:'textarea',hidden:true,editrules:{edithidden:true},editoptions:{rows:'5',cols:'35'}},
			{name:'staffId',index:'staffId', width:150,sortable:true,editable:true,edittype:"select",editoptions:{value:staffOpts}}
		],
		rowNum:30,
		rowList:[10,20,30,40,50],
		pager: '#dataListPager',
		sortname: 'name',
		viewrecords: true,
		sortorder: "asc",
		caption:"Service Maintenance",
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
	$('#dmDownload').attr('action','/input/srvc/format/csv');	

	
});
