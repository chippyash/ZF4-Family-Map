<?php
/**
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Help
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
 * Help controller
 *
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Help
 */
class HelpController extends Application_Model_Controller {
	/**
	 * Allow user to change their password
	 * Will issue a new random password if successful
	 */
	public function forgottenpasswordAction() {
		$request = $this->getRequest();
		$form = new Application_Model_Form_Forgot();
		if ($request->isPost()) {
			if ($form->isValid($request->getParams())) {
				if ($form->save($this->view)) {
					$this->renderScript('help/thanks.phtml');
					return;
				} else {
					throw new Application_Model_Form_Exception_UnknownRecordSaveError();
				}
			}
			//else form will redisplay with errors
		} elseif (!$request->isGet()) {
			throw new Application_Model_Exception_InvalidHttpRequest();
		}
		$this->view->form = $form;
	}

	/**
	 * 
	 *
	 */
	public function testAction() {
	}
}