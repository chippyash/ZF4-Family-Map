<?php
/**
 * @category	ZF4
 * @package 	Visitor
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
 * @category	ZF4
 * @package 	Visitor
 *
 * Utilities to act on the site visitor
 */
class ZF4_Visitor {

	/**
	 * Return the site visitor's IP address
	 *
	 * usage: $ip = ZF4_Visitor::getIp();
	 *
	 * @return string
	 */
	static function getIp() {
	    if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	        $theIp=$_SERVER['HTTP_X_FORWARDED_FOR'];
	    else $theIp=$_SERVER['REMOTE_ADDR'];

	    return trim($theIp);
	}
}