/**
 * WLC system standard functions
 */

/** POPUP - Relies on jquery.FancyBox and jquery.jgrowl **/
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

popupOptions['modal'] = {
	modal		:true,
	scrolling	: false,
	titleShow	: true,
	titlePosition : 'inside',
	showCloseButton	: true
	,'speedIn'			: 0 			//
	,'speedOut' 		: 0 			// 	Speed of the fade and elastic transitions, in milliseconds
	,'transitionIn'		: 'fade' 		//
	,'transitionOut' 	: 'fade' 		// 	The transition type. Can be set to 'elastic', 'fade' or 'none'
	,'padding'			: 30
	,onComplete			: function(){popupOnCompleteFunctions();}
}

function popup(contentHtml,_opts){	
	var opts = typeof _opts !== 'undefined' ? _opts : {};
	opts.content = contentHtml;
	opts = $.extend(opts,popupOptions['shared']);
			
	$.fancybox(opts);
}

function popupOnCompleteFunctions(){
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

function goLogout(){window.location='/user/logout';}
$(document).ready(function(){
	$('span.userlogoff img').hover(
		function(){
			$(this).fadeTo(500,0.5);
		},
		function(){
			$(this).fadeTo(500,1);
		}
	);
});