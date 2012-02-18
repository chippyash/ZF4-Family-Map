/** Change the data table that is being imported 
_lastSel is created by server script
**/
function dmSel(ele,table) {
	$(_lastSel).removeClass('dmSel');
	$(ele).addClass('dmSel');
	window.location='/import/index/stg/upload/tbl/' + table;
}
//display an error message
var _dlgError;
function dlgError(msg) {
	$('#uiError p.#errMsg').html(msg);
	_dlgError.dialog('open');
}
//display a notice message
var _dlgNotice;
function dlgNotice(msg) {
	$('#uiNotice p.#msg').html(msg);
	_dlgNotice.dialog('open');
}

$(document).ready(function(){
	
	$(_lastSel).addClass('dmSel');
	//reconfigure the mapping form display
	$('div#impmap form table').addClass('zend_form').prepend('<thead><th>Incoming field</th><th>Maps to</th></thead>');
	$('div#impmap form table input#submit').val('Upload');
	$('div#impmap table.zend_form tr').last().hide().prev().addClass('lastrow');
	//add accordion
	$('div#rightPane').accordion({autoHeight:false});
	//set up dialogs
	_dlgError = $('#uiError').dialog({autoOpen:false,modal:true,resizeable:false,buttons:{"Ok":function(){$(this).dialog('close');}}})
	_dlgNotice = $('#uiNotice').dialog({autoOpen:false,modal:true,resizeable:false,buttons:{"Ok":function(){$(this).dialog('close');}}})
});
//profile save
function _setProfile() {
	//collect the mapping
	var mapping = {};
	$('div#impmap table.zend_form tr td select').each(function(){
		mapping[$(this).attr('id')] = $(this).val();
	});
	//post the profile
	$.post(
		$('form#FrmImpprofile').attr('action'),
		{nm:$('input#prfnm').val(),tbl:$('input#tbl').val(),map:mapping,prfop:'set'},
		function(response){
			if (response.success) {
				//add profile name to selector
				var xhtml = '<option value="' + response.data.id + '">' + response.data.value + '</option>';
				$('select#prfid').append(xhtml);
				dlgNotice('Saved your profile');
			} else {
				dlgError(response.msg);
			}
		}
	);
}
//retrieve profile
function _getProfile() {
	$.getJSON(
		$('form#FrmImpprofile').attr('action'),
		{id:$('select#prfid').val(),prfop:'get'},
		function(response){
			if (response.success) {
				//set the profile
				var map = response.data;
				for (var id in map) {
					$('div#impmap table.zend_form tr td select#'+id).val(map[id]);
				}
				dlgNotice('Retrieved your profile');
			} else {
				dlgError(response.msg);
			}
		}
	);
}