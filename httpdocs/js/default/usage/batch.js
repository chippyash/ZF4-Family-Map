/* batch entry of service usage */

$(document).ready(function(){
	usgSetTableRows();
	$('#dt').datepicker();
});

/** Set the number of table rows to the batch count
 * _memOpts and _srvcOpts are output by server
 */
function usgSetTableRows() {
	var batch = $('#nrec').val();
	var content = $('#usgTable>tbody');
	content.html(''); //clear the current content
	var xhtml = '';
	for (var x=0;x<batch;x++) {
		xhtml += "<tr><td><select name='mbr[" + x + "]'>" + _memOpts + "</select></td>";
		xhtml += "<td><select name='srvc[" + x + "]'>" + _srvcOpts + "</select></td></tr>";
	}
	content.html(xhtml)
}