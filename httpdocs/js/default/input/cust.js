var _dlgError; //error dialog
//display an error message
function dlgError(msg) {
	$('#uiError p.#errMsg').html(msg);
	_dlgError.dialog('open');
}
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

//MEMBER Grid 
var editPopupWidth = 500;
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
	addCaption:"Add Member",
	editCaption:"Edit Member",
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
	 url:'/input/cust?format=json'
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

//RELATIONSHIP Grid 
var editPopupWidth = 500;
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
	closeOnEscape:true,
	reloadAfterSubmit : true,
	recreateForm : true,
	jqModal : true,
	addedrow : "first",
	topinfo : '',
	savekey: [false,13],
	navkeys: [false,38,40],
	viewPagerButtons : true,
	addCaption:"Add Relationship",
	editCaption:"Edit Relationship",
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
	 url:'/input/cust?format=json'
}
var _DetAddOptions = $.extend({},_DetGridOptions,{
	editData:{'oper':'add','g':'det'},
	afterShowForm:function(formId){
		//get current member we are adding relationship for
		var mbr = $('#dataList').jqGrid('getCell',$('#dataList').jqGrid('getGridParam','selrow'),'id');
		$('select#prsnIdA').val(mbr);
		//get allowable relationship types for the person
		$.getJSON(
			'/input/sel',
			{'format':'json','uid':mbr,'sel':'allowrel'},
			function(response){
				if (response.success) {
					var xhtml = '';
					for (var id in response.data) {
						xhtml += "<option value='"+id+"'>"+response.data[id]+"</option>";
					}
					$('select#relTypeId').html(xhtml);
				} else {
					dlgError(response.msg);
				}
			}
		);
	}
});
var _DetEditOptions = $.extend({},_DetGridOptions,{
	editData:{'oper':'edit','g':'det'}
});

var _DetDelOptions = $.extend({},_DetGridOptions,{
	delData:{'oper':'del','g':'det'}
});

//get allowed people for a particular relationship type
function getAllowedPeople(ele) {
	$.ajax({
		url:'/input/sel',
		data:{'format':'json','reltype':$(ele).val(),'uid':$('select#prsnIdA').val(),'sel':'allowed'},
		dataType:'json',
		async:false,
		success: function(response){
			if (response.success) {
				var xhtml = '';
				for (var id in response.data) {
					xhtml += "<option value='"+id+"'>"+response.data[id]+"</option>";
				}
				$('select#prsnIdB').html(xhtml);
			} else {
				dlgError(response.msg);
			}
		}
	});
}

//onLoad
$(document).ready(function(){
	//set up error dialog
	_dlgError = $('#uiError').dialog({autoOpen:false,modal:true,resizeable:false,buttons:{"Ok":function(){$(this).dialog('close');}}})
	//turn on table selector
	_lastSel = '#dmCust';
	$(_lastSel).addClass('dmSel');	
	var langOpts = {};
	var relOpts = {};
	var peopleOpts = {};
	var yesno = {'no':'No','yes':'Yes'};
	var catOpts = {};
	$.ajax({
		url:'/input/sel',
		data:{'format':'json','sel':'lang'},
		async:false,
		success:function(response) {
			if (response.success) {
				langOpts = response.data
			} else {
				dlgError(response.msg);
			}
		},
		dataType:'json'
	});
	$.ajax({
		url:'/input/sel',
		data:{'format':'json','sel':'cats'},
		async:false,
		success:function(response) {
			if (response.success) {
				catOpts = response.data
			} else {
				dlgError(response.msg);
			}
		},
		dataType:'json'
	});
	$.ajax({
		url:'/input/sel',
		data:{'format':'json','sel':'rels'},
		async:false,
		success:function(response) {
			if (response.success) {
				relOpts = response.data
			} else {
				dlgError(response.msg);
			}
		},
		dataType:'json'
	});
	$.ajax({
		url:'/input/sel',
		data:{'format':'json','sel':'people'},
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
	var styleOpts = {'Mr':'Mr','Mrs':'Mrs','Miss':'Miss','Ms':'Ms','Mst':'Mst','Dr':'Dr'};
	var ptypeOpts = {'member':'Member','pupil':'Pupil'};
	jQuery("#dataList").jqGrid({
		url:'/input/cust?format=json&g=mst',
		datatype: "json",
		colNames:['Id','OrgId','Uid','Style','First Name','Mid. Name','Last Name','DOB','Gender','Ethnicity','Type','Mother tongue','Categories','Mobile','Telephone','Email','Surgery','PIN','Address #1','Post Code'],
		colModel:[
			{name:'id',index:'id', width:10,editable:false,hidden:true},
			{name:'orgId',index:'orgId', width:10,editable:false,hidden:true},
			{name:'uid',index:'uid', width:85,sortable:true,editable:true},
			{name:'style',index:'style', width:40,editable:true,edittype:"select",editoptions:{value:styleOpts}},
			{name:'fName',index:'fName', width:110,editable:true,editrules:{required:true}},
			{name:'mName',index:'mName', width:110,editable:true,hidden:true,editrules:{edithidden:true}},
			{name:'lName',index:'lName', width:110,editable:true,editrules:{required:true}},
			{name:'dob',index:'dob', width:80,editable:true,hidden:true,editrules:{edithidden:true,required:true},editoptions:{dataInit:function(ele){$(ele).datepicker({dateFormat:'yy-mm-dd',gotoCurrent:true,changeYear:true,defaultDate:$(ele).val(),yearRange:'1900:2020'})}}},
			{name:'gender',index:'gender', width:40,editable:true,hidden:true,edittype:"select",editoptions:{value:{'female':'Female','male':'Male'}},editrules:{required:true,edithidden:true}},
			{name:'ethnicity',index:'ethnicity', width:40,editable:true,hidden:true,editrules:{required:true,edithidden:true}},
			{name:'pType',index:'pType', width:60,editable:true,hidden:false,editrules:{required:true},edittype:"select",editoptions:{value:ptypeOpts}},			
			{name:'lang',index:'lang', width:60,editable:true,hidden:true,editrules:{required:true,edithidden:true},edittype:"select",editoptions:{value:langOpts}},			
			{name:'cats',index:'cats', width:60,editable:true,hidden:true,editrules:{required:false,edithidden:true},edittype:"select",editoptions:{value:catOpts,multiple:true,size:6}},
			{name:'mTel',index:'mTel',width:85,editable:true,editrules:{number:true}},
			{name:'oTel',index:'oTel',width:85,editable:true,editrules:{number:true}},
			{name:'email',index:'email',width:110,editable:true,editrules:{email:true,required:false}},
			{name:'surgery',index:'surgery',width:100,editable:true},
			{name:'pin',index:'pin',width:50,editable:true,hidden:true,editrules:{edithidden:true},editoptions:{readonly:true,disabled:true}},
			{name:'hNum',index:'hNum', width:160,editable:true,editrules:{required:true}},
			{name:'pCode',index:'pCode', width:75,editable:true,editrules:{required:true}}
			
		],
		rowNum:30,
		rowList:[10,20,30,40,50],
		pager: '#dataListPager',
		sortname: 'lName',
		viewrecords: true,
		sortorder: "asc",
		caption:"Member Maintenance",
		autowidth:true,
		height:200,
		scroll:false,
		shrinkToFit:false,
		hidegrid:false,
		loadError:function(xhr,status,error){
			dlgError(status);
		},
		onSelectRow: function(rowId,selected) {
			if (selected) {
				var rowData = $(this).jqGrid('getRowData',rowId);
				var pName = rowData.style + ' ' + rowData.fName + ' ' + rowData.lName;
				$('#dataDetList')
					.jqGrid('setGridParam',{url:'/input/cust?format=json&g=det&pId='+rowData.id,page:1})
					.jqGrid('setCaption','Relationships for ' + pName)
					.trigger('reloadGrid');
			}
		}
		
	}).jqGrid('gridResize');

	jQuery("#dataDetList").jqGrid({
		url:'/input/cust?format=json&g=det',
		datatype: "json",
		colNames:['Id','Person','Relationship','','Name','Related to'],
		colModel:[
			{name:'id',index:'id', width:10,editable:false,hidden:true},
			{name:'prsnIdA',index:'prsnIdA', width:10,editable:true,hidden:true,
				edittype:'select',
				editrules:{edithidden:true,required:true},
				editoptions:{value:peopleOpts,readonly:true,disabled:true}
			},
			{name:'relTypeId',index:'relTypeId', width:150,editable:true,edittype:"select",
				editoptions:{value:relOpts,onChange:'getAllowedPeople(this)'}
			},
			{name:'direction','index':'direction',width:30,editable:false},
			{name:'name',index:'name', width:200,editable:false},
			{name:'prsnIdB',index:'prsnIdB', width:10,editable:true,hidden:true,
				edittype:'select',
				editrules:{edithidden:true,required:true},
				editoptions:{value:peopleOpts}
			}
		],
		rowNum:30,
		rowList:[10,20,30,40,50],
		pager: '#dataDetListPager',
		sortname: 'name',
		viewrecords: true,
		sortorder: "asc",
		caption:"Relationship Maintenance",
		autowidth:true,
		height:100,
		scroll:false,
		shrinkToFit:false,
		hidegrid:false,
		loadError:function(xhr,status,error){
			dlgError(status);
		}
		
	});

	jQuery("#dataList").jqGrid('navGrid','#dataListPager',
	   {edit:true,add:true,del:false},
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
	/*
	$("td.ops select option[value='lt']").remove();
	$("td.ops select option[value='le']").remove();
	$("td.ops select option[value='gt']").remove();
	$("td.ops select option[value='ge']").remove();	
	$("td.ops select option[value='bn']").remove();	
	$("td.ops select option[value='in']").remove();	
	$("td.ops select option[value='ni']").remove();
	$("td.ops select option[value='ew']").remove();
	$("td.ops select option[value='en']").remove();
	$("td.ops select option[value='cn']").remove();
	$("td.ops select option[value='nc']").remove();
	*/
	//set additional actions form attributes
	$('#dmDownload').attr('action','/input/cust/format/csv');	

});
