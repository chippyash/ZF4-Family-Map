$(document).ready(function(){

//<![CDATA[
	jQuery("#msgList").jqGrid({
		url:'/audit/logons?format=json',
		datatype: "json",
		colNames:['Id','Log Date','Priority','Message','User','IP'],
		colModel:[
			{name:'id',index:'id', width:20,editable:false,hidden:true},
			{name:'logDt',index:'logDt', width:100,sortable:true,editable:false},
			{name:'lvl',index:'lvl', width:30,editable:false,sortable:true},
			{name:'msg',index:'msg', width:100,editable:false,sortable:true},
			{name:'uName',index:'uName', width:80,editable:false,sortable:true},
			{name:'ip',index:'ip', width:80,sortable:true,editable:false}
		],
		rowNum:10,
		rowList:[10,20,30],
		pager: '#msgListPager',
		sortname: 'id',
		viewrecords: true,
		sortorder: "desc",
		caption:"Logon activity",
		autowidth:true,
		height:300,
		scroll:true,
		loadComplete:function(){gridOnLoad();},
		onSelectRow:function(){}
	});

	jQuery("#msgList").jqGrid('navGrid','#msgListPager',{edit:false,add:false,del:false});


	//]]>
});