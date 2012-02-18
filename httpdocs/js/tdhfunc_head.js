/**
 * tdhfunc_head.js
 *
 * General jQuery scripts required for Triumph Data Hub site
 */
//when dom loaded


//////////////////////////////////////////////////////////////////////////////////////////
// DEFINITIONS
//////////////////////////////////////////////////////////////////////////////////////////
			
// create array of popup options for fancybox
var popupOptions = new Array();

popupOptions['shared'] = {
	'speedIn'			: 0 			//
	,'speedOut' 		: 0 			// 	Speed of the fade and elastic transitions, in milliseconds
	,'transitionIn'		: 'fade' 		//
	,'transitionOut' 	: 'fade' 		// 	The transition type. Can be set to 'elastic', 'fade' or 'none'
	,showCloseButton 	: false 		// 	Toggle close button
	,'padding'			: 30
	,onComplete			: function(){popupOnCompleteFunctions();}
};

popupOptions['default'] = {
};

var brandSelectIcons = [
	{find:'.all-brands',icon:'all-brands'}
	,{find:'.brand',icon:'brand'}
	,{find:'.triumph',icon:'triumph'}
	,{find:'.sloggi',icon:'sloggi'}
	,{find:'.valisere',icon:'valisere'}
	,{find:'.hom',icon:'hom'}
];
var miscSelectIcons = [
	{find:'.loading',icon:'loading'}				   
];
var propertySelectIcons = [{find:'.property',icon:'property'}];
var campaignSelectIcons = [{find:'.campaign',icon:'campaign'}];
var filterOptionIcons = [
	{find:'.add',icon:'icon-add'}
	,{find:'.age',icon:'icon-age'}
	,{find:'.gender',icon:'icon-gender'}
	,{find:'.country',icon:'icon-country'}
	,{find:'.continent',icon:'icon-country'}
	,{find:'.created',icon:'icon-created'}
	,{find:'.opt-in',icon:'icon-opt-in'}
	,{find:'.tp-opt-in',icon:'icon-tp-opt-in'}
	,{find:'.firstname',icon:'icon-firstname'}
	,{find:'.surname',icon:'icon-surname'}
	,{find:'.email',icon:'icon-email'}
	,{find:'.postal',icon:'icon-postal'}
	,{find:'.phone',icon:'icon-phone'}
	,{find:'.male',icon:'icon-male'}
	,{find:'.female',icon:'icon-female'}
	,{find:'.yes',icon:'icon-yes'}
	,{find:'.no',icon:'icon-no'}
	,{find:'.bra',icon:'icon-bra'}
	,{find:'.brief',icon:'icon-brief'}
	,{find:'.store',icon:'icon-store'}
	,{find:'.winner',icon:'icon-winner'}
	,{find:'.campaign-property',icon:'icon-campaign-property'}
];
var insightSelectIcons = filterOptionIcons;
//////////////////////////////////////////////////////////////////////////////////////////
// FUNCTIONS /////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////

function uniqid()
{
	var newDate = new Date;
	return newDate.getTime();
}

function centerHorz(id)
{
	if(typeof $.browser.msie == 'undefined' || $.browser.msie==false){
		var left = (window.innerWidth/2) - ($(id).width()/2);
		$(id).css('left',left + 'px');
	}
}

function addButtonIcons(){
	// add icon tags to buttons (this is to allow CSS to deal with backgrounds instead of having icons in background)
	$('.button, button, .imgLink').each(function(){
		addButtonIcon(this);
	});
}
function addButtonIcon(button)
{
	if($(button).find('span.icon').length==0){
		//wrap the text node in a span first to aid styling
		$(button).contents().wrap('<span class="label" />');
		$(button).prepend('<span class="icon" />');
	}
}

function popup(contentHtml,_opts){
	
	var opts = typeof _opts !== 'undefined' ? _opts : {};
	opts.content = contentHtml;
	opts = $.extend(popupOptions['shared'],opts);
			
	$.fancybox(opts);
}

function popupOnCompleteFunctions(){
	Cufon.refresh();
	addButtonIcons();
	$('.closePopup').click(function(){closePopup();});
}

function getPopupContent(html,options)
{
	var content = html;
	var buttons = '';
	if(typeof options.buttons == 'array'){
		for(var i=0;i<buttons.length;i++){
			// not finished
		}
	}
}

function simplePopupMessage(params)
{
	var p = $.extend({
		title:		'',
		label:		'Close',
		button:		true,
		message:	'',
		'class':	'positive'
	},params);
		
	buttonMarkup = p.button ? '<button type="button" class="ok" onclick="$.fancybox.close();">'+p.label+'</button>' : '';
	
	$.fancybox($.extend(popupOptions['shared'],{
		content				: '<h2 class="'+p['class']+'">'+p.title+'</h2><p>'+p.message+'</p>' + buttonMarkup
		,showCloseButton	: false
		,onComplete			: function(){popupOnCompleteFunctions();}
		,scrolling			: false
	}));
}

function pleaseWait(message)
{
	message = typeof message=='undefined' ? "Loading data..." : message;
	$.jGrowl(message,{header:'Please Wait...',corners:'0',sticky:true});

	//simplePopupMessage({message:'<p><img src="/images/loading_animation.gif" /></p><p>Loading data...</p>',button:false})
}
function hidePleaseWait()
{
	//$('div.jGrowl-notification .close').trigger('click');
	$('body').jGrowl("close");
}

function closePopup()
{
	$.fancybox.close();
}

function toObj(a)
{
  var o = {};
  for(var i=0;i<a.length;i++)
  {
    o[a[i]]='';
  }
  return o;
}

function classNameFromAttr(e,attrName)
{
	e.addClass(e.attr(attrName).split(' ').join('-').toLowerCase());	
}

function applyPopupToLink(linkElement)
{
	var opts = {};
	// check if each element has an id
	var href = $(linkElement).attr('href');
	if(href !== '' && typeof href !== 'undefined'){
		// get options from popupOptions array with same index as element href eg: popupOptions['#mydiv']
		var opts = typeof popupOptions[href] !== 'undefined' ? popupOptions[href] : {};
		// combine options with shared options
		opts = $.extend(popupOptions['shared'],opts);
	}
	// apply fancybox
	$(linkElement).fancybox(opts);	
}

// turn a ui.selectmenu into a single item select with a loading animation, waiting for new content
function uiSelectLoading(selector){
	$(selector).html('<option class="loading">Loading...</option>');
	var opts = $(selector).selectmenu('option');
	opts.icons = miscSelectIcons;
	$(selector).selectmenu('destroy').selectmenu(opts);
}

function doTooltips(){
	$("[title]").tooltip({layout:'<div><span class="arrow"></span></div>'});
}

function applyAttributeClasses()
{
	$('input[type]').each(function(){
		$(this).addClass('inputType-'+$(this).attr('type'));
		if($(this).attr('readonly')) $(this).addClass('inputReadOnly');
	});
}

function enableButtonsOnData()
{
	$('.enableButtonOnData').keyup(function()
	{
		var button = $('#'+$(this).attr('rel'));
		// if all fields with this class (enableButtonOnData) AND the same rel are not empty
		if($(this).val()!=''){//$('input[rel='+$(this).attr('rel')+'].enableButtonOnData').val() != ''){
			button.removeClass('disabled').removeAttr('disabled');
		}else{
			button.addClass('disabled').attr('disabled','disabled');
		}
	});
}

//////////////////////////////////////////////////////////////////////////////////////////
// ON LOAD
//////////////////////////////////////////////////////////////////////////////////////////


$(document).ready(function($){
	
	$('#content').show('fade',1000);
	
	// add icons to buttons
	addButtonIcons();
	
	// wrap divs around .divwrap content (for simple coding convenience)
	$('.divwrap').each(function(){
		$(this).wrap('<div />');
		$(this).parent().attr('id',$(this).attr('id')+'_container');
	});
	
	// apply .parent classes to appropriate navigation links (anything before a <ul> is a parent link or label)
	$('#navigation ul').prev().addClass('parent');
	
	// highlight navigation links
	var currentLink = '#navigation a[href='+location.pathname+']';
	$(currentLink).addClass('selected');
	$(currentLink).parent().parent().prev().addClass('selected');
		
	// apply fancybox to all elements with 'popup' class
	$("a.popup, button.popup").each(function(){
		applyPopupToLink(this);
	});
	
	// group .zend_form <DT> and <DD> tags into divs for easier CSS
	$('.zend_form, .sto_form').append('<div class="form_elements"></div>');
	var dds = $('.zend_form, .sto_form').children('dd');
	dds.each(function(i){
		var thisId = $(this).prev().attr('id'); // actually uses the id of the <dt> tag as it's missing from the <dd> on file upload fields
		var formElementId = thisId.substr(0,thisId.indexOf('-'));
		// make sure there is an id on the <dd> tag
		if($(this).attr('id')=='') $(this).attr('id',formElementId+'-element');
		containerId = formElementId + '-container';
		newDiv = $(this).siblings('.form_elements');
		firstlast = i==0 ? 'first' : '';
		firstlast = i==dds.length-1 ? 'last' : firstlast;
		dt = $(this).prev().remove(); // remove the dt
		dd = $(this).remove(); // remove the dd
		// wrap the dt and put it in the new container
		newDiv.append(dt.wrap('<div id="'+ containerId +'" class="element '+firstlast+'"></div>').parent());
		// put the dd in the same container as the dt
		$('#'+containerId).append(dd);		
	});
	
	// bind click event to errors icon
	$('ul.errors').click(function(){
		popup('<div class="popup small"><h2>Error</h2><ul class="errorList">'+$(this).html()+'</ul><div class="controls"><button type="button" class="button ok closePopup">Close</button></div></div>');
	});
	// put error text in the title of the <li> so it can be shown as a tooltip
	$('ul.errors li').each(function(){
		var errorText = $(this).html();
		$(this).parent().attr('title',$(this).attr('title') + "\n" + errorText);								
	});
	
	// show a popup when there are errors
	if($('ul.errors').length > 0){
		simplePopupMessage({title:'Oh dear...',message:'There were problems submitting the form,<br>please click on the <span class="icon error"></span>icons to find out more.'});
	}
	
	// bind tab functions
	$('.tab').each(function(){
		$(this).click(function(){
			// go through all tabs in same parent and hide their content
			$(this).parent().children('.tab').each(function(){
				// switch this tab off
				$(this).removeClass('selected');
				// hide content
				$('#'+$(this).attr('rel')).hide();
			});
			// switch this tab on
			$(this).addClass('selected');
			// show content (by matching the div id with the rel attribute of this tag
			$('#'+$(this).attr('rel')).show();
		});
	});
		
	
	// prevent default action of disabled links
	// * may not work, not in use
	$('a.button').click(function(e){
		if($(this).hasClass('disabled'))
		{
			e.preventDefault();
			return false;
		}
	});
	
	// set logout button animation
	$('#headerInfo a.logout').click(function(){$(this).slideUp();});	
	
	// enable buttons when a field is not empty
	// to use this, you need a field like: <input type="text" class="enableButtonOnData" rel="myButton" />
	enableButtonsOnData();
	
	// show tooltips
	doTooltips();
	
	// apply classes to inputs with certain attributes
	applyAttributeClasses();
	
	
/**/	
});
