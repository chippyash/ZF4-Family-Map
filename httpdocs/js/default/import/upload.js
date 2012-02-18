/** Change the data table that is being imported 
_lastSel is created by server script
**/
function dmSel(ele,table) {
	$(_lastSel).removeClass('dmSel');
	$(ele).addClass('dmSel');
	window.location='/import/index/stg/upload/tbl/' + table;
}
$(document).ready(function(){
	$(_lastSel).addClass('dmSel');
})