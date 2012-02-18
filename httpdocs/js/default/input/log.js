
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
	viewPagerButtons : true
}
//onLoad
$(document).ready(function(){
	//turn on table selector
	_lastSel = '#dmLog';
	$(_lastSel).addClass('dmSel');	

	jQuery("#dataList").jqGrid({
		url:'/input/log?format=json',
		datatype: "json",
		colNames:['#','Date & time','Priority','Message','Log Uid','Log IP'],
		colModel:[
			{name:'id',index:'id', width:35,sortable:true},
			{name:'logDt',index:'logDt', width:120,sortable:true},
			{name:'lvl',index:'lvl', width:70,sortable:true},
			{name:'msg',index:'msg', width:350},
			{name:'uName',index:'uName', width:55,sortable:true},
			{name:'ip',index:'ip', width:100,sortable:true}
		],
		rowNum:30,
		rowList:[10,20,30,40,50],
		pager: '#dataListPager',
		sortname: 'logDT',
		viewrecords: true,
		sortorder: "asc",
		caption:"View Message Log",
		autowidth:true,
		height:350,
		scroll:false,
		hidegrid:false,
		shrinkToFit:false
	});

	jQuery("#dataList").jqGrid('navGrid','#dataListPager',
	   {edit:false,add:false,del:false}
	);
	
	//set additional actions form attributes
	$('#dmDownload').attr('action','/input/log/format/csv');	

	//show log delete button
	if (!_modeDemo) {
		$('input#dellog').show();
	} else {
		$('input[name="dnld"]').hide();
	}
});
