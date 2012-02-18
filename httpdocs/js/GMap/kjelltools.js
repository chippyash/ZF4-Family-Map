// This application is provided by Kjell Scharning
//  Licensed under the Apache License, Version 2.0;
//  http://www.apache.org/licenses/LICENSE-2.0
//  @see http://www.birdtheme.org/useful/v3tool.html

function _kjGob(e){if(typeof(e)=='object')return(e);if(document.getElementById)return(document.getElementById(e));return(eval(e))}
var _kjMap;
var _kjPolyShape;
var _kjOldShape;
//var tmpPolyLine;
var _kjDrawnShapes = [];
var _kjHoleShapes = [];
var _kjStartMarker;
var _kjNeMarker;
var _kjMarkers = [];
var _kjMidMarkers = [];
var _kjMarkerListener1;
var _kjMarkerListener2;
var _kjRectangle;
var _kjCircle;
var _kjSouthWest;
var _kjNorthEast;
var _kjCenterPoint;
var _kjRadiusPoint;
var _kjCalc;
var _kjStartPoint;
var _kjPolyPoints = [];
var _kjPointsArray = [];
var _kjToolId = 1;
var _kjCodeId = 1;
var _kjShapeId = 0;
var _kjAdder = 0;
var _kjPlmcur = 0;
var _kjLcur = 0;
var _kjPcur = 0;
var _kjRcur = 0;
var _kjCcur = 0;
var _kjOuterPoints = [];
var _kjHolePolyArray = [];
var _kjOuterShape;
var _kjAnotherHole = false;
var _kjIt;
var _kjOuterArray = [];
var _kjInnerArray = [];
var _kjInnerArrays = [];
var _kjPlacemarks = [];
var _kjMyListener;
var _kjEditing = false;
var _kjKmlcode = "";
var _kjJavacode = "";
var _kjPolylineDecColorCur = "255,0,0";
var _kjPolygonDecColorCur = "255,0,0";
var _kjDocuname = "My document";
var _kjDocudesc = "Content";
var _kjPolylineStyles = [];
var _kjPolygonStyles = [];
var _kjRectangleStyles = [];
var _kjCircleStyles = [];
var _kjImageNormal = new google.maps.MarkerImage(
	"/images/GMap/kjelltools/square.png",
	new google.maps.Size(11, 11),
	new google.maps.Point(0, 0),
	new google.maps.Point(6, 6)
);
var _kjImageHover = new google.maps.MarkerImage(
	"/images/GMap/kjelltools/square_over.png",
	new google.maps.Size(11, 11),
	new google.maps.Point(0, 0),
	new google.maps.Point(6, 6)
);
var _kjImageNormalMidpoint = new google.maps.MarkerImage(
	"/images/GMap/kjelltools/square_transparent.png",
	new google.maps.Size(11, 11),
	new google.maps.Point(0, 0),
	new google.maps.Point(6, 6)
);
/*var _kjImageHoverMidpoint = new google.maps.MarkerImage(
	"/images/GMap/kjelltools/square_transparent_over.png",
	new google.maps.Size(11, 11),
	new google.maps.Point(0, 0),
	new google.maps.Point(6, 6)
);*/

function _kjfnPolystyle() {
    this.name = "Lump";
    this.kmlcolor = "660000FF";
    this.kmlfill = "660000FF";
    this.color = "#FF0000";
    this.fill = "#FF0000";
    this.width = 2;
    this.lineopac = 1;
    this.fillopac = 0.4;
}
function _kjfnLinestyle() {
    this.name = "Path";
    this.kmlcolor = "660000FF";
    this.color = "#FF0000";
    this.width = 3;
    this.lineopac = 1;
}
function _kjfnRectanglestyle() {
    this.name = "Rec";
    this.kmlcolor = "CD0000FF";
    this.kmlfill = "9AFF0000";
    this.color = "#FF0000";
    this.fill = "#0000FF";
    this.width = 2;
    this.lineopac = 0.8;
    this.fillopac = 0.6;
}
function _kjfnPolystyle() {
    this.name = "Circ";
    this.color = "#FF0000";
    this.fill = "#0000FF";
    this.width = 2;
    this.lineopac = 0.8;
    this.fillopac = 0.6;
}
function _kjfnPlacemarkobject() {
    this.name = "NAME";
    this.desc = "YES";
    this.style = "";
    this.tess = 1;
    this.alt = "clampToGround";
    this.plmtext = "";
    this.jstext = "";
    this.code = [];
    this.poly = "pl";
    this.shape = null;
    this.point = null;
    this._kjToolId = 1;
    this.hole = 0;
    this.ID = 0;
}
function _kjfnCreateplacemarkobject() {
    var thisplacemark = new _kjfnPlacemarkobject();
    _kjPlacemarks.push(thisplacemark);
}
function _kjfnCreatepolygonstyleobject() {
    var polygonstyle = new _kjfnPolystyle();
    _kjPolygonStyles.push(polygonstyle);
}
function _kjfnCreatelinestyleobject() {
    var polylinestyle = new _kjfnLinestyle();
    _kjPolylineStyles.push(polylinestyle);
}
function _kjfnCreaterectanglestyleobject() {
    var recstyle = new _kjfnRectanglestyle();
    _kjRectangleStyles.push(recstyle);
}
function _kjfnCreatecirclestyleobject() {
    var cirstyle = new _kjfnPolystyle();
    _kjCircleStyles.push(cirstyle);
}
/** 
 * Initialise the _kjMap for drawing 
 * @param gMap _kjMap to initialise
 */
function _kjfnInitmap(gMap){
    _kjMap = gMap;
    _kjPolyPoints = new google.maps.MVCArray(); // collects coordinates
    _kjfnCreateplacemarkobject();
    _kjfnCreatelinestyleobject();
    _kjfnCreatepolygonstyleobject();
    _kjfnCreaterectanglestyleobject();
    _kjfnCreatecirclestyleobject();
    _kjfnPreparePolyline(); // create a Polyline object
    _kjMyListener = google.maps.event.addListener(_kjMap, 'click', _kjfnAddLatLng);
    google.maps.event.addListener(_kjMap,'zoom_changed',_kjfnMapZoom);
    /* This adds an x,y coord display
    google.maps.event.addListener(_kjMap,'mousemove',function(point){
            var LnglatStr6 = point.latLng.lng().toFixed(6) + ', ' + point.latLng.lat().toFixed(6);
            var latLngStr6 = point.latLng.lat().toFixed(6) + ', ' + point.latLng.lng().toFixed(6);
            _kjGob('over').options[0].text = LnglatStr6;
            _kjGob('over').options[1].text = latLngStr6;
            });
    */
}

function _kjfnPreparePolyline(){
    var polyOptions = {
        path: _kjPolyPoints,
        strokeColor: _kjPolylineStyles[_kjLcur].color,
        strokeOpacity: _kjPolylineStyles[_kjLcur].lineopac,
        strokeWeight: _kjPolylineStyles[_kjLcur].width};
    _kjPolyShape = new google.maps.Polyline(polyOptions);
    _kjPolyShape.setMap(_kjMap);
    /*var tmpPolyOptions = {
    	strokeColor: _kjPolylineStyles[_kjLcur].color,
    	strokeOpacity: _kjPolylineStyles[_kjLcur].lineopac,
    	strokeWeight: _kjPolylineStyles[_kjLcur].width
    };
    tmpPolyLine = new google.maps.Polyline(tmpPolyOptions);
    tmpPolyLine.setMap(_kjMap);*/
}

function _kjfnPreparePolygon(){
    var polyOptions = {
        path: _kjPolyPoints,
        strokeColor: _kjPolygonStyles[_kjPcur].color,
        strokeOpacity: _kjPolygonStyles[_kjPcur].lineopac,
        strokeWeight: _kjPolygonStyles[_kjPcur].width,
        fillColor: _kjPolygonStyles[_kjPcur].fill,
        fillOpacity: _kjPolygonStyles[_kjPcur].fillopac};
    _kjPolyShape = new google.maps.Polygon(polyOptions);
    _kjPolyShape.setMap(_kjMap);
}
function _kjfnActivateRectangle() {
    _kjRectangle = new google.maps.Rectangle({
        map: _kjMap,
        fillColor: _kjRectangleStyles[_kjRcur].fill,
        fillOpacity: _kjRectangleStyles[_kjRcur].fillopac,
        strokeColor: _kjRectangleStyles[_kjRcur].color,
        strokeOpacity: _kjRectangleStyles[_kjRcur].lineopac,
        strokeWeight: _kjRectangleStyles[_kjRcur].width
        });
}
function _kjfnActivateCircle() {
    _kjCircle = new google.maps.Circle({
        map: _kjMap,
        fillColor: _kjCircleStyles[_kjCcur].fill,
        fillOpacity: _kjCircleStyles[_kjCcur].fillopac,
        strokeColor: _kjCircleStyles[_kjCcur].color,
        strokeOpacity: _kjCircleStyles[_kjCcur].lineopac,
        strokeWeight: _kjCircleStyles[_kjCcur].width
        });
}
function _kjfnAddLatLng(point){
    if(_kjPlmcur != _kjPlacemarks.length-1) {
        _kjNextShape();
    }
    // _kjRectangle and _kjCircle can't collect points with getPath. solved by letting Polyline collect the points and then erase Polyline
    _kjPolyPoints = _kjPolyShape.getPath();
    _kjPolyPoints.insertAt(_kjPolyPoints.length, point.latLng); // or: _kjPolyPoints.push(point.latLng)
    if(_kjPolyPoints.length == 1) {
        _kjStartPoint = point.latLng;
        _kjPlacemarks[_kjPlmcur].point = _kjStartPoint;
        setstartMarker(_kjStartPoint);
    }
    if(_kjPolyPoints.length == 2 && _kjToolId == 3) createrectangle(point);
    if(_kjPolyPoints.length == 2 && _kjToolId == 4) createcircle(point);
    if(_kjToolId == 1 || _kjToolId == 2) {
        var stringtobesaved = point.latLng.lat().toFixed(6) + ',' + point.latLng.lng().toFixed(6);
        if(_kjAdder == 0) {
            _kjPointsArray.push(stringtobesaved);
            if(_kjPolyPoints.length == 1 && _kjToolId == 2) closethis('polygonstuff');
            if(_kjCodeId == 1 && _kjToolId == 1) logCode1(); // write kml for polyline
            if(_kjCodeId == 1 && _kjToolId == 2) logCode2(); // write kml for polygon
            if(_kjCodeId == 2) logCode4(); // write Google javascript
            if(_kjCodeId == 3) logCode8(); // write Bing javascript
        }
        if(_kjAdder == 1) _kjOuterArray.push(stringtobesaved);
        if(_kjAdder == 2) _kjInnerArray.push(stringtobesaved);
    }
}

function setstartMarker(point){
    _kjStartMarker = new google.maps.Marker({
        position: point,
        _kjMap: _kjMap});
    _kjStartMarker.setTitle("#" + _kjPolyPoints.length);
}
function createrectangle(point) {
    // _kjStartMarker is _kjSouthWest point. now set _kjNorthEast
    _kjNeMarker = new google.maps.Marker({
        position: point.latLng,
        draggable: true,
        title: "Draggable",
        _kjMap: _kjMap});
    _kjMarkerListener1 = google.maps.event.addListener(_kjStartMarker, 'drag', drawRectangle);
    _kjMarkerListener2 = google.maps.event.addListener(_kjNeMarker, 'drag', drawRectangle);
    _kjStartMarker.setDraggable(true);
    _kjStartMarker.setTitle("Draggable");
    drawRectangle();
    _kjPolyShape.setMap(null); // remove the Polyline that has collected the points
    _kjPolyPoints = [];
}
function drawRectangle() {
    _kjSouthWest = _kjStartMarker.getPosition(); // used in logCode6()
    _kjNorthEast = _kjNeMarker.getPosition(); // used in logCode6()
    var latLngBounds = new google.maps.LatLngBounds(
        _kjSouthWest,
        _kjNorthEast
        );
    _kjRectangle.setBounds(latLngBounds);
    // the _kjRectangle was created in _kjfnActivateRectangle(), called from newstart(), which may have been called from setTool()
    //_kjPlacemarks[_kjPlmcur].style = _kjRectangleStyles[_kjRcur].name;
    if(_kjCodeId == 3) _kjCodeId = _kjGob('codechoice').value = 1;
    logCode6();
}
function createcircle(point) {
    // _kjStartMarker is center point. now set radius
    _kjNeMarker = new google.maps.Marker({
        position: point.latLng,
        draggable: true,
        title: "Draggable",
        _kjMap: _kjMap});
    _kjMarkerListener1 = google.maps.event.addListener(_kjStartMarker, 'drag', drawCircle);
    _kjMarkerListener2 = google.maps.event.addListener(_kjNeMarker, 'drag', drawCircle);
    _kjStartMarker.setDraggable(true);
    _kjStartMarker.setTitle("Draggable");
    drawCircle();
    _kjPolyShape.setMap(null); // remove the Polyline that has collected the points
    _kjPolyPoints = [];
}
function drawCircle() {
    _kjCenterPoint = _kjStartMarker.getPosition();
    _kjRadiusPoint = _kjNeMarker.getPosition();
    _kjCircle.bindTo('center', _kjStartMarker, 'position');
    _kjCalc = distance(_kjCenterPoint.lat(),_kjCenterPoint.lng(),_kjRadiusPoint.lat(),_kjRadiusPoint.lng());
    _kjCircle.setRadius(_kjCalc);
    //_kjPlacemarks[_kjPlmcur].style = _kjCircleStyles[_kjCcur].name;
    _kjCodeId = _kjGob('codechoice').value = 2;
    logCode7();
}
// calculate distance between two coordinates
function distance(lat1,lon1,lat2,lon2) {
    var R = 6371000; // earth's radius in meters
    var dLat = (lat2-lat1) * Math.PI / 180;
    var dLon = (lon2-lon1) * Math.PI / 180;
    var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
    Math.cos(lat1 * Math.PI / 180 ) * Math.cos(lat2 * Math.PI / 180 ) *
    Math.sin(dLon/2) * Math.sin(dLon/2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    var d = R * c;
    return d;
}
function kmlheading() {
    var heading = "";
    var styleforpolygon = "";
    var styleforrectangle = "";
    var styleforpolyline = "";
    var i;
    heading = '<?xml version="1.0" encoding="UTF-8"?>\n' +
        '<kml xmlns="http://www.opengis.net/kml/2.2">\n' +
        '<Document><name>'+_kjDocuname+'</name>\n' +
        '<description>'+_kjDocudesc+'</description>\n';
    for(i=0;i<_kjPolygonStyles.length;i++) {
        styleforpolygon += '<Style id="'+_kjPolygonStyles[i].name+'">\n' +
        '<LineStyle><color>'+_kjPolygonStyles[i].kmlcolor+'</color><width>'+_kjPolygonStyles[i].width+'</width></LineStyle>\n' +
        '<PolyStyle><color>'+_kjPolygonStyles[i].kmlfill+'</color></PolyStyle>\n' +
        '</Style>\n';
    }
    for(i=0;i<_kjRectangleStyles.length;i++) {
        styleforrectangle += '<Style id="'+_kjRectangleStyles[i].name+'">\n' +
        '<LineStyle><color>'+_kjRectangleStyles[i].kmlcolor+'</color><width>'+_kjRectangleStyles[i].width+'</width></LineStyle>\n' +
        '<PolyStyle><color>'+_kjRectangleStyles[i].kmlfill+'</color></PolyStyle>\n' +
        '</Style>\n';
    }
    for(i=0;i<_kjPolylineStyles.length;i++) {
        styleforpolyline += '<Style id="'+_kjPolylineStyles[i].name+'">\n' +
        '<LineStyle><color>'+_kjPolylineStyles[i].kmlcolor+'</color><width>'+_kjPolylineStyles[i].width+'</width></LineStyle>\n' +
        '</Style>\n';
    }
    return heading+styleforpolygon+styleforrectangle+styleforpolyline;
}
function kmlend() {
    var ending;
    return ending = '</Document>\n</kml>';
}
// write kml for polyline in text area
function logCode1(){
    var code = "";
    var kmltext1 = '<Placemark><name>'+_kjPlacemarks[_kjPlmcur].name+'</name>\n' +
                    '<description>'+_kjPlacemarks[_kjPlmcur].desc+'</description>\n' +
                    '<styleUrl>#'+_kjPolylineStyles[_kjLcur].name+'</styleUrl>\n' +
                    '<LineString>\n<tessellate>'+_kjPlacemarks[_kjPlmcur].tess+'</tessellate>\n' +
                    '<altitudeMode>'+_kjPlacemarks[_kjPlmcur].alt+'</altitudeMode>\n<coordinates>\n';
    if(_kjPointsArray.length != 0) {
        for(var i = 0; i < _kjPointsArray.length; i++) {
            code += _kjPointsArray[i] + ',0\n';
        }
        _kjPlacemarks[_kjPlmcur].code = _kjPointsArray;
    }/*else{
        code = _kjPlacemarks[_kjPlmcur].code;
    }*/
    kmltext2 = '</coordinates>\n</LineString>\n</Placemark>\n';
    _kjPlacemarks[_kjPlmcur].plmtext = _kjKmlcode = kmltext1+code+kmltext2;
    _kjPlacemarks[_kjPlmcur].poly = "pl";
    _kjGob('coords1').value = kmlheading()+kmltext1+code+kmltext2+kmlend();
}
// write kml for polygon in text area
function logCode2(){
    var code = "";
    var kmltext1 = '<Placemark><name>'+_kjPlacemarks[_kjPlmcur].name+'</name>\n' +
                    '<description>'+_kjPlacemarks[_kjPlmcur].desc+'</description>\n' +
                    '<styleUrl>#'+_kjPolygonStyles[_kjPcur].name+'</styleUrl>\n' +
                    '<Polygon>\n<tessellate>'+_kjPlacemarks[_kjPlmcur].tess+'</tessellate>\n' +
                    '<altitudeMode>'+_kjPlacemarks[_kjPlmcur].alt+'</altitudeMode>\n' +
                    '<outerBoundaryIs><LinearRing><coordinates>\n';
    if(_kjPointsArray.length != 0) {
        for(var i = 0; i < _kjPointsArray.length; i++) {
            code += _kjPointsArray[i] + ',0\n';
        }
        code += _kjPointsArray[0] + ',0\n';
        _kjPlacemarks[_kjPlmcur].code = _kjPointsArray;
    }/*else{
        code = _kjPlacemarks[_kjPlmcur].code;
    }*/
    //kmltext += _kjPointsArray[0] + ',0\n';
    kmltext2 = '</coordinates></LinearRing></outerBoundaryIs>\n</Polygon>\n</Placemark>\n';
    _kjPlacemarks[_kjPlmcur].plmtext = _kjKmlcode = kmltext1+code+kmltext2;
    _kjPlacemarks[_kjPlmcur].poly = "pg";
    _kjGob('coords1').value = kmlheading()+kmltext1+code+kmltext2+kmlend();
}
// write kml for polygon with hole
function logCode3(){
    var kmltext = '<Placemark><name>'+_kjPlacemarks[_kjPlmcur].name+'</name>\n' +
                    '<description>'+_kjPlacemarks[_kjPlmcur].desc+'</description>\n' +
                    '<styleUrl>#'+_kjPolygonStyles[_kjPcur].name+'</styleUrl>\n' +
                    '<Polygon>\n<tessellate>'+_kjPlacemarks[_kjPlmcur].tess+'</tessellate>\n' +
                    '<altitudeMode>'+_kjPlacemarks[_kjPlmcur].alt+'</altitudeMode>\n' +
                    '<outerBoundaryIs><LinearRing><coordinates>\n';
    for(var i = 0; i < _kjOuterArray.length; i++) {
        kmltext += _kjOuterArray[i]+',0\n';
    }
    kmltext += _kjOuterArray[0]+',0\n';
    kmltext += '</coordinates></LinearRing></outerBoundaryIs>\n';
    for(var m = 0; m < _kjInnerArrays.length; m++) {
        kmltext += '<innerBoundaryIs><LinearRing><coordinates>\n';
        for(var i = 0; i < _kjInnerArrays[m].length; i++) {
            kmltext += _kjInnerArrays[m][i]+',0\n';
        }
        kmltext += _kjInnerArrays[m][0]+',0\n';
        kmltext += '</coordinates></LinearRing></innerBoundaryIs>\n';
    }
    kmltext += '</Polygon>\n</Placemark>\n';
    _kjPlacemarks[_kjPlmcur].plmtext = _kjKmlcode = kmltext;
    _kjGob('coords1').value = kmlheading()+kmltext+kmlend();
}
// write javascript
function logCode4(){
    _kjGob('coords1').value = 'var myCoordinates = [\n';
    for(var i=0; i<_kjPointsArray.length; i++){
        if(i == _kjPointsArray.length-1){
            _kjGob('coords1').value += 'new google.maps.LatLng('+_kjPointsArray[i] + ')\n';
        }else{
            _kjGob('coords1').value += 'new google.maps.LatLng('+_kjPointsArray[i] + '),\n';
        }
    }
    if(_kjToolId == 1){
        _kjGob('coords1').value += '];\n';
        var options = 'var polyOptions = {\n'
        +'path: myCoordinates,\n'
        +'strokeColor: "'+_kjPolylineStyles[_kjLcur].color+'",\n'
        +'strokeOpacity: '+_kjPolylineStyles[_kjLcur].lineopac+',\n'
        +'strokeWeight: '+_kjPolylineStyles[_kjLcur].width+'\n'
        +'}\n';
        _kjGob('coords1').value += options;
        _kjGob('coords1').value +='var _kjIt = new google.maps.Polyline(polyOptions);\n'
        +'_kjIt.setMap(_kjMap);\n';
    }
    if(_kjToolId == 2){
        _kjGob('coords1').value += '];\n';
        var options = 'var polyOptions = {\n'
        +'path: myCoordinates,\n'
        +'strokeColor: "'+_kjPolygonStyles[_kjPcur].color+'",\n'
        +'strokeOpacity: '+_kjPolygonStyles[_kjPcur].lineopac+',\n'
        +'strokeWeight: '+_kjPolygonStyles[_kjPcur].width+',\n'
        +'fillColor: "'+_kjPolygonStyles[_kjPcur].fill+'",\n'
        +'fillOpacity: '+_kjPolygonStyles[_kjPcur].fillopac+'\n'
        +'}\n';
        _kjGob('coords1').value += options;
        _kjGob('coords1').value +='var _kjIt = new google.maps.Polygon(polyOptions);\n'
        +'_kjIt.setMap(_kjMap);\n';
    }
    _kjJavacode = _kjGob('coords1').value;
}
// write javascript for polygon with hole
function logCode5() {
    var hstring = "";
    _kjGob('coords1').value = 'var _kjOuterPoints = [\n';
    for(var i=0; i<_kjOuterArray.length; i++){
        if(i == _kjOuterArray.length-1){
            _kjGob('coords1').value += 'new google.maps.LatLng('+_kjOuterArray[i] + ')\n';
        }else{
            _kjGob('coords1').value += 'new google.maps.LatLng('+_kjOuterArray[i] + '),\n';
        }
    }
    _kjGob('coords1').value += '];\n';
    for(var m=0; m<_kjInnerArrays.length; m++){
        _kjGob('coords1').value += 'var innerPoints'+m+' = [\n';
        var holestring = 'innerPoints'+m;
        if(m<_kjInnerArrays.length-1) holestring += ',';
        hstring += holestring;
        for(i=0; i<_kjInnerArrays[m].length; i++){
            if(i == _kjInnerArrays[m].length-1){
                _kjGob('coords1').value += 'new google.maps.LatLng('+_kjInnerArrays[m][i] + ')\n';
            }else{
                _kjGob('coords1').value += 'new google.maps.LatLng('+_kjInnerArrays[m][i] + '),\n';
            }
        }
        _kjGob('coords1').value += '];\n';
    }
    _kjGob('coords1').value += 'var myCoordinates = [_kjOuterPoints,'+hstring+'];\n';
    _kjGob('coords1').value += 'var polyOptions = {\n'
    +'paths: myCoordinates,\n'
    +'strokeColor: "'+_kjPolygonStyles[_kjPcur].color+'",\n'
    +'strokeOpacity: '+_kjPolygonStyles[_kjPcur].lineopac+',\n'
    +'strokeWeight: '+_kjPolygonStyles[_kjPcur].width+',\n'
    +'fillColor: "'+_kjPolygonStyles[_kjPcur].fill+'",\n'
    +'fillOpacity: '+_kjPolygonStyles[_kjPcur].fillopac+'\n'
    +'};\n'
    +'var _kjIt = new google.maps.Polygon(polyOptions);\n'
    +'_kjIt.setMap(_kjMap);\n';
    _kjJavacode = _kjGob('coords1').value;
}
// write javascript or kml for _kjRectangle
function logCode6() {
    _kjPlacemarks[_kjPlmcur].style = _kjRectangleStyles[_kjRcur].name;
    if(_kjCodeId == 2) { // javascript
        _kjGob('coords1').value = 'var _kjRectangle = new google.maps._kjRectangle({\n'
            +'_kjMap: _kjMap,\n'
            +'fillColor: '+_kjRectangleStyles[_kjRcur].fill+',\n'
            +'fillOpacity: '+_kjRectangleStyles[_kjRcur].fillopac+',\n'
            +'strokeColor: '+_kjRectangleStyles[_kjRcur].color+',\n'
            +'strokeOpacity: '+_kjRectangleStyles[_kjRcur].lineopac+',\n'
            +'strokeWeight: '+_kjRectangleStyles[_kjRcur].width+'\n'
            +'});\n';
        _kjGob('coords1').value += 'var sWest = new google.maps.LatLng('+_kjSouthWest.lat().toFixed(6)+','+_kjSouthWest.lng().toFixed(6)+');\n'
        +'var nEast = new google.maps.LatLng('+_kjNorthEast.lat().toFixed(6)+','+_kjNorthEast.lng().toFixed(6)+');\n'
        +'var bounds = new google.maps.LatLngBounds(sWest,nEast);\n'
        +'_kjRectangle.setBounds(bounds);\n';
        _kjGob('coords1').value += '\n\\\\ Code for polyline _kjRectangle\n';
        _kjGob('coords1').value += 'var myCoordinates = [\n';
        _kjGob('coords1').value += _kjSouthWest.lat().toFixed(6) + ',' + _kjSouthWest.lng().toFixed(6) + ',\n' +
                    _kjSouthWest.lat().toFixed(6) + ',' + _kjNorthEast.lng().toFixed(6) + ',\n' +
                    _kjNorthEast.lat().toFixed(6) + ',' + _kjNorthEast.lng().toFixed(6) + ',\n' +
                    _kjNorthEast.lat().toFixed(6) + ',' + _kjSouthWest.lng().toFixed(6) + ',\n' +
                    _kjSouthWest.lat().toFixed(6) + ',' + _kjSouthWest.lng().toFixed(6) + '\n';
        _kjGob('coords1').value += '];\n';
        var options = 'var polyOptions = {\n'
        +'path: myCoordinates,\n'
        +'strokeColor: "'+_kjRectangleStyles[_kjRcur].color+'",\n'
        +'strokeOpacity: '+_kjRectangleStyles[_kjRcur].lineopac+',\n'
        +'strokeWeight: '+_kjRectangleStyles[_kjRcur].width+'\n'
        +'}\n';
        _kjGob('coords1').value += options;
        _kjGob('coords1').value +='var _kjIt = new google.maps.Polyline(polyOptions);\n'
        +'_kjIt.setMap(_kjMap);\n';
        _kjJavacode = _kjGob('coords1').value;
    }
    if(_kjCodeId == 1) { // kml
        var kmltext = '<Placemark><name>'+_kjPlacemarks[_kjPlmcur].name+'</name>\n' +
                        '<description>'+_kjPlacemarks[_kjPlmcur].desc+'</description>\n' +
                        '<styleUrl>#'+_kjRectangleStyles[_kjRcur].name+'</styleUrl>\n' +
                        '<Polygon>\n<tessellate>'+_kjPlacemarks[_kjPlmcur].tess+'</tessellate>\n' +
                        '<altitudeMode>'+_kjPlacemarks[_kjPlmcur].alt+'</altitudeMode>\n' +
                        '<outerBoundaryIs><LinearRing><coordinates>\n';
        kmltext += _kjSouthWest.lat().toFixed(6) + ',' + _kjSouthWest.lng().toFixed(6) + ',0\n' +
                    _kjSouthWest.lat().toFixed(6) + ',' + _kjNorthEast.lng().toFixed(6) + ',0\n' +
                    _kjNorthEast.lat().toFixed(6) + ',' + _kjNorthEast.lng().toFixed(6) + ',0\n' +
                    _kjNorthEast.lat().toFixed(6) + ',' + _kjSouthWest.lng().toFixed(6) + ',0\n' +
                    _kjSouthWest.lat().toFixed(6) + ',' + _kjSouthWest.lng().toFixed(6) + ',0\n';
        kmltext += '</coordinates></LinearRing></outerBoundaryIs>\n</Polygon>\n</Placemark>\n';
        _kjPlacemarks[_kjPlmcur].plmtext = _kjKmlcode = kmltext;
        _kjGob('coords1').value = kmlheading()+kmltext+kmlend();
    }
}
function logCode7() { // javascript for _kjCircle
    _kjPlacemarks[_kjPlmcur].style = _kjCircleStyles[_kjCcur].name;
    _kjGob('coords1').value = 'var _kjCircle = new google.maps._kjCircle({\n'
        +'_kjMap: _kjMap,\n'
        +'center: new google.maps.LatLng('+_kjCenterPoint.lat().toFixed(6)+','+_kjCenterPoint.lng().toFixed(6)+'),\n'
        +'fillColor: '+_kjCircleStyles[_kjCcur].fill+',\n'
        +'fillOpacity: '+_kjCircleStyles[_kjCcur].fillopac+',\n'
        +'strokeColor: '+_kjCircleStyles[_kjCcur].color+',\n'
        +'strokeOpacity: '+_kjCircleStyles[_kjCcur].lineopac+',\n'
        +'strokeWeight: '+_kjCircleStyles[_kjCcur].width+'\n'
        +'});\n';
    _kjGob('coords1').value += '_kjCircle.setRadius('+_kjCalc+');\n';
    _kjJavacode = _kjGob('coords1').value;
}
function logCode8(){ //javascript for Bing
    _kjGob('coords1').value = 'var points = [\n';
    for(var i=0; i<_kjPointsArray.length; i++){
        if(i == _kjPointsArray.length-1){
            _kjGob('coords1').value += 'new VELatLong('+_kjPointsArray[i] + ')\n';
        }else{
            _kjGob('coords1').value += 'new VELatLong('+_kjPointsArray[i] + '),\n';
        }
    }
    if(_kjToolId == 1){
        _kjGob('coords1').value += '];\n';
        _kjGob('coords1').value +='var polyline = new VEShape(VEShapeType.Polyline, points);\n';
        _kjGob('coords1').value +='polyline.HideIcon();\n';
        _kjGob('coords1').value +='polyline.SetLineColor(new VEColor('+_kjPolylineDecColorCur+','+_kjPolylineStyles[_kjLcur].lineopac+'));\n';
        _kjGob('coords1').value +='polyline.SetFillColor(new VEColor(0,0,0,0));\n';
        _kjGob('coords1').value +='polyline.SetLineWidth('+_kjPolylineStyles[_kjLcur].width+');\n';
        _kjGob('coords1').value +='_kjMap.AddShape(polyline);\n';
    }
    if(_kjToolId == 2){
        _kjGob('coords1').value += '];\n';
        _kjGob('coords1').value +='var polygon = new VEShape(VEShapeType.Polygon, points);\n';
        _kjGob('coords1').value +='polygon.HideIcon();\n';
        _kjGob('coords1').value +='polygon.SetLineColor(new VEColor('+_kjPolygonDecColorCur+','+_kjPolygonStyles[_kjPcur].lineopac+'));\n';
        _kjGob('coords1').value +='polygon.SetFillColor(new VEColor('+polygonFillDecColorCur+','+_kjPolygonStyles[_kjPcur].fillopac+'));\n';
        _kjGob('coords1').value +='polygon.SetLineWidth('+_kjPolygonStyles[_kjPcur].width+');\n';
        _kjGob('coords1').value +='_kjMap.AddShape(polygon);\n';
    }
    _kjJavacode = _kjGob('coords1').value;
}
function setTool(){
    if(_kjPolyPoints.length == 0 && _kjKmlcode == "" && _kjJavacode == "") {
        newstart();
    }else{
        if(_kjToolId == 1){
            // change to polyline draw mode not allowed
            if(_kjOuterArray.length > 0) { //indicates polygon with hole
                _kjPolyShape.setMap(null);
                newstart();
                return;
            }
            // change to polyline draw mode not allowed
            if(_kjRectangle) {
                _kjRectangle.setMap(null);
                newstart();
                return;
            }
            // change to polyline draw mode not allowed
            if(_kjCircle) {
                _kjCircle.setMap(null);
                newstart();
                return;
            }
            if(_kjPolyShape) _kjPolyShape.setMap(null);
            _kjfnPreparePolyline(); //if a polygon exists, _kjIt will be redrawn as polylines
            if(_kjCodeId == 1) logCode1();
            if(_kjCodeId == 2) logCode4();
            if(_kjCodeId == 3) logCode8();
        }
        if(_kjToolId == 2){
            if(_kjRectangle) {
                _kjRectangle.setMap(null);
                newstart();
                return;
            }
            if(_kjCircle) {
                _kjCircle.setMap(null);
                newstart();
                return;
            }
            if(_kjPolyShape) _kjPolyShape.setMap(null);
            _kjfnPreparePolygon(); //if a polyline exists, _kjIt will be redrawn as a polygon
            if(_kjCodeId == 1) logCode2();
            if(_kjCodeId == 2) logCode4();
            if(_kjCodeId == 3) logCode8();
        }
        if(_kjToolId == 3){
            if(_kjPolyShape) {
                _kjPolyShape.setMap(null);
                newstart();
                return;
            }
            if(_kjCircle) {
                _kjCircle.setMap(null);
                newstart();
                return;
            }
            if(_kjCodeId == 3) _kjCodeId = _kjGob('codechoice').value = 2;
        }
        if(_kjToolId == 4){
            if(_kjPolyShape) {
                _kjPolyShape.setMap(null);
                newstart();
                return;
            }
            if(_kjRectangle) {
                _kjRectangle.setMap(null);
                newstart();
                return;
            }
            _kjCodeId = _kjGob('codechoice').value = 2;
        }
    }
}
function setCode(){
    if(_kjKmlcode == "" && _kjJavacode == "") {
        if(_kjToolId == 1){
            polylineintroduction();
        }
        if(_kjToolId == 2){
            polygonintroduction();
            showthis('polygonstuff');
        }
        if(_kjToolId == 3){
            rectangleintroduction();
        }
        if(_kjToolId == 4){
            circleintroduction();
        }
    }else{
        if(_kjCodeId == 1 && _kjToolId == 1) logCode1();
        if(_kjCodeId == 1 && _kjToolId == 2 && _kjOuterArray.length == 0) logCode2();
        if(_kjCodeId == 1 && _kjToolId == 2 && _kjOuterArray.length > 0) logCode3();
        if(_kjCodeId == 2 && _kjOuterArray.length == 0) logCode4();
        if(_kjCodeId == 2 && _kjOuterArray.length > 0) logCode5();
        if(_kjCodeId == 3 && _kjToolId == 1) logCode8();
        if(_kjCodeId == 3 && _kjToolId == 2) logCode8();
        if(_kjToolId == 3) {
            if(_kjCodeId == 3) _kjCodeId = _kjGob('codechoice').value = 2;
            logCode6();
        }
        if(_kjToolId == 4) {
            if(_kjCodeId == 3) _kjCodeId = _kjGob('codechoice').value = 2;
            if(_kjCodeId == 1) _kjCodeId = _kjGob('codechoice').value = 2;
            logCode7();
        }
    }
}
function _kjNextShape() {
    if(_kjEditing == true) stopediting();
    if(_kjStartMarker) _kjStartMarker.setMap(null);
    if(_kjNeMarker) _kjNeMarker.setMap(null);
    if(_kjOldShape) _kjOldShape.setMap(null);
    _kjPlmcur = _kjPlacemarks.length -1;
    if(_kjPlacemarks[_kjPlmcur].plmtext != "") {
        if(_kjPolyShape) {
            _kjPlacemarks[_kjPlmcur].shape = _kjPolyShape;
            //_kjDrawnShapes.push(_kjPolyShape); // used in clearMap, to have _kjIt removed from the _kjMap, _kjDrawnShapes[i].setMap(null)
            addpolyShapelistener();
            _kjfnCreateplacemarkobject();
            _kjPlmcur = _kjPlacemarks.length -1;
        }
    }
    /*if(_kjRectangle) {
        var thisshape = _kjPlmcur;
        google.maps.event.addListener(_kjRectangle,'mouseover',function(point){
            _kjGob('coords1').value = _kjPlacemarks[thisshape].plmtext;
            });
        _kjDrawnShapes.push(_kjRectangle);
    }*/
    if(_kjPolyShape) _kjDrawnShapes.push(_kjPolyShape); // used in clearMap, to have _kjIt removed from the _kjMap, _kjDrawnShapes[i].setMap(null)
    if(_kjOuterShape) _kjDrawnShapes.push(_kjOuterShape);
    if(_kjCircle) _kjDrawnShapes.push(_kjCircle);
    if(_kjRectangle) _kjDrawnShapes.push(_kjRectangle);
    _kjPolyShape = null;
    _kjOuterShape = null;
    _kjRectangle = null;
    _kjCircle = null;
    newstart();
}
function addpolyShapelistener() {
    var thisshape = _kjPlmcur;
    // In v2 I can give a shape an ID and have that ID revealed, with the _kjMap listener, when the shape is clicked on
    // I can't do that in v3. Instead I put a listener on the shape
    google.maps.event.addListener(_kjPolyShape,'click',function(point){
        _kjPolyShape = _kjPlacemarks[thisshape].shape;
        _kjPolyPoints = _kjPolyShape.getPath();
        setstartMarker(_kjPlacemarks[thisshape].point);
        _kjPlmcur = thisshape;
        _kjPointsArray = _kjPlacemarks[_kjPlmcur].code;
        if(_kjPlacemarks[_kjPlmcur].poly == "pl") {
            _kjToolId = _kjGob('toolchoice').value = 1;
            closethis('polygonstuff');
            if(_kjCodeId == 1) logCode1();
            if(_kjCodeId == 2) logCode4(); // write Google javascript
            if(_kjCodeId == 3) logCode8(); // write Bing javascript
        }else{
            _kjToolId = _kjGob('toolchoice').value = 2;
            if(_kjCodeId == 1) logCode2();
            if(_kjCodeId == 2) logCode4(); // write Google javascript
            if(_kjCodeId == 3) logCode8(); // write Bing javascript
        }
    });
}
// Clear current _kjMap
function _kjClearMap(){
    //if(_kjStartMarker) _kjStartMarker.setMap(null);
    //if(_kjNeMarker) _kjNeMarker.setMap(null);
    if(_kjEditing == true) stopediting();
    if(_kjPolyShape) _kjPolyShape.setMap(null); // polyline or polygon
    //if(_kjIt) _kjIt.setMap(null); // polygon with hole
    if(_kjOldShape) _kjOldShape.setMap(null);
    if(_kjOuterShape) _kjOuterShape.setMap(null);
    if(_kjRectangle) _kjRectangle.setMap(null);
    if(_kjCircle) _kjCircle.setMap(null);
    if(_kjDrawnShapes.length > 0) {
        for(var i = 0; i < _kjDrawnShapes.length; i++) {
            _kjDrawnShapes[i].setMap(null);
        }
    }
    newstart();
    _kjPlmcur = 0;
    _kjPlacemarks = [];
    _kjfnCreateplacemarkobject();
}
function newstart() {
    _kjPolyPoints = [];
    _kjOuterPoints = [];
    _kjPointsArray = [];
    _kjOuterArray = [];
    _kjInnerArray = [];
    _kjHolePolyArray = [];
    _kjInnerArrays = [];
    _kjAdder = 0;
    closethis('polylineoptions');
    closethis('polygonoptions');
    closethis('rectang');
    closethis('circleoptions');
    if(_kjToolId != 2) closethis('polygonstuff');
    if(_kjStartMarker) _kjStartMarker.setMap(null);
    if(_kjNeMarker) _kjNeMarker.setMap(null);
    if(_kjToolId == 1) {
        _kjfnPreparePolyline();
        polylineintroduction();
    }
    if(_kjToolId == 2){
        showthis('polygonstuff');
        _kjGob('stepdiv').innerHTML = "Step 0";
        _kjfnPreparePolygon();
        polygonintroduction();
    }
    if(_kjToolId == 3) {
        _kjfnPreparePolyline(); // use Polyline to collect clicked point
        _kjfnActivateRectangle();
        rectangleintroduction();
    }
    if(_kjToolId == 4) {
        _kjfnPreparePolyline(); // use Polyline to collect clicked point
        _kjfnActivateCircle();
        circleintroduction();
        _kjCodeId = _kjGob('codechoice').value = 2; // javascript, no KML for _kjCircle
    }
    _kjKmlcode = "";
    _kjJavacode = "";
}

function _kjDeleteLastPoint(){
    if(!_kjIt && _kjToolId != 3 && _kjToolId != 4){
        _kjPolyPoints = _kjPolyShape.getPath();
        if(_kjPolyPoints.length > 0) _kjPolyPoints.removeAt(_kjPolyPoints.length-1);
        if(_kjPolyPoints.length == 0 && _kjStartMarker) _kjStartMarker.setMap(null);
    }
}
function counter(num){
    return _kjAdder = _kjAdder + num;
}
function holecreator(){
    var step = counter(1);
    if(step == 1){
        if(_kjGob('stepdiv').innerHTML == "Finished"){
            _kjAdder = 0;
            return;
        }else{
            if(_kjStartMarker) _kjStartMarker.setMap(null);
            if(_kjPolyShape) _kjPolyShape.setMap(null);
            _kjPolyPoints = [];
            _kjfnPreparePolyline();
            _kjGob('stepdiv').innerHTML = "Step 1";
            _kjGob('coords1').value = 'You may now draw the outer boundary. When finished, click Hole to move on to the next step.'
            +' Remember, you do not have to let start and end meet.'
            +' The API will close the shape in the finished polygon.';
        }
    }
    if(step == 2){
        if(_kjAnotherHole == false) {
            // outer line is finished, in Polyline draw mode
            _kjPolyPoints.insertAt(_kjPolyPoints.length, _kjStartPoint); // let start and end meet
            _kjOuterPoints = _kjPolyPoints;
            _kjHolePolyArray.push(_kjOuterPoints);
            _kjOuterShape = _kjPolyShape;
        }
        _kjGob('stepdiv').innerHTML = "Step 2";
        _kjGob('coords1').value = 'You may now draw an inner boundary. Click Hole again to see the finished polygon.';
        if(_kjAnotherHole == true) {
            // a hole has been drawn, another is about to be drawn
            if(_kjPolyShape && _kjPolyPoints.length == 0) {
                _kjPolyShape.setMap(null);
                _kjGob('coords1').value = 'Oops! Not programmed yet, but you may continue drawing holes. '+
                'Everything you have created will show up when you click Hole again.';
            }else{
                _kjPolyPoints.insertAt(_kjPolyPoints.length, _kjStartPoint);
                _kjHolePolyArray.push(_kjPolyPoints);
                if(_kjInnerArray.length>0) _kjInnerArrays.push(_kjInnerArray);
                _kjHoleShapes.push(_kjPolyShape);
                _kjInnerArray = [];
            }
        }
        _kjPolyPoints = [];
        _kjfnPreparePolyline();
        if(_kjStartMarker) _kjStartMarker.setMap(null);
    }
    if(step == 3){
        if(_kjStartMarker) _kjStartMarker.setMap(null);
        if(_kjOuterShape) _kjOuterShape.setMap(null);
        if(_kjPolyShape) _kjPolyShape.setMap(null);
        if(_kjPolyPoints.length>0) _kjHolePolyArray.push(_kjPolyPoints);
        if(_kjInnerArray.length>0) _kjInnerArrays.push(_kjInnerArray); // used in KML logging
        drawpolywithhole();
        _kjAdder = 0;
        _kjGob('stepdiv').innerHTML = "Finished";
        if(_kjCodeId == 1) logCode3();
        if(_kjCodeId == 2) logCode5();
    }
}
function drawpolywithhole() {
    if(_kjHoleShapes.length > 0) {
        for(var i = 0; i < _kjHoleShapes.length; i++) {
            _kjHoleShapes[i].setMap(null);
        }
    }
    if(_kjCodeId == 3) _kjCodeId = _kjGob('codechoice').value = 2;
    var Points = new google.maps.MVCArray(_kjHolePolyArray);
    var polyOptions = {
        paths: Points,
        strokeColor: _kjPolygonStyles[_kjPcur].color,
        strokeOpacity: _kjPolygonStyles[_kjPcur].lineopac,
        strokeWeight: _kjPolygonStyles[_kjPcur].width,
        fillColor: _kjPolygonStyles[_kjPcur].fill,
        fillOpacity: _kjPolygonStyles[_kjPcur].fillopac
    };
    _kjPolyShape = new google.maps.Polygon(polyOptions);
    _kjPolyShape.setMap(_kjMap);
    _kjAnotherHole = false;
    _kjStartMarker = new google.maps.Marker({
        position: _kjOuterPoints.getAt(0),
        _kjMap: _kjMap});
    _kjStartMarker.setTitle("Polygon with hole");
}
function nexthole() {
    if(_kjGob('stepdiv').innerHTML != "Finished") {
        if(_kjOuterPoints.length > 0) {
            _kjAdder = 1;
            _kjAnotherHole = true;
            _kjDrawnShapes.push(_kjPolyShape);
            holecreator();
        }
    }
}
function stopediting(){
    _kjEditing = false;
    _kjGob('EditButton').value = 'Edit lines';
    for(var i = 0; i < _kjMarkers.length; i++) {
        _kjMarkers[i].setMap(null);
    }
    for(var i = 0; i < _kjMidMarkers.length; i++) {
        _kjMidMarkers[i].setMap(null);
    }
    _kjPolyPoints = _kjPolyShape.getPath();
    _kjMarkers = [];
    _kjMidMarkers = [];
    //_kjMyListener = google.maps.event.addListener(_kjMap,'click',mapClick);
    if(_kjPlmcur != _kjPlacemarks.length-1) {
        _kjPlacemarks[_kjPlmcur].shape = _kjPolyShape;
        _kjDrawnShapes.push(_kjPolyShape);
        addpolyShapelistener();
    }
}
// the "Edit lines" button has been pressed
function _kjEditLines(){
    if(_kjEditing == true){
        stopediting();
    }else{
        if(_kjOuterArray.length > 0) {
            return;
        }
        //if (_kjOldShape) _kjPolyShape = _kjOldShape;
        _kjPolyPoints = _kjPolyShape.getPath();
        if(_kjPolyPoints.length > 0){
            _kjToolId = _kjGob('toolchoice').value = 1; // _kjEditing is set to be possible only in polyline draw mode
            setTool();
            //google.maps.event.removeListener(_kjMyListener);
            for(var i = 0; i < _kjPolyPoints.length; i++) {
                /*var stringtobesaved = _kjPolyPoints.getAt(i).lat().toFixed(6) + ',' + _kjPolyPoints.getAt(i).lng().toFixed(6);
                _kjPointsArray.push(stringtobesaved);*/
                var marker = setmarkers(_kjPolyPoints.getAt(i));
                _kjMarkers.push(marker);
                if(i > 0) {
                    var midmarker = setmidmarkers(_kjPolyPoints.getAt(i));
                    _kjMidMarkers.push(midmarker);
                }
            }
            _kjEditing = true;
            _kjGob('EditButton').value = 'Stop edit';
        }
    }
}
function setmarkers(point) {
    var marker = new google.maps.Marker({
    	position: point,
    	_kjMap: _kjMap,
    	icon: _kjImageNormal,
    	draggable: true
    });
    google.maps.event.addListener(marker, "mouseover", function() {
    	marker.setIcon(_kjImageHover);
    });
    google.maps.event.addListener(marker, "mouseout", function() {
    	marker.setIcon(_kjImageNormal);
    });
    google.maps.event.addListener(marker, "drag", function() {
        for (var i = 0; i < _kjMarkers.length; i++) {
            if (_kjMarkers[i] == marker) {
                _kjPolyShape.getPath().setAt(i, marker.getPosition());
                movemidmarker(i);
                break;
            }
        }
        _kjPolyPoints = _kjPolyShape.getPath();
        var stringtobesaved = marker.getPosition().lat().toFixed(6) + ',' + marker.getPosition().lng().toFixed(6);
        _kjPointsArray.splice(i,1,stringtobesaved);
        logCode1();
    });
    google.maps.event.addListener(marker, "click", function() {
        for (var i = 0; i < _kjMarkers.length; i++) {
            if (_kjMarkers[i] == marker && _kjMarkers.length != 1) {
                marker.setMap(null);
                _kjMarkers.splice(i, 1);
                _kjPolyShape.getPath().removeAt(i);
                removemidmarker(i);
                break;
            }
        }
        _kjPolyPoints = _kjPolyShape.getPath();
        if(_kjMarkers.length > 0) {
            _kjPointsArray.splice(i,1);
            logCode1();
        }
    });
    return marker;
}
function setmidmarkers(point) {
    var prevpoint = _kjMarkers[_kjMarkers.length-2].getPosition();
    var marker = new google.maps.Marker({
    	position: new google.maps.LatLng(
    		point.lat() - (0.5 * (point.lat() - prevpoint.lat())),
    		point.lng() - (0.5 * (point.lng() - prevpoint.lng()))
    	),
    	_kjMap: _kjMap,
    	icon: _kjImageNormalMidpoint,
    	draggable: true
    });
    google.maps.event.addListener(marker, "mouseover", function() {
    	marker.setIcon(_kjImageNormal);
    });
    google.maps.event.addListener(marker, "mouseout", function() {
    	marker.setIcon(_kjImageNormalMidpoint);
    });
    /*google.maps.event.addListener(marker, "dragstart", function() {
    	for (var i = 0; i < _kjMidMarkers.length; i++) {
    		if (_kjMidMarkers[i] == marker) {
    			var tmpPath = tmpPolyLine.getPath();
    			tmpPath.push(_kjMarkers[i].getPosition());
    			tmpPath.push(_kjMidMarkers[i].getPosition());
    			tmpPath.push(_kjMarkers[i+1].getPosition());
    			break;
    		}
    	}
    });
    google.maps.event.addListener(marker, "drag", function() {
    	for (var i = 0; i < _kjMidMarkers.length; i++) {
    		if (_kjMidMarkers[i] == marker) {
    			tmpPolyLine.getPath().setAt(1, marker.getPosition());
    			break;
    		}
    	}
    });*/
    google.maps.event.addListener(marker, "dragend", function() {
    	for (var i = 0; i < _kjMidMarkers.length; i++) {
    		if (_kjMidMarkers[i] == marker) {
    			var newpos = marker.getPosition();
    			var startMarkerPos = _kjMarkers[i].getPosition();
    			var firstVPos = new google.maps.LatLng(
    				newpos.lat() - (0.5 * (newpos.lat() - startMarkerPos.lat())),
    				newpos.lng() - (0.5 * (newpos.lng() - startMarkerPos.lng()))
    			);
    			var endMarkerPos = _kjMarkers[i+1].getPosition();
    			var secondVPos = new google.maps.LatLng(
    				newpos.lat() - (0.5 * (newpos.lat() - endMarkerPos.lat())),
    				newpos.lng() - (0.5 * (newpos.lng() - endMarkerPos.lng()))
    			);
    			var newVMarker = setmidmarkers(secondVPos);
    			newVMarker.setPosition(secondVPos);//apply the correct position to the midmarker
    			var newMarker = setmarkers(newpos);
    			_kjMarkers.splice(i+1, 0, newMarker);
    			_kjPolyShape.getPath().insertAt(i+1, newpos);
    			marker.setPosition(firstVPos);
    			_kjMidMarkers.splice(i+1, 0, newVMarker);
    			/*tmpPolyLine.getPath().removeAt(2);
    			tmpPolyLine.getPath().removeAt(1);
    			tmpPolyLine.getPath().removeAt(0);
    			newpos = null;
    			startMarkerPos = null;
    			firstVPos = null;
    			endMarkerPos = null;
    			secondVPos = null;
    			newVMarker = null;
    			newMarker = null;*/
    			break;
    		}
    	}
        _kjPolyPoints = _kjPolyShape.getPath();
        var stringtobesaved = newpos.lat().toFixed(6) + ',' + newpos.lng().toFixed(6);
        _kjPointsArray.splice(i+1,0,stringtobesaved);
        logCode1();
    });
    return marker;
}
function movemidmarker(index) {
    var newpos = _kjMarkers[index].getPosition();
    if (index != 0) {
    	var prevpos = _kjMarkers[index-1].getPosition();
    	_kjMidMarkers[index-1].setPosition(new google.maps.LatLng(
    		newpos.lat() - (0.5 * (newpos.lat() - prevpos.lat())),
    		newpos.lng() - (0.5 * (newpos.lng() - prevpos.lng()))
    	));
    	//prevpos = null;
    }
    if (index != _kjMarkers.length - 1) {
    	var nextpos = _kjMarkers[index+1].getPosition();
    	_kjMidMarkers[index].setPosition(new google.maps.LatLng(
    		newpos.lat() - (0.5 * (newpos.lat() - nextpos.lat())),
    		newpos.lng() - (0.5 * (newpos.lng() - nextpos.lng()))
    	));
    	//nextpos = null;
    }
    //newpos = null;
    //index = null;
}
function removemidmarker(index) {
    if (_kjMarkers.length > 0) {//clicked marker has already been deleted
    	if (index != _kjMarkers.length) {
    		_kjMidMarkers[index].setMap(null);
    		_kjMidMarkers.splice(index, 1);
    	} else {
    		_kjMidMarkers[index-1].setMap(null);
    		_kjMidMarkers.splice(index-1, 1);
    	}
    }
    if (index != 0 && index != _kjMarkers.length) {
    	var prevpos = _kjMarkers[index-1].getPosition();
    	var newpos = _kjMarkers[index].getPosition();
    	_kjMidMarkers[index-1].setPosition(new google.maps.LatLng(
    		newpos.lat() - (0.5 * (newpos.lat() - prevpos.lat())),
    		newpos.lng() - (0.5 * (newpos.lng() - prevpos.lng()))
    	));
    	//prevpos = null;
    	//newpos = null;
    }
    //index = null;
}
function showKML() {
    if (_kjPolyPoints.length > 0 || _kjPlmcur > 0) {
        if(_kjCodeId != 1) {
            _kjCodeId = _kjGob('codechoice').value = 1; // complete KML
            setCode();
        }
        _kjGob('coords1').value = kmlheading();
        for (var i = 0; i < _kjPlacemarks.length; i++) {
            _kjGob('coords1').value += _kjPlacemarks[i].plmtext;
        }
        _kjGob('coords1').value += kmlend();
    }
}
/*Syntax reminder
To edit the polygon you simply edit the paths array, like:
  poly.getPaths().removeAt(1)  // Remove the hole
Or insert a new hole:
  poly.getPaths().insertAt(1, new MVCArray([ ... some LatLngs ... ])) */
function closethis(name){
  //  _kjGob(name).style.visibility = 'hidden';
}
function showthis(name){
  //  _kjGob(name).style.visibility = 'visible';
}
function styleoptions(){ //present current style
    closethis('polylineoptions');
    closethis('polygonoptions');
    closethis('rectang');
    closethis('circleoptions');
    if(_kjToolId == 1){
        showthis('polylineoptions');
        _kjGob('polylineinput1').value = _kjPolylineStyles[_kjLcur].color;
        _kjGob('polylineinput2').value = _kjPolylineStyles[_kjLcur].lineopac;
        _kjGob('polylineinput3').value = _kjPolylineStyles[_kjLcur].width;
        _kjGob('polylineinput4').value = _kjPolylineStyles[_kjLcur].name;
    }
    if(_kjToolId == 2){
        showthis('polygonoptions');
        _kjGob('polygoninput1').value = _kjPolygonStyles[_kjPcur].color;
        _kjGob('polygoninput2').value = _kjPolygonStyles[_kjPcur].lineopac;
        _kjGob('polygoninput3').value = _kjPolygonStyles[_kjPcur].width;
        _kjGob('polygoninput4').value = _kjPolygonStyles[_kjPcur].fill;
        _kjGob('polygoninput5').value = _kjPolygonStyles[_kjPcur].fillopac;
        _kjGob('polygoninput6').value = _kjPolygonStyles[_kjPcur].name;
    }
    if(_kjToolId == 3) {
        showthis('rectang');
        _kjGob('recinput1').value = _kjRectangleStyles[_kjRcur].color;
        _kjGob('recinput2').value = _kjRectangleStyles[_kjRcur].lineopac;
        _kjGob('recinput3').value = _kjRectangleStyles[_kjRcur].width;
        _kjGob('recinput4').value = _kjRectangleStyles[_kjRcur].fill;
        _kjGob('recinput5').value = _kjRectangleStyles[_kjRcur].fillopac;
        _kjGob('recinput6').value = _kjRectangleStyles[_kjRcur].name;
    }
    if(_kjToolId == 4) {
        showthis('circleoptions');
        _kjGob('circinput1').value = _kjCircleStyles[_kjCcur].color;
        _kjGob('circinput2').value = _kjCircleStyles[_kjCcur].lineopac;
        _kjGob('circinput3').value = _kjCircleStyles[_kjCcur].width;
        _kjGob('circinput4').value = _kjCircleStyles[_kjCcur].fill;
        _kjGob('circinput5').value = _kjCircleStyles[_kjCcur].fillopac;
        _kjGob('circinput6').value = _kjCircleStyles[_kjCcur].name;
    }
}
function polylinestyle(){ //save style. multiple styles not yet implemented
    _kjPolylineStyles[_kjLcur].color = _kjGob('polylineinput1').value;
    _kjPolylineDecColorCur = color_hex2dec(_kjPolylineStyles[_kjLcur].color);
    _kjPolylineStyles[_kjLcur].lineopac = _kjGob('polylineinput2').value;
    if(_kjPolylineStyles[_kjLcur].lineopac<0 || _kjPolylineStyles[_kjLcur].lineopac>1) return alert('Opacity must be between 0 and 1');
    _kjPolylineStyles[_kjLcur].width = _kjGob('polylineinput3').value;
    if(_kjPolylineStyles[_kjLcur].width<0 || _kjPolylineStyles[_kjLcur].width>20) return alert('Numbers below zero and above 20 are not accepted');
    _kjPolylineStyles[_kjLcur].kmlcolor = getopacityhex(_kjPolylineStyles[_kjLcur].lineopac) + color_html2kml(""+_kjPolylineStyles[_kjLcur].color);
    _kjPolylineStyles[_kjLcur].name = _kjGob('polylineinput4').value;
    if(_kjPolyPoints.length > 0) {
        if(_kjPolyShape) _kjPolyShape.setMap(null);
        //if(_kjIt) _kjIt.setMap(null);
        if(_kjCodeId == 1) logCode1();
        if(_kjCodeId == 2) logCode4();
        if(_kjCodeId == 3) logCode8();
    }
    _kjfnPreparePolyline();
}
function polygonstyle() {
    _kjPolygonStyles[_kjPcur].color = _kjGob('polygoninput1').value;
    _kjPolygonDecColorCur = color_hex2dec(_kjPolygonStyles[_kjPcur].color);
    _kjPolygonStyles[_kjPcur].lineopac = _kjGob('polygoninput2').value;
    if(_kjPolygonStyles[_kjPcur].lineopac<0 || _kjPolygonStyles[_kjPcur].lineopac>1) return alert('Opacity must be between 0 and 1');
    _kjPolygonStyles[_kjPcur].width = _kjGob('polygoninput3').value;
    if(_kjPolygonStyles[_kjPcur].width<0 || _kjPolygonStyles[_kjPcur].width>20) return alert('Numbers below zero and above 20 are not accepted');
    _kjPolygonStyles[_kjPcur].fill = _kjGob('polygoninput4').value;
    polygonFillDecColorCur = color_hex2dec(_kjPolygonStyles[_kjPcur].fill);
    _kjPolygonStyles[_kjPcur].fillopac = _kjGob('polygoninput5').value;
    if(_kjPolygonStyles[_kjPcur].fillopac<0 || _kjPolygonStyles[_kjPcur].fillopac>1) return alert('Opacity must be between 0 and 1');
    _kjPolygonStyles[_kjPcur].kmlcolor = getopacityhex(_kjPolygonStyles[_kjPcur].lineopac) + color_html2kml(""+_kjPolygonStyles[_kjPcur].color);
    _kjPolygonStyles[_kjPcur].kmlfill = getopacityhex(_kjPolygonStyles[_kjPcur].fillopac) + color_html2kml(""+_kjPolygonStyles[_kjPcur].fill);
    _kjPolygonStyles[_kjPcur].name = _kjGob('polygoninput6').value;
    if(_kjPolyShape) _kjPolyShape.setMap(null);
    if(_kjOuterShape) _kjOuterShape.setMap(null);
    if(_kjHolePolyArray.length > 0) {
        drawpolywithhole();
        if(_kjCodeId == 1) logCode3();
        if(_kjCodeId == 2) logCode5();
    }else{
        _kjfnPreparePolygon();
        if(_kjCodeId == 1) logCode2();
        if(_kjCodeId == 2) logCode4();
        if(_kjCodeId == 3) logCode8();
    }
}
function _kjRectanglestyle() {
    _kjRectangleStyles[_kjRcur].color = _kjGob('recinput1').value;
    _kjRectangleStyles[_kjRcur].lineopac = _kjGob('recinput2').value;
    if(_kjRectangleStyles[_kjRcur].lineopac<0 || _kjRectangleStyles[_kjRcur].lineopac>1) return alert('Opacity must be between 0 and 1');
    _kjRectangleStyles[_kjRcur].width = _kjGob('recinput3').value;
    _kjRectangleStyles[_kjRcur].fill = _kjGob('recinput4').value;
    _kjRectangleStyles[_kjRcur].fillopac = _kjGob('recinput5').value;
    if(_kjRectangleStyles[_kjRcur].fillopac<0 || _kjRectangleStyles[_kjRcur].fillopac>1) return alert('Opacity must be between 0 and 1');
    _kjRectangleStyles[_kjRcur].kmlcolor = getopacityhex(_kjRectangleStyles[_kjRcur].lineopac) + color_html2kml(""+_kjRectangleStyles[_kjRcur].color);
    _kjRectangleStyles[_kjRcur].kmlfill = getopacityhex(_kjRectangleStyles[_kjRcur].fillopac) + color_html2kml(""+_kjRectangleStyles[_kjRcur].fill);
    _kjRectangleStyles[_kjRcur].name = _kjGob('recinput6').value;
    if(_kjRectangle) {
        _kjRectangle.setMap(null);
        _kjfnActivateRectangle();
        drawRectangle();
        logCode6();
    }
}
function circlestyle() {
    _kjCircleStyles[_kjCcur].color = _kjGob('circinput1').value;
    _kjCircleStyles[_kjCcur].lineopac = _kjGob('circinput2').value;
    if(_kjCircleStyles[_kjCcur].lineopac<0 || _kjCircleStyles[_kjCcur].lineopac>1) return alert('Opacity must be between 0 and 1');
    _kjCircleStyles[_kjCcur].width = _kjGob('circinput3').value;
    _kjCircleStyles[_kjCcur].fill = _kjGob('circinput4').value;
    _kjCircleStyles[_kjCcur].fillopac = _kjGob('circinput5').value;
    if(_kjCircleStyles[_kjCcur].fillopac<0 || _kjCircleStyles[_kjCcur].fillopac>1) return alert('Opacity must be between 0 and 1');
    _kjCircleStyles[_kjCcur].name = _kjGob('circinput6').value;
    if(_kjCircle) {
        _kjCircle.setMap(null);
        _kjfnActivateCircle();
        drawCircle();
        logCode7();
    }
}
function docudetails(){
    _kjGob("plm1").value = _kjPlacemarks[_kjPlmcur].name;
    _kjGob("plm2").value = _kjPlacemarks[_kjPlmcur].desc;
    _kjGob("plm3").value = _kjPlacemarks[_kjPlmcur].tess;
    _kjGob("plm4").value = _kjPlacemarks[_kjPlmcur].alt;
    _kjGob("doc1").value = _kjDocuname;
    _kjGob("doc2").value = _kjDocudesc;
}
function savedocudetails(){
    _kjDocuname = _kjGob("doc1").value;
    _kjDocudesc = _kjGob("doc2").value;
    _kjPlacemarks[_kjPlmcur].name = _kjGob("plm1").value;
    _kjPlacemarks[_kjPlmcur].desc = _kjGob("plm2").value;
    _kjPlacemarks[_kjPlmcur].tess = _kjGob("plm3").value;
    _kjPlacemarks[_kjPlmcur].alt = _kjGob("plm4").value;
    if(_kjPlacemarks[_kjPlmcur].poly == "pl") logCode1();
    if(_kjPlacemarks[_kjPlmcur].poly == "pg") logCode2();
}
function _kjfnMapZoom(){_kjGob("myzoom").value = _kjMap.getZoom();}
function mapcenter(){
    var mapCenter = _kjMap.getCenter();
    var latLngStr = mapCenter.lat().toFixed(6) + ', ' + mapCenter.lng().toFixed(6);
    _kjGob("centerofmap").value = latLngStr;
}
function color_html2kml(color){
    var newcolor ="FFFFFF";
    if(color.length == 7) newcolor = color.substring(5,7)+color.substring(3,5)+color.substring(1,3);
    return newcolor;
}
function color_hex2dec(color) {
    var deccolor = "255,0,0";
    var dec1 = parseInt(color.substring(1,3),16);
    var dec2 = parseInt(color.substring(3,5),16);
    var dec3 = parseInt(color.substring(5,7),16);
    if(color.length == 7) deccolor = dec1+','+dec2+','+dec3;
    return deccolor;
}
function getopacityhex(opa){
    var hexopa = "66";
    if(opa == 0) hexopa = "00";
    if(opa == .0) hexopa = "00";
    if(opa >= .1) hexopa = "1A";
    if(opa >= .2) hexopa = "33";
    if(opa >= .3) hexopa = "4D";
    if(opa >= .4) hexopa = "66";
    if(opa >= .5) hexopa = "80";
    if(opa >= .6) hexopa = "9A";
    if(opa >= .7) hexopa = "B3";
    if(opa >= .8) hexopa = "CD";
    if(opa >= .9) hexopa = "E6";
    if(opa == 1.0) hexopa = "FF";
    if(opa == 1) hexopa = "FF";
    return hexopa;
}
function polylineintroduction() {
    _kjGob('coords1').value = 'Ready for Polyline. Click on the _kjMap. The code for the shape you create will be presented here.\n\n'
                        +'When finished with a shape, click Next shape and draw another shape, if you wish.\n'
                        +'If you want to edit a saved polyline or polygon, click on _kjIt. Then click Edit lines.\n'
                        +'The complete KML code for what you have created, is always available with Show KML.';
}
function polygonintroduction() {
    _kjGob('coords1').value = 'Ready for Polygon. Click on the _kjMap. The code for the shape you create will be presented here. '
            +'The Maps API will automatically "close" any polygons by drawing a stroke connecting the last coordinate back to the '
            +'first coordinate for any given paths.\n'
            +'\nTo create a polygon with hole(-s), click "Hole" before you start the drawing.'
            +'When finished with a shape, click Next shape and draw another shape, if you wish.\n'
            +'If you want to edit a saved polyline or polygon, click on _kjIt. Then click Edit lines.\n'
            +'The complete KML code for what you have created, is always available with Show KML.';
}
function rectangleintroduction() {
    _kjGob('coords1').value = 'Ready for _kjRectangle. Click two times on the _kjMap - first for the _kjSouthWest and '+
            'then for the _kjNorthEast corner. You may resize and move '+
            'the _kjRectangle with the two draggable _kjMarkers you then have.\n\n'+
            'The v3 _kjRectangle is a polygon. But in Javascript code mode an extra code for '+
            'polyline is presented here in the text area.';
}
function circleintroduction() {
    _kjGob('coords1').value = 'Ready for _kjCircle. Click for center. Then click for radius distance. '+
    'You may resize and move the _kjCircle with the two draggable _kjMarkers you then have.\n\n'+
    'KML code is not available for _kjCircle.';
}
