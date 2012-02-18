
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

var editPopupWidth = 550;
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
	addCaption:"Add Relationship Type",
	editCaption:"Edit Relationship Type",
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
	 url:'/input/reltype?format=json',
	 onInitializeForm: function(formId){
	 	//convert colour select to colorpicker
		$('select#relColour').colourPicker({ico:'/images/colourpicker/colourPicker.gif'});
	 },
	 onclickSubmit: function(params,postData) {
	 	//add colour to post data
	 	return {relColour:$('input[name="relColour"]').val()}
	 },
	 afterShowForm:function(formId){
	 	//get current colour and put into colour selector
	 	var c = $('#dataList').jqGrid('getCell',$('#id_g').val(),'relColour');
	 	$('input[name="relColour"]').val(c).css('background-color','#'+c);
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

//create custom element for head and tail person types
function elePType(value,options) {
	var elArr = Array();
	var valArr = value.split(',');
	//NB if you extend the number of person types you will need to update next line
	var pTypes = Array('member','pupil','staff','doctor','health visitor','carer');
	for (x in pTypes) {
		var ele = document.createElement('input');
		ele.type = 'checkbox';
		ele.value = pTypes[x];
		ele.name = options.name;
		if (pTypes[x] == 'pupil') {
			$(ele).bind('click',function(ev){
				if ($(this).attr('checked')) {
					var nm = $(this).attr('name');
					$('input[name="'+nm+'"][value="member"]').attr('checked',true);
				}
			});
		}
		var s = pTypes[x];
		var t = valArr.indexOf(pTypes[x]);
		if (valArr.indexOf(pTypes[x])>=0) {
			ele.checked = true;
		}
		var sp = document.createElement('span');
		$(sp).append(ele).append('&nbsp;'+pTypes[x].charAt(0).toUpperCase() + pTypes[x].slice(1)+'&nbsp;');
		elArr.push(sp);
	}
	
	return elArr;
}
//retrieve the return value from the form for head and tail types
function eleValPType(ele) {
	var ret = Array();
	//NB if you extend the number of person types you will need to update next line
	for (var x=0;x<=5;x++) {
		var thisele = $(ele[x]).children('input:checked').first();
		if (thisele.length == 1) {
			ret.push($(thisele).val())
		}
	}
	ret = ret.join(',')
	return ret;
}


//onLoad
$(document).ready(function(){
	//turn on table selector
	_lastSel = '#dmReltype';
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
		url:'/input/reltype?format=json',
		datatype: "json",
		colNames:['Id','orgId','Name','Reverse Name','Description','Direction','Colour','Line thickness','Allowed Head Types','Allowed Tail Types'],
		colModel:[
			{name:'id',index:'id', width:10,editable:false,hidden:true},
			{name:'orgId',index:'orgId', width:10,editable:false,hidden:true},
			{name:'name',index:'name', width:140,editable:true,
				editrules:{required:true}
			},
			{name:'revName',index:'revName', width:140,editable:true,
				editrules:{required:true}
			},
			{name:'desc',index:'desc', width:360,editable:true,edittype:'textarea',
				editoptions:{rows:3}
			},
			{name:'direction',index:'direction', width:500,editable:true,hidden:true,
				edittype:'select',
				editoptions:{value:{'one-way':'one-way','two-way':'two-way'}},
				editrules:{edithidden:true,required:true}
			},
			{name:'relColour',index:'relColour', editable:true,hidden:true,
				edittype:'select',
				editoptions:{value:colourOpts},
				editrules:{edithidden:true,required:true}
			},
			{name:'relValue',index:'relValue', editable:true,hidden:true,
				edittype:'select',
				editoptions:{value:{1:'1',3:'3',5:'5',7:'7'}},
				editrules:{edithidden:true,required:true}
			},
			{name:'headType',index:'headType', editable:true,hidden:true,
				edittype:'custom',
				editoptions:{custom_element:elePType,custom_value:eleValPType},
				editrules:{edithidden:true,required:true}
			},
			{name:'tailType',index:'tailType', editable:true,hidden:true,
				edittype:'custom',
				editoptions:{custom_element:elePType,custom_value:eleValPType},
				editrules:{edithidden:true,required:true}
			}
		],
		rowNum:30,
		rowList:[10,20,30,40,50],
		pager: '#dataListPager',
		sortname: 'name',
		viewrecords: true,
		sortorder: "desc",
		caption:"Relationship Type Maintenance",
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
	$('#dmDownload').attr('action','/input/reltype/format/csv');	

	
});
