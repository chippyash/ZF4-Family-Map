<?php
/**
 * Family Map Form Exception
 *
 * @category	Family_Map
 * @package 	Form
 * @subpackage  Exception
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
 * Exception: Unknown record save for a form
 * @category	Family_Map
 * @package 	Form
 * @subpackage  Exception
 */
class Application_Model_Form_Exception_UnknownRecordSaveError
	extends ZF4_Exception {

	/**
	 * Standard error message
	 *
	 * @var string
	 */
	protected $_staticMessage = 'An error occurred whilst trying to save your details - please try later';

}
