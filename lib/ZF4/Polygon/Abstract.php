<?php
/**
 * ZF4 Library
 *
 * @category 	ZF4_Lib
 * @package  	Polygon
 * @subpackage  Abstract
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
 * Base class support
 */ 
$bp = dirname(dirname(dirname(__FILE__)));
if (file_exists($bp . '/GPL/Polygon/polygon.php')) {
	require_once($bp . '/GPL/Polygon/polygon.php');
} else if(defined('ZF4_PATH')) {
	require_once(ZF4_PATH . '/GPL/Polygon/polygon.php');
} else {
	throw new Exception('Cannot find Polygon library');
}

/**
 * ZF4 Polygon maths
 *
 * @category 	ZF4_Lib
 * @package  	Polygon
 * @subpackage  Abstract
 */
abstract class ZF4_Polygon_Abstract extends polygon {

	/**
	 * Constructor
	 *
	 * @param array $polygon [[x,y],[x,y],...]
	 */
	public function __construct(array $polygon = array()) {
		parent::polygon();
		$this->setPolygon($polygon);
	}
	
	/**
	 * Set the polygon coords
	 *
	 * @param array $polyArray
	 */
	public function setPolygon(array $polyArray) {
		foreach ($polyArray as $point) {
			parent::addv($point[0],$point[1]);
		}
	}
	
	/**
	 * Get the area inside the polygon
	 *
	 * @todo Finish getArea()
	 * @return double
	 */
	public function getArea() {
		return 0;
	}
	
	/**
	 * Return vertices of polygon as an array
	 *
	 */
	public function toArray() {
		$poly = array();
		$thisV = $this->getFirst();
		for ($c = $this->_cnt;$c!=0;$c--) {
			$poly[] = array($thisV->X(),$thisV->Y());
			$thisV = $thisV->Next();
		}
		
		return $poly;
	}
	
	/**
	 * Return the polygon bounding box coordinates
	 * 
	 * If you want the polygon, use ->bRect();
	 *
	 * @return array [Corner1,Corner2,Corner3,Corner4] = [[x,y],[x,y]][x,y]][x,y]]
	 */
	public function getBoundingBox() {
		$boxPoly = parent::bRect();
		$p1 = $boxPoly->getFirst();
		$p2 = $p1->Next();
		$p3 = $p2->Next();
		$p4 = $p3->Next();
		$box = array(
			array($p1->X(),$p1->Y()),
			array($p2->X(),$p2->Y()),
			array($p3->X(),$p3->Y()),
			array($p4->X(),$p4->Y())
		);
		return $box;
	}
}