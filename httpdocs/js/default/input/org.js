/* Organisation edit javascript */
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
	recreateForm : true,
	jqModal : true,
	addedrow : "first",
	topinfo : '',
	savekey: [false,13],
	navkeys: [false,38,40],
	viewPagerButtons : true,
	addCaption:"Add Record",
	editCaption:"Edit Record",
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
	 url:'/input/org?format=json'
}
var _addOptions = $.extend({},_gridOptions,{
	editData:{'oper':'add'},
	afterShowForm: function(formId) {
		//hide
		$('input#enckey').hide();
	},
	bottominfo: 'NB. If you add a new organisation, you must add its overlay file'
});
var _editOptions = $.extend({},_gridOptions,{
	editData:{'oper':'edit'},
	bottominfo: '',
	afterShowForm: function(formId) {
		//set value to something to pass required validation and hide
		$('input#uName').val('0').hide();
		$('input#uEmail').val('0').hide();
		$('input#payrollId').val('0').hide();
	},
	bottominfo: 'Administrator details are changed by the sysadmin for existing organisations'
});

var _delOptions = $.extend({},_gridOptions,{
	delData:{'oper':'del'}
});

$(document).ready(function(){

	jQuery("#dataList").jqGrid({
		url:'/input/org?format=json',
		datatype: "json",
		colNames:['Id','Tag','Name','Address','Contact','Tel','Email','Map CLat','Map CLong','Url','Enc key','License','Admin Name','Admin Email','Admin Payroll No.'],
		colModel:[
			{name:'id',index:'id', width:10,editable:false,hidden:true},
			{name:'tag',index:'tag', width:60,sortable:true,editable:true,editrules:{required:true}},
			{name:'name',index:'name', width:300,sortable:true,editable:true,editrules:{required:true}},
			{name:'address',index:'address', width:40,editable:true,hidden:true,editrules:{edithidden:true,required:true}},
			{name:'ctctName',index:'ctctName', width:100,editable:true,hidden:false,editrules:{edithidden:true,required:true}},
			{name:'ctctTel',index:'ctctTel', width:100,editable:true,hidden:false,editrules:{edithidden:true,required:true}},
			{name:'ctctEmail',index:'ctctEmail', width:250,editable:true,hidden:false,editrules:{edithidden:true,required:true}},
			{name:'mapCLat',index:'mapCLat', width:80,editable:true,hidden:false,editrules:{edithidden:true,required:true}},
			{name:'mapCLong',index:'mapCLong', width:80,editable:true,hidden:false,editrules:{edithidden:true,required:true}},
			{name:'url',index:'url', width:40,editable:true,hidden:true,editrules:{edithidden:true}},
			{name:'enckey',index:'enckey',editable:true,hidden:true,editrules:{edithidden:true},editoptions:{readonly:true}},
			{name:'license_key',index:'license_key',editable:true,hidden:true,editrules:{edithidden:true,required:true}},			
			{name:'uName',index:'uName', width:40,editable:true,hidden:true,editrules:{edithidden:true,required:true}},
			{name:'uEmail',index:'uEmail', width:40,editable:true,hidden:true,editrules:{edithidden:true,required:true}},
			{name:'payrollId',index:'payrollId', width:40,editable:true,hidden:true,editrules:{edithidden:true,required:true}}
		],
		rowNum:10,
		rowList:[10,20,30,40,50],
		pager: '#dataListPager',
		sortname: 'name',
		viewrecords: true,
		sortorder: "desc",
		caption:"Organisation Maintenance",
		autowidth:true,
		height:300,
		scroll:false,
		shrinkToFit:false
	});

	jQuery("#dataList").jqGrid('navGrid','#dataListPager',
	   {edit:true,add:true,del:false},
	   _editOptions,
	   _addOptions,
	   _delOptions
	);
});