var sn = Array();
sn.push('.title');
sn.push('h1');
sn.push('h2');
sn.push('h3');
sn.push('h4');
sn.push('.reportTable caption');
sn.push('.menu');
sn.push('label.tip');
sn.push('.ui-jqdialog-title');
sn.push('.ui-jqgrid-titlebar');

var sh = Array();
sh.push('#navigation > ul > li > a'); // requires jQuery selector engine
sh.push('#navigation span');
sh.push('.link');

var normal_selectors = sn.join(', ');
var hover_selectors = sh.join(', ');

Cufon.replace(normal_selectors, { fontFamily: 'Helvetica', hover: false }); 
Cufon.replace(hover_selectors, { fontFamily: 'Helvetica', hover: true });   


