
/** Change the data table that is being imported **/
function dmSel(ele,table) {
	$(ele).addClass('dmSel');	
	window.location='/input/' + table;
}
