<?php
/**
 * ZF4 Library
 *
 * @category 	ZF4_Lib
 * @package  	Polygon
 * @subpackage  Mercator
 * @author 	Ashley Kitson
 * @copyright   ZF4 Business Limited 2011, UK
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE V3
 * 
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *    License text is located in /docs/LICENSE.FAMILYMAP.txt
 */

/**
 * ZF4 Polygon maths
 * A mercator projected polygon using WGS84 datum
 * 
 * @category 	ZF4_Lib
 * @package  	Polygon
 * @subpackage  Mercator
 */
class ZF4_Polygon_Mercator_WGS84 extends ZF4_Polygon_Mercator  {
	
	/**
	 * Radius being used for mercator projection
	 *
	 * @var double
	 */
	protected $_radius = 1;
	
	public function __construct(array $polygon = array()) {
		$this->_radius = pi()*2;
		parent::__construct();
		$this->setPolygon($polygon);
	}
	
	/**
	 * Set the polygon coords
	 *
	 * @param array $polygon WGS84 coords [[lat,lng],[lat,lng],...]
	 */
	public function setPolygon(array $polygon) {
		foreach ($polygon as &$point) {
			$x = $this->longToX((double)$point[1]);
			$y = $this->latToY((double)$point[0]);
			parent::addv($x,$y);
		}
	}	
	
	/**
	 * Is a WGS84 point inside the polygon
	 *
	 * @param array $LatLng [lat,lng]
	 * @return boolean
	 */
	public function pointInside(array $latLng) {
		return parent::pointInside($this->wgs84ToMercator($latLng));
	}
		
	/**
	 * Convert WGS84 longitude to Mercator X
	 *
	 * @param double $long
	 * @return double
	 */
	public function longToX($long) {
		return ($this->_radius * deg2rad($long));
	}
	
	/**
	 * Convert WGS84 latitude to Mercator Y
	 *
	 * @param double $lat
	 * @return double
	 */
	public function latToY($lat) {
		$lat = deg2rad($lat);
		return $this->_radius/2.0 * 
            log( (1.0 + sin($lat)) /
                 (1.0 - sin($lat)) );
	}
	
	/**
	 * Convert from Mercator Y to WGS84 Longitude
	 *
	 * @param double $x
	 * @return double
	 */
	public function xToLong($x) {
	    $longRadians = $x/$this->_radius;
	    $longDegrees = rad2deg($longRadians);
	    
	    /* The user could have panned around the world a lot of times.
	    Lat long goes from -180 to 180.  So every time a user gets 
	    to 181 we want to subtract 360 degrees.  Every time a user
	    gets to -181 we want to add 360 degrees. */
	       
	    $rotations = floor((longDegrees + 180)/360);
	    $longitude = $longDegrees - ($rotations * 360);
	    return $longitude;
	}

	protected function _yToLat($y) {
		$latitude =  (pi()/2) - 
                    (2 * atan(
                       exp(-1.0 * y / $this->_radius)));
	    return rad2deg($latitude);
	}
	
	/**
	 * Convert a WGS84 datum to Mercator coordinate
	 *
	 * @param array $point
	 * @return array
	 */
	public function wgs84ToMercator(array $point) {
		$point = array($this->longToX((double)$point[1]),$this->latToY((double)$point[0]));
		return $point;
	}
	
	/**
	 * Convert a Mercator Coordinate to WGS84 dataum (lat,lng)
	 *
	 * @param array $point
	 * @return array
	 */
	public function mercatorToWgs84(array $point) {
		$point = array($this->yToLat((double)$point[1]),$this->xToLong((double)$point[0]));
		return $point;
	}
}