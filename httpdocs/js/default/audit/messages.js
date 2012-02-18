
$(document).ready(function(){

	//<![CDATA[
	jQuery("#msgList").jqGrid({
		url:'/audit/messages?format=json',
		datatype: "json",
		colNames:['Id','Log Date','Priority','Message','User','IP'],
		colModel:[
			{name:'id',index:'id', width:55,editable:false,hidden:true},
			{name:'logDt',index:'logDt', width:180,sortable:true,editable:false},
			{name:'lvl',index:'lvl', width:70,editable:false,sortable:true},
			{name:'msg',index:'msg', width:480,editable:false,sortable:true},
			{name:'uName',index:'uName', width:80,editable:false,sortable:true},
			{name:'ip',index:'ip', width:80,sortable:true,editable:false}
		],
		rowNum:10,
		rowList:[10,20,30],
		pager: '#msgListPager',
		sortname: 'id',
		viewrecords: true,
		sortorder: "desc",
		caption:"Action Messages",
		autowidth:true,
		height:300,
		scroll:true,
		loadComplete:function(){gridOnLoad();},
		onSelectRow:function(){}
	});
	jQuery("#msgList").jqGrid('navGrid','#msgListPager',{edit:false,add:false,del:false});

//]]>

});