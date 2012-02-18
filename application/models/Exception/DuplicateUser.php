<?php
/**
 * Exception
 *
 * @category	Family_Map
 * @package 	Exception
 * @author 	Ashley Kitson
 * @copyright   ZF4 Business Limited and Woodnewton - a learning community, 2011, UK
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
 * Exception Handler
 */
include_once "ZF4/Exception.php";

/**
 * Exception: Duplicate user name
 *
 * @category	Family_Map
 * @package 	Exception
 */

class Application_Model_Exception_DuplicateUser extends ZF4_Exception {

	/**
	 * Standard error message
	 *
	 * @var string
	 */
	protected $_staticMessage = 'Sorry, that email is taken. Please try again with a different email address or <a href="http://www.snackajacks.co.uk/contact/" target="_blank">contact us</a><a href="/" class="backtoshetime" title="Back to SHE-TIME" alt="Back to SHE-TIME"></a>';
	protected $_clothesMessage = 'Sorry, that email is taken. Please try again with a different email address or <a href="http://www.snackajacks.co.uk/contact/" target="_blank">contact us</a><a href="/clothesshow/register" class="backtoshetime" title="Back to Registration" alt="Back to registration"></a>';

	/**
	 * Added amendment to message to cater for clothes show 7/6/10
	 *
	 * @param string $message
	 * @param int $code
	 * @param int $httpCode
	 */
	public function __construct($message = null, $code = null, $httpCode = null) {
		$sess = new Zend_Session_Namespace(ClothesshowController::SESS_CLOTHES );
		if (isset($sess->flag)) {
			$this->_staticMessage = $this->_clothesMessage;
		}
		parent::__construct($message,$code,$httpCode);
	}
}