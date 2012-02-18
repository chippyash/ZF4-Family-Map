/**
 * Common jQGrid support functionality for TDH application
 */
function gridOnLoad()
{
	Cufon.refresh();
	// show the add/edit forms in popups
	// strip all button classes and replace them with 'button'
	$('.fm-button').removeClass().addClass('button');
}

function showEditRowButton(_btn_class)
{
	var btn_class = typeof _btn_class != 'undefined' ? '.'+_btn_class : '';
	jQuery('#btn-editRow'+btn_class).removeClass('disabled').removeAttr('disabled');
	jQuery('#btn-deleteRow'+btn_class).removeClass('disabled').removeAttr('disabled');
}

function hideEditRowButton(_btn_class)
{
	var btn_class = typeof _btn_class != 'undefined' ? '.'+_btn_class : '';
	jQuery('#btn-editRow'+btn_class).addClass('disabled').attr('disabled','disabled');
	jQuery('#btn-deleteRow'+btn_class).addClass('disabled').attr('disabled','disabled');
}

function afterShowForm_Add()
{
	afterShowForm_Generic();
}

function afterShowForm_Edit()
{
	afterShowForm_Generic();
}

function afterShowForm_Delete()
{
	afterShowForm_Generic();
}


function afterShowForm_Generic()
{
	applyAttributeClasses();
	// do various formatting
	var dialog = jQuery('.ui-jqdialog');
	// apply our styles to buttons
	dialog.find('#sData').addClass('button ok');
	dialog.find('#cData').addClass('button cancel');
	dialog.find('#eData').addClass('button cancel'); // delete cancel
	dialog.find('#dData').addClass('button ok'); // delete submit
	// remove icon tags
	dialog.find('#sData span.ui-icon, #cData span.ui-icon, #eData span.ui-icon, #dData span.ui-icon').remove();
	// style select menus
	//dialog.find('select').removeClass().addClass('uiselect').css('width','20em').selectmenu({style:'dropdown'});
	addButtonIcons();
	Cufon.refresh();

}

// configure markup, buttons, styles etc.
function reConfigure(jqGridId,addOptions,editOptions,delOptions,_btn_class)
{
	var grid = jQuery(jqGridId);
	var btn_class = typeof _btn_class == 'undefined' ? '' : '.'+_btn_class;
	
	// add row button
	jQuery("#btn-addRow"+btn_class).click(function(){
		grid.jqGrid('editGridRow','new',addOptions);
	});

	// edit row button
	jQuery("#btn-editRow"+btn_class).click(function(){
		var selectedRow = grid.jqGrid('getGridParam','selrow');
		if (selectedRow) {
			grid.jqGrid('editGridRow',grid.jqGrid('getRowData',selectedRow).id,editOptions);
		} else {
			simplePopupMessage({title:"Oops!",message:"Please select a row to edit.",'class':'negative'});
		}
	});

	// delete button
	jQuery("#btn-deleteRow"+btn_class).click(function(){
		var selectedRow = grid.jqGrid('getGridParam','selrow');
		if (selectedRow) {
			grid.jqGrid('delGridRow',grid.jqGrid('getRowData',selectedRow).id,delOptions);
		} else {
			simplePopupMessage({title:"Oops!",message:"Please select a row to edit.",'class':'negative'});
		}
	});

	// search button
	jQuery("#btn-search"+btn_class).click(function(){
		grid.jqGrid('searchGrid');
	});
}