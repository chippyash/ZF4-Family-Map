
/** Change the data table that is being imported **/
function dmSel(ele,table) {
	$(ele).addClass('dmSel');	
	window.location='/import/index/stg/upload/tbl/' + table;
}
