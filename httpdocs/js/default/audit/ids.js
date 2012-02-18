$(document).ready(function(){

	//<![CDATA[
	jQuery("#msgList").jqGrid({
		url:'/audit/ids?format=json',
		datatype: "json",
		colNames:['Id','Name','Value','Page','Tags','IP','Impact','Origin','Created'],
		colModel:[
			{name:'id',index:'id', width:55,editable:false,hidden:true},
			{name:'name',index:'name', width:180,sortable:true,editable:false},
			{name:'value',index:'value', width:70,editable:false,sortable:true},
			{name:'page',index:'page', width:480,editable:false,sortable:true},
			{name:'tags',index:'tags', width:80,editable:false,sortable:true},
			{name:'ip',index:'ip', width:80,sortable:true,editable:false},
			{name:'impact',index:'impact', width:80,sortable:true,editable:false},
			{name:'origin',index:'origin', width:80,sortable:true,editable:false},
			{name:'created',index:'created', width:80,sortable:true,editable:false}
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