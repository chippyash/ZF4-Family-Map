// Global variables
var map;
var polyShape;
var markers = [];
var report = document.getElementById("status");
 
 
function setShape() {
 
  var opts_poly = {
   strokeColor: "#3355ff",
   strokeOpacity: .8,
   strokeWeight: 3,
   fillColor: "#335599",
   fillOpacity: .3
  };
  polyShape = new google.maps.Polygon(opts_poly);
  polyShape.setMap(map);
}
 
 
function createMarker(point) {
 
  var g = google.maps;
  var image = new g.MarkerImage("../square.png",
   new g.Size(11, 11),
   null,
   new g.Point(5, 5));
 
  var over_img = new g.MarkerImage("../m-over-square.png",
   new g.Size(11, 11),
   null,
   new g.Point(5, 5));
 
  var marker = new g.Marker({
    position: point, map: map,
    icon: image,
    draggable: true
  });
  markers.push(marker);
 
  g.event.addListener(marker, "mouseover", function() {
   marker.setIcon(over_img);
  });
 
  g.event.addListener(marker, "mouseout", function() {
   marker.setIcon(image);
  });
 
  // Drag listener
  g.event.addListener(marker, "drag", function() {
   for (var m = 0; m < markers.length; m++) {
    if(markers[m] == marker) {
     var newpos = marker.getPosition();
     break;
    }
   }
 
   // Update MVCArray
   polyShape.getPath().setAt(m, newpos);
  });
 
 
  // Click listener to remove a marker
  g.event.addListener(marker, "click", function() {
   // Find out removed marker
   for (var n = 0; n < markers.length; n++) {
    if(markers[n] == marker) {
     marker.setMap(null);
     markers.splice(n, 1);
     break;
    }
   }
 
   // Remove removed point from MVCArray
   polyShape.getPath().removeAt(n);
  });
 return marker;
}
 
 
function buildMap() {
 
  var g = google.maps;
  var opts_map = {
   center: new g.LatLng(51.2516, 6.976318),
   zoom: 8,
   mapTypeId: g.MapTypeId.ROADMAP,
   draggableCursor:'auto', draggingCursor:'move',
   disableDoubleClickZoom: true,
   mapTypeControlOptions: {
    mapTypeIds: [ g.MapTypeId.ROADMAP, g.MapTypeId.SATELLITE, g.MapTypeId.TERRAIN]
   },
   navigationControlOptions: {
     style: g.NavigationControlStyle.SMALL
   }
  };
  map = new g.Map(document.getElementById("map"), opts_map);
  setShape();
 
  // Add listener for the click event
  g.event.addListener(map, "click", leftClick);
}
 
 
 
function leftClick(event) {
 
 if (event.latLng) {
  if (!polyShape) setShape();
 
  // Add a marker at the clicked point
  var marker = createMarker(event.latLng);
 
  var path = polyShape.getPath();
  path.push(event.latLng);
 }
}
 
/* No spherical geometry integrated in v3 yet
function showValues() {
 
 var unit = " km&sup2;";
 var area = polyShape.getArea()/(1000*1000);
 
 if(markers.length <= 2 ) {
  report.innerHTML = "&nbsp;";
 }
 else if(markers.length > 2 ) { 
  report.innerHTML = area.toFixed(3)+ unit;
 }
}
*/
 
 
function zoomToPoly() {
 
 if (polyShape && polyShape.getPath().getLength() > 1) {
  var shapeBounds = new google.maps.LatLngBounds();
  // Iterate through MVCArray
  polyShape.getPath().forEach(function(point) {
    shapeBounds.extend(point);
  });
  map.fitBounds(shapeBounds);
 }
}
 
 
function clearPoly() {
 
 // Remove polygon, markers and reset globals
 for (var i = 0; i < markers.length; i++) {
   markers[i].setMap(null);
 }
 if (polyShape) {
  polyShape.setMap(null);
  polyShape = null;
 }
 markers.length = 0;
// report.innerHTML = "&nbsp;";
}