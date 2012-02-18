<?php
/**
 * Base exception
 *
 * Use this as base for all your exceptions
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
 * Exception base class
 *
 * All application exceptions must be extended from this one.  It allows the setting of a standard
 * message in the $_staticMessage parameter and a standard http error code (500) in the $_httpCode parameter
 *
 * @category	ZF4
 * @package 	Exception
 * @see /application/Controllers/ErrorController.php
 *
 */
class ZF4_Exception extends Exception {

	/**
	 * The http errocode to return in the header
	 * Overide in ancestor if required
	 *
	 * @var int
	 */
	protected $_httpCode = 500;

	/**
	 * Standard error message
	 * Overide in ancestor
	 *
	 * @var string
	 */
	protected $_staticMessage = 'An error occured';

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
		//set static message if required
		if (is_null($message)) $message = $this->_staticMessage;
		parent::__construct($message, $code);
	}

	/**
	 * Get the current HTTP error code
	 *
	 * @return int
	 */
	public function getHttpCode() {
		return $this->_httpCode;
	}

	/**
	 * Set the HTTP error code
	 *
	 * @param int $code
	 */
	public function setHttpCode($code) {
		$this->_httpCode = $code;
	}
}