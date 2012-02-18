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
 * A mercator projected polygon
 * 
 * @category 	ZF4_Lib
 * @package  	Polygon
 * @subpackage  Mercator
 */
abstract class ZF4_Polygon_Mercator extends ZF4_Polygon_Abstract  {
	
	/**
	 * Is a point inside the polygon
	 *
	 * @param array $point[x,y]
	 * @return boolean
	 */
	public function pointInside(array $point) {
		$v = new vertex($point[0],$point[1]);
		return parent::isInside($v);
	}	
}