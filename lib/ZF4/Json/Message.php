<?php
/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package  	Json
 * @subpackage  Message
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
 * Standardised Json return message block
 *
 * <p>message = <p><ul>
 * <li>success : boolean</li>
 * <li>msg : string</li>
 * <li>data : mixed</li>
 * </ul>
 *
 * @category	ZF4
 * @package     Json
 * @subpackage  Message
 */

class ZF4_Json_Message {
	/**
	 * Success flag
	 *
	 * @var boolean
	 */
	public $success = true;
	/**
	 * Message to be sent back to caller
	 *
	 * @var string
	 */
	public $msg = '';
	/**
	 * Data to be sent back to caller
	 *
	 * @var array
	 */
	public $data = array();

}