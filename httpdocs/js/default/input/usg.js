
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
	addCaption:"Add Usage Record",
	editCaption:"Edit Usage Record",
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
	 url:'/input/usg?format=json'
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
	_lastSel = '#dmUsg';
	$(_lastSel).addClass('dmSel');	

	/* Member selector */
	var _selMbr;
	$.ajax({url:'/input/sel?format=json&sel=mbr',
		success:function(response){
			if(response.success) {
				_selMbr = response.data;
			} else {
				simplePopupMessage({title:"Oops!",message:response.msg,'class':'negative'});
			}
		},
		cache:false,
		async:false,
		dataType:'json'
	});
	/* Service selector */
	var _selSrvc;
	$.ajax({url:'/input/sel?format=json&sel=srvc',
		success:function(response){
			if(response.success) {
				_selSrvc = response.data;
			} else {
				simplePopupMessage({title:"Oops!",message:response.msg,'class':'negative'});
			}
		},
		cache:false,
		async:false,
		dataType:'json'
	});

	jQuery("#dataList").jqGrid({
		url:'/input/usg?format=json',
		datatype: "json",
		colNames:['Id','Usage Date','Member','Service'],
		colModel:[
			{name:'id',index:'id', width:10,editable:false,hidden:true},
			{name:'uDate',index:'uDate', width:100,editable:true,editrules:{required:true}},
			{name:'prsnId',index:'prsnId', width:200,sortable:true,editable:true,edittype:"select",editoptions:{value:_selMbr},editrules:{required:true}},
			{name:'srvcId',index:'srvcId', width:200,sortable:true,editable:true,edittype:"select",editoptions:{value:_selSrvc},editrules:{required:true}}
		],
		rowNum:30,
		rowList:[10,20,30,40,50],
		pager: '#dataListPager',
		sortname: 'uDate',
		viewrecords: true,
		sortorder: "asc",
		caption:"Usage Maintenance",
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
	$('#dmDownload').attr('action','/input/usg/format/csv');	
	
});
