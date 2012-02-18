<?php
/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package 	Exception
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
 * Exception: Serious exception
 *
 * @category	ZF4
 * @package  Exception
 */

class ZF4_Exception_Serious extends ZF4_Exception {

	/**
	 * Standard error message
	 *
	 * @var string
	 */
	protected $_staticMessage = "<h3>This shouldn't happen</h3><p>Error was: %s</p><p style='color:red;'>Please contact the system developer</p>";
	
	/**
	 * Constructor
	 *
	 * @param string $message	Error Message
	 * @param int $code			Error code
	 * @param int $httpCode		HTTP Header code to be sent
	 */
	public function __construct($message = null, $code = null, $httpCode = null) {
		//set the HTTP return code
		if (!is_null($httpCode)) $this->_httpCode = $httpCode;
		
		$message = sprintf($this->_staticMessage,$message);
		parent::__construct($message, $code);
	}

}