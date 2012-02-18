/* js for report index page */
var _dlgSaveQuery;
var _dlgError;
var _rpWidth;
var _mainWidth;
$(document).ready(function(){
	//set up report div
	$('div#rightPane').html('<div id="report"></div>').after('<div class="clear"></div>');
	//clear all check buttons to ALL
	$('.chkAll').each(function(idx,ele){setCheckAll(ele)});
	$('#panelLeft').accordion({autoHeight:false});
	//set up dialogs
	_dlgSaveQuery = $('#uiSaveReport').dialog({autoOpen:false,modal:true,buttons:{"Save":function(){_saveQuery(this);}}});
	_dlgError = $('#uiError').dialog({autoOpen:false,modal:true,resizeable:false,buttons:{"Ok":function(){$(this).dialog('close');}}})
	//save some measurements
	_rpWidth = $('div#rightPane').width();
	_mainWidth = $('div#main').width();
	//setup datepickers
	$.datepicker.setDefaults({
		altFormat:'yyyy-mm-dd',
		dateFormat:'dd/mm/yy',
		constrainInput:true,
		appendText:'dd/mm/yyyy'
	});
	$('.datepicker').datepicker();
});

//display an error message
function dlgError(msg) {
	$('#uiError p.#errMsg').html(msg);
	_dlgError.dialog('open');
}

//check box events
//an All check box was clicked
function setCheckAll(ele) {
	var relId = $(ele).attr('id');
	var group = $(ele).parent().parent().parent().parent().parent().parent().parent().attr('id');
	$(ele).fadeTo(1,1).next('span').css('color','black');
	$('#' + group + ' input[rel="'+relId+'"]').removeAttr('checked').fadeTo(1,0.5).next('span').css('color','grey');
	return true;
}
//a single element check box was clicked
function setCheckSingle(ele) {
	var relId = $(ele).attr('rel');
	var group = $(ele).parent().parent().parent().parent().parent().parent().parent().attr('id');
	$('#' + group + ' #'+relId).removeAttr('checked').fadeTo(1,0.5).next('span').css('color','grey');;
	$('#' + group + ' input[rel="'+relId+'"]').fadeTo(1,1).next('span').css('color','black');
	return true;
}
//run a report and display results
// rpt = ovl|mbr|srvc
// target = html|word|excel
function runReport(rpt,target) {
	//check to see if are runnable
	//if ($('#goBtn').attr('rel') == 'off') return;
	//collect data
	var gender = Array();
	var age = Array();
	var pcode = Array();
	var ethnicity = Array();
	var lang = Array();
	var cat = Array();
	var srvc = Array();
	var pupil = Array();
	var ovl = Array();
	var norpt = false;
	var startDt;
	var endDt;
	var splitDt;
	var attSrvc;
	var attDate;
	var enrSrvc;
	var fltExclude;
	switch(rpt) {
		case 'mbr' :
			$('div#mbrQ input:checked').each(function(idx,ele){
				var name = $(ele).attr('name');
				var val = $(ele).val();
				switch(name) {
					case 'mbrGender':
						gender.push(val);
						break;
					case 'mbrAge':
						age.push(val);
						break;
					case 'mbrPCode':
						pcode.push(val);
						break;
					case 'mbrEthnicity':
						ethnicity.push(val);
						break;
					case 'mbrLang':
						lang.push(val);
						break;
					case 'mbrPupil':
						pupil.push(val);
						break;
					case 'catCat':
						cat.push(val);
						break;
					case 'srvcSrvc':
						srvc.push(val);
						break;
					default:
						break;
				}
			});
			break;
		case 'ovl':
			$('div#ovlQ input:checked').each(function(idx,ele){
				var name = $(ele).attr('name');
				var val = $(ele).val();
				switch(name) {
					case 'ovlOvl':
						ovl.push(val);
						break;
					case 'mbrGender':
						gender.push(val);
						break;
					case 'mbrAge':
						age.push(val);
						break;
					case 'mbrPCode':
						pcode.push(val);
						break;
					case 'mbrEthnicity':
						ethnicity.push(val);
						break;
					case 'mbrLang':
						lang.push(val);
						break;
					case 'mbrPupil':
						pupil.push(val);
						break;
					case 'catCat':
						cat.push(val);
						break;
					case 'srvcSrvc':
						srvc.push(val);
						break;
					case 'fltExclude':
						fltExclude = val;
						break;
					default:
						break;
				}
			});
			break;
			
		case 'usg':
			$('div#usgQ input:checked').each(function(idx,ele){
				var name = $(ele).attr('name');
				var val = $(ele).val();
				switch(name) {
					case 'srvcSrvc':
						srvc.push(val);
						break;
					default:
						break;
				}
			});
			startDt = $('input[name="fromDt"]').val();
			endDt = $('input[name="toDt"]').val();
			splitDt = $('select[name="splitDt"]').val();
			break;

		case 'att':
			attSrvc = $('select#attSrvcSelect').val();
			attDate = $('select#attDateSelect').val();
			break;

		case 'enr':
			enrSrvc = $('select#enrSrvcSelect').val();
			break;
			
		case 'card':
			break;
		default:
			norpt = true;
			break;
			
	}
	if (!norpt) {
		//reset widths
		$('div#rightPane').css('width',_rpWidth);
		$('div#main').css('width',_mainWidth);
		$('div#wrap').css('width','1024px');

		//run query
		//If we a doing an html report, post to server using ajax
		if (target=='html') {
			$.post(
				'/report/run',
				{'format':'xml','gender':gender,'age':age,'pcode':pcode,'cat':cat,'srvc':srvc,'ethnicity':ethnicity,'lang':lang,'pupil':pupil,'ovl':ovl,'startDt':startDt,'endDt':endDt,'rpt':rpt,'splitDt':splitDt,'attSrvc':attSrvc,'attDate':attDate,'enrSrvc':enrSrvc,'fltExclude':fltExclude},
				function(result){
					//$('img#rptWaiting').toggleClass('noshow');
					//$('div#report').toggleClass('noshow');
					$('div#report').html(result);
					var tw = $('table.rptTable').width();
					var rpw = $('div#rightPane').width();
					if (tw > _rpWidth) {
						var expand = (tw - _rpWidth) * 1.68;
						$('div#rightPane').css('width',tw);
						$('div#main').css('width',$('div#main').width() + expand);
						$('div#wrap').css('width',$('div#wrap').width() + expand);
					}
					$('img#saveBtn').show();
				},
				'html'
			);
		} else {
			//do redirection for a file download using post
			//we have to use a hidden form to do this.
			$('#dnlForm input[name="format"]').val(target);
			$('#dnlForm input[name="gender"]').val(gender);
			$('#dnlForm input[name="age"]').val(age);
			$('#dnlForm input[name="pcode"]').val(pcode);
			$('#dnlForm input[name="cat"]').val(cat);
			$('#dnlForm input[name="srvc"]').val(srvc);
			$('#dnlForm input[name="ethnicity"]').val(ethnicity);
			$('#dnlForm input[name="lang"]').val(lang);
			$('#dnlForm input[name="pupil"]').val(pupil);
			$('#dnlForm input[name="ovl"]').val(ovl);
			$('#dnlForm input[name="startDt"]').val(startDt);
			$('#dnlForm input[name="endDt"]').val(endDt);
			$('#dnlForm input[name="rpt"]').val(rpt);
			$('#dnlForm input[name="splitDt"]').val(splitDt);
			$('#dnlForm input[name="attSrvc"]').val(attSrvc);
			$('#dnlForm input[name="attDate"]').val(attDate);
			$('#dnlForm input[name="enrSrvc"]').val(enrSrvc);
			$('#dnlForm input[name="fltExclude"]').val(fltExclude);
			$('#dnlForm').submit();
		}
	}
}//end func

//run a saved query
function runSaved(target) {
	$('img#saveBtn').hide();
	var val = $('#saveSelect option:selected').val();
	//reconfigure format types
	if (target=='html') target='xml';
	//run query
	$.post(
		'/report/runsaved',
		{'format':target,'id':val},
		function(result){
			//$('img#rptWaiting').toggleClass('noshow');
			//$('div#report').toggleClass('noshow');
			$('div#report').html(result);
			var tw = $('table.rptTable').width();
			var rpw = $('div#rightPane').width();
			if (tw > _rpWidth) {
				var expand = (tw - _rpWidth) * 1.68;
				$('div#rightPane').css('width',tw);
				$('div#main').css('width',$('div#main').width() + expand);
				$('div#wrap').css('width',$('div#wrap').width() + expand);
			}
			$('img#saveBtn').show();
		},
		'html'
	);	
}
//save the current report
function saveFilter() {
	$('img#saveBtn').hide();
	_dlgSaveQuery.dialog('open');
}
//runs when user clicks save button on save report dialog
function _saveQuery(dlg) {
	var nm = $('#sqname').val();
	if (nm == '') {
		dlgError('Name is required');
		return false;
	}
	var desc = $('#sqdesc').val();
	$(dlg).dialog('close');
	$.post(
		'/report/save',
		{'format':'json','name':nm,'desc':desc},
		function(result){
			if (result.success) {
				var options = $('#saveSelect').attr('options');
				options[options.length] = new Option(result.data.name,result.data.id);
			} else {
				dlgError(result.msg);
			}
		},
		'json'
		
	);
	return true;
}

//attendance report support
function _attFetchDates(ele) {
	$.getJSON(
		'/report/sel',
		{sel:'srvcdate',srvc:$(ele).val()},
		function(response) {
			if (response.success) {
				var xhtml = '';
				if (response.data.length == 0) {
					var xLble = 'Sorry - no dates available';
				} else {
					var xLble = 'Please select date(s)';
					for (var id in response.data) {
						xhtml += '<option value="' + id + '">' + response.data[id] + '&nbsp;</option>';
					}
				}
				$('select#attDateSelect').html(xhtml);
				$('label[for="attDateSelect"]').html(xLble);
			} else {
				dlgError(result.msg);
			}
		}
	);
}