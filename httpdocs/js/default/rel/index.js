/** Roamer support callbacks **/
function constellationRoamer_onLoaded(){
	//alert('Loaded');
}
function constellationRoamer_onChange(nodeID){}
function constellationRoamer_onDoubleClick(nodeID) {}
function constellationRoamer_onEdgeClick(edgeID) {}
function constellationRoamer_onEdgeDoubleClick(edgeID){}

//ONLOAD
$(document).ready(function(){
	var flashvars = {
		config_url: '/constellation_roamer/constellation_config.xml2',
		selected_node_id: '0',
		instance_id: '1',
		debug: false
	};
	
	var params = {
		bgcolor: '#ffffff',
		allowScriptAccess: 'sameDomain',
		quality: 'high',
		scale: 'noscale'
	};
	
	var attributes = {
		id: "roamer",
		name: "roamer"
	};
	
	swfobject.embedSWF(
		"/constellation_roamer/constellation_roamer.swf", "roamer", "710px", "650px",
		"9", "/constellation_roamer/expressInstall.swf", flashvars, params, attributes			);
	//swfobject.getObjectById('roamer').setTreeDepth($('#treeDepth').val());
	
	$("#panelLeft").accordion({autoHeight:false});
});