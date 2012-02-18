
var _addOptions = {
	addCaption:"Add User",
	closeAfterEdit:true,
	closeAfterAdd:true,
	closeOnEscape:true,
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
	afterShowForm: function(){afterShowForm_Add();},
	url:'/user/edituser',
	editData:{'oper':'add','format':'json'},
}

var editPopupWidth = 500;
var _editOptions = $.extend({},_addOptions,{
	editCaption:"Edit User",
	top : 200,
	left: (window.innerWidth/2)-(editPopupWidth/2),
	width: editPopupWidth,
	height: 'auto',
	dataheight: 'auto',
	modal: false,
	drag: false,
	resize: false,
	url: null,
	mtype : "POST",
	clearAfterAdd : true,
	closeAfterEdit : true,
	reloadAfterSubmit : true,
	onInitializeForm: null,//function(){onInitializeForm_Edit();},
	beforeInitData: null,//function(){beforeInitData_Edit();},
	beforeShowForm: null,//function(){beforeShowForm_Edit();},
	afterShowForm: function(){afterShowForm_Edit();},
	beforeSubmit: null,//function(){beforeSubmit_Edit();},
	afterSubmit: null,
	onclickSubmit: null,
	afterComplete: null,
	onclickPgButtons : null,
	afterclickPgButtons: null,
	editData : {},
	recreateForm : false,
	jqModal : true,
	closeOnEscape : true,
	addedrow : "first",
	topinfo : '',
	bottominfo: '',
	saveicon : [],
	closeicon : [],
	savekey: [false,13],
	navkeys: [false,38,40],
	checkOnSubmit : false,
	checkOnUpdate : false,
	_savedData : {},
	processing : false,
	onClose : null,
	ajaxEditOptions : {},
	serializeEditData : null,
	viewPagerButtons : true,
	editData:{'oper':'edit','format':'json'},
});

var _delOptions = $.extend({},_addOptions,{
	delData:{'oper':'del','format':'json'},
	afterShowForm: function(){afterShowForm_Delete();}
});


/*
function gridOnLoad()
{
	Cufon.refresh();
	// show the add/edit forms in popups
	// strip all button classes and replace them with 'button'
	//$('.fm-button').removeClass().addClass('button');
}

function showEditRowButton()
{
	jQuery("#btn-editRow").show();
}

function hideEditRowButton()
{
	jQuery("#btn-editRow").hide();
}

function afterShowForm_Edit()
{
	// do various formatting
	var dialog = jQuery('.ui-jqdialog');
	// apply our styles to buttons
	dialog.find('#sData').addClass('button ok');
	dialog.find('#cData').addClass('button cancel');
	// remove icon tags
	dialog.find('#cData span.ui-icon, #sData span.ui-icon').remove();
	// style select menus
	//dialog.find('select').removeClass().addClass('uiselect').css('width','20em').selectmenu({style:'dropdown'});
	addButtonIcons();
	Cufon.refresh();

}
*/

/* @todo - make status and role editable when jGrid bug sorted */

$(document).ready(function(){


	jQuery("#userList").jqGrid({
		url:'/user/list?format=json',
		datatype: "json",
		colNames:[,'Username', 'Email', 'First Name','Last Name','Last Logon','Status','Role'],
		colModel:[
			{name:'id',index:'id', width:1,editable:false,editoptions:{readonly:true,size:1},hidden:true},
			{name:'uName',index:'uName', width:0,sortable:true,editable:true,editoptions:{size:30},editrules:{required:true}},
			{name:'uEmail',index:'uEmail', width:400,editable:true,editoptions:{size:30},sortable:true,editrules:{required:true,email:true}},
			{name:'fName',index:'fName', width:0,editable:true,editoptions:{size:20},sortable:true,editrules:{required:true}},
			{name:'lName',index:'lName', width:0,editable:true,editoptions:{size:20},sortable:true,editrules:{required:true}},
			{name:'lastLogon',index:'lastLogon', width:250,sortable:true,editable:false},
			{name:'rowSts',index:'rowSts', width:0,sortable:true,editable:false,edittype:"select",editoptions:{value:{'active':'Active','suspended':'Suspended','defunct':'Defunct'}},editrules:{required:true}},
			{name:'rName',index:'rName', width:0,editable:false,edittype:"select",editoptions:{value:"user:user;admin:admin"},sortable:true,editrules:{required:true}}
		],
		rowNum:10,
		rowList:[10,20,30],
		pager: '#userListPager',
		sortname: 'uName',
		viewrecords: true,
		sortorder: "asc",
		caption:"System Users",
		autowidth:true,
		height:300,
		scroll:true,
		loadComplete:function(){gridOnLoad();},
		onSelectRow:function(){showEditRowButton();}
	});

	jQuery("#userList").jqGrid('navGrid',
	   '#userListPager',
	   {edit:false,add:false,del:false,search:false}
	);

	reConfigure('#userList',_addOptions,_editOptions,_delOptions);

});
