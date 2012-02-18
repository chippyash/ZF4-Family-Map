var _dlgError; //error dialog
//display an error message
function dlgError(msg) {
	$('#uiError p.#errMsg').html(msg);
	_dlgError.dialog('open');
}
//last selection in master grid - used when amending detail grid
var _lastMstSel = 0;

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
//** Services **//
var _MstGridOptions = {
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
	 url:'/input/enroll?format=json'
}

var _MstAddOptions = $.extend({},_MstGridOptions,{
	editData:{'oper':'add','g':'mst'}
});
var _MstEditOptions = $.extend({},_MstGridOptions,{
	editData:{'oper':'edit','g':'mst'}
});

var _MstDelOptions = $.extend({},_MstGridOptions,{
	delData:{'oper':'del','g':'mst'}
});

//** Enrollments **//
var _DetGridOptions = {
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
	closeAfterAdd : true,
	closeOnEscape:true,
	reloadAfterSubmit : true,
	recreateForm : true,
	jqModal : true,
	addedrow : "first",
	topinfo : '',
	savekey: [false,13],
	navkeys: [false,38,40],
	viewPagerButtons : true,
	addCaption:"Add Enrollment",
	editCaption:"Edit Enrollment",
	afterSubmit: function(response,postdata) {
		var json=response.responseText;
		var result=eval("("+json+")");
		if (result.success) {
			//save the currently selected row in master grid
			_lastMstSel = $("#dataList").jqGrid('getGridParam','selrow');
			//var a = _lastMstSel;
			//update master grid
			$("#dataList").trigger("reloadGrid");
			return [true,'',result.data.id];
		} else {
			return [false,result.msg,postdata.id];
		}
	 },
	 errorTextFormat: function(response) {
		var msg = response.responseText;
		return msg.msg;
	 },
	 url:'/input/enroll?format=json'
}

var _DetAddOptions = $.extend({},_DetGridOptions,{
	editData:{'oper':'add','g':'det'},
	afterShowForm:function(formId){
		$('#srvcId').val(_getSrvcId());
	}		
});
var _DetEditOptions = $.extend({},_DetGridOptions,{
	editData:{'oper':'edit','g':'det'}
});

var _DetDelOptions = $.extend({},_DetGridOptions,{
	delData:{'oper':'del','g':'det'}
});

//get the service id - this is in the currently selected master grid row
function _getSrvcId() {
	var id = $('#dataList').jqGrid('getCell',$('#dataList').jqGrid('getGridParam','selrow'),'id');
	return id;
}

//onLoad
$(document).ready(function(){
	//set up error dialog
	_dlgError = $('#uiError').dialog({autoOpen:false,modal:true,resizeable:false,buttons:{"Ok":function(){$(this).dialog('close');}}})
	//turn on table selector
	_lastSel = '#dmEnroll';
	$(_lastSel).addClass('dmSel');	
	jQuery("#dataList").jqGrid({
		url:'/input/enroll?format=json&g=mst',
		datatype: "json",
		colNames:['Id','Service Name','Limit','Enrolled','Waiting','Past'],
		colModel:[
			{name:'id',index:'id', width:10,editable:false,hidden:true},
			{name:'name',index:'name', width:300,editable:false},
			{name:'eLimit',index:'eLimit', width:50,editable:false, align:'center'},
			{name:'enrolled',index:'enrolled', width:60,editable:false, align:'center'},
			{name:'waiting',index:'waiting', width:60,editable:false, align:'center'},
			{name:'past',index:'past', width:60,editable:false, align:'center'}
		],
		rowNum:30,
		rowList:[10,20,30,40,50],
		pager: '#dataListPager',
		sortname: 'name',
		viewrecords: true,
		sortorder: "asc",
		caption:"Enrollment Maintenance",
		autowidth:true,
		height:120,
		scroll:false,
		hidegrid:false,
		shrinkToFit:false,
		onSelectRow: function(rowId,selected) {
			if (selected) {
				var rowData = $(this).jqGrid('getRowData',rowId);
				$('#dataDetList')
					.jqGrid('setGridParam',{url:'/input/enroll?format=json&g=det&sId='+rowData.id,page:1})
					.jqGrid('setCaption','Enrollments for ' + rowData.name)
					.trigger('reloadGrid');
			}
		},
	 	gridComplete: function(data){
	 		//var a = $("#dataList").jqGrid('getGridParam','selrow');
	 		//var b = _lastMstSel;
	 		$("#dataList").jqGrid('setSelection',_lastMstSel, false);
	 	}
	});

	var peopleOpts = {};
	$.ajax({
		url:'/input/sel',
		data:{'format':'json','sel':'mbr'},
		async:false,
		success:function(response) {
			if (response.success) {
				peopleOpts = response.data
			} else {
				dlgError(response.msg);
			}
		},
		dataType:'json'
	});	
	jQuery("#dataDetList").jqGrid({
		url:'/input/enroll?format=json&g=det',
		datatype: "json",
		colNames:['Id','Member','Service','Enrol Date','orgId','Status'],
		colModel:[
			{name:'id',index:'id', width:10,editable:false,hidden:true},
			{name:'prsnId',index:'prsnId', width:200,editable:true,
				edittype:'select',
				editrules:{edithidden:true,required:true},
				editoptions:{value:peopleOpts}
			},
			{name:'srvcId',index:'srvcId', width:10,editable:true,hidden:true,
				editrules:{edithidden:false},
				editoptions:{defaultValue:_getSrvcId(),readonly:true,disabled:true}
			},
			{name:'eDate','index':'eDate',width:100,editable:true,
				editoptions:{dataInit:function(ele){$(ele).datepicker({dateFormat:'yy-mm-dd',gotoCurrent:true,changeYear:true,yearRange:'1900:2020'})}}},
			{name:'orgId',index:'orgId', width:200,editable:false,hidden:true},
			{name:'status',index:'status', width:80,editable:true,
				edittype:'select',
				editrules:{edithidden:true,required:true},
				editoptions:{value:{'enrolled':'Enrolled','waiting':'Waiting'}}
			}
		],
		rowNum:30,
		rowList:[10,20,30,40,50],
		pager: '#dataDetListPager',
		sortname: 'eDate',
		viewrecords: true,
		sortorder: "asc",
		caption:"Enrollment Maintenance",
		autowidth:true,
		height:150,
		scroll:false,
		shrinkToFit:false,
		hidegrid:false,
		loadError:function(xhr,status,error){
			dlgError(status);
		}
	});
	
	jQuery("#dataList").jqGrid('navGrid','#dataListPager',
	   {edit:false,add:false,del:false},
	   _MstEditOptions,
	   _MstAddOptions,
	   _MstDelOptions
	);
	jQuery("#dataDetList").jqGrid('navGrid','#dataDetListPager',
	   {edit:false,add:true,del:true},
	   _DetEditOptions,
	   _DetAddOptions,
	   _DetDelOptions
	);
	//set additional actions form attributes
	$('#dmDownload').attr('action','/input/enroll/format/csv');	

	
});
