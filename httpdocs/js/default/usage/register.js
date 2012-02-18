/* batch entry of service usage */

$(document).ready(function(){
	srvcSetTableRows();
	$('#dt').datepicker();
});

/** 
 * Fetch the members for a particular enrolled service
 */
function srvcSetTableRows() {
	var content = $('#usgTable>tbody');
	content.html(''); //clear the current content
	$.getJSON(
		'/usage/sel',
		{'format':'json','sel':'enrolled','srvcId':$('#srvcId').val()},
		function(response){
			var xhtml = '';
			if (response.success) {
				for (var x in response.data) {
					xhtml += "<tr><td>" + response.data[x] + "</td>";
					xhtml += "<td align='center'><input type='checkbox' name='mbr[" + x + "]' checked=true/></td></tr>";
				}
			} else {
				xhtml = '<tr><td colspan="2">No enrollments</td></tr>';
			}
			content.html(xhtml);
		}
	);
	
}