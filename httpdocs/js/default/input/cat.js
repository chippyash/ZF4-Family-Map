
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
	addCaption:"Add Category",
	editCaption:"Edit Category",
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
	 url:'/input/cat?format=json'
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
	_lastSel = '#dmCat';
	$(_lastSel).addClass('dmSel');	

	jQuery("#dataList").jqGrid({
		url:'/input/cat?format=json',
		datatype: "json",
		colNames:['Id','OrgId','Name','Desc','Valid Relationships'],
		colModel:[
			{name:'id',index:'id', width:10,editable:false,hidden:true},
			{name:'orgId',index:'orgId', width:10,editable:false,hidden:true},
			{name:'name',index:'name', width:100,editable:true,editrules:{required:true}},
			{name:'desc',index:'desc', width:250,editable:true,editrules:{required:true}},
			{name:'relId',index:'relId', width:100,sortable:true,editable:true,editrules:{required:true}}
		],
		rowNum:30,
		rowList:[10,20,30,40,50],
		pager: '#dataListPager',
		sortname: 'id',
		viewrecords: true,
		sortorder: "desc",
		caption:"Category Maintenance",
		autowidth:true,
		height:350,
		scroll:false,
		hidegrid:false,
		shrinkToFit:false
	});

	jQuery("#dataList").jqGrid('navGrid','#dataListPager',
	   {edit:true,add:true,del:true},
	   _editOptions,
	   _addOptions,
	   _delOptions
	);
	//set additional actions form attributes
	$('#dmDownload').attr('action','/input/cat/format/csv');	

	
});
