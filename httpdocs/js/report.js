/* Support for control panel report screen */
// when dom loaded
jQuery(function(){
	$('#rptName').bind('change',function() {rptSwitchOn();});
	$('#period').bind('change',function() {rptChangeRange();});
	$('#from').dateEntry({spinnerImage: '/images/dateEntry/spinnerSquare.png', dateFormat:'dmy/',maxDate:new Date()});
	$('#to').dateEntry({spinnerImage: '/images/dateEntry/spinnerSquare.png', dateFormat:'dmy/',maxDate:new Date()});
	rptSwitchOn(); //switch on options for current report
	$('#fieldset-rightPane').show(); //show the right pane (switched off when form rendered)
});

function rptSwitchOff() {
	//switch of all parameters
	var id;
	for (i in _rptParms) {
		if (_rptParms[i].parm != '') {
			id = '#' + _rptParms[i].parm;
			$(id + '-label').hide();
			$(id + '-element').hide();
		}
	}
	//switch of display type radio
	$('#dispType-label').hide();
	$('#dispType-element').hide();
	//switch off date inputs
	$('#period-label').hide();
	$('#period-element').hide();
	$('#from-label').hide();
	$('#from-element').hide();
	$('#to-label').hide();
	$('#to-element').hide();
}

function rptSwitchOn() {
	rptSwitchOff(); //switch everything off
	var curr = $('#rptName').val();
	var def = _rptParms[curr];
	if (def.hasCsv) {
		$('#dispType-label').show();
		$('#dispType-element').show();
	}
	if (def.hasDate) {
		$('#period-label').show();
		$('#period-element').show();
		rptChangeRange();
	}
	if (def.parm != '') {
		var id = '#' + def.parm;
		$(id + '-label').show();
		$(id + '-element').show();
	}

}

function rptChangeRange() {
	var curr = $('#period').val();
	if (curr == 'range') {
		$('#from-label').show();
		$('#from-element').show();
		$('#to-label').show();
		$('#to-element').show();
	} else {
		$('#from-label').hide();
		$('#from-element').hide();
		$('#to-label').hide();
		$('#to-element').hide();
	}
}
