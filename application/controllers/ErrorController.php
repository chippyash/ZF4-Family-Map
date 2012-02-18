<?php
/**
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Error
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
 * Error controller
 *
 * @category	Family_Map
 * @package 	Controller
 * @subpackage  Error
 */
class ErrorController extends Application_Model_Controller {

	/**
	 * Handles automatic translation of exceptions into error pages
	 * The http response code is set to whatever the exception dictated
	 *
	 * For Json requests, returns a ZF4_Json_Message
	 * and resets the http response code to 200
	 * Message block is:
	 * Message->success = false
	 * Message->msg = error (exception) message
	 * Message->data->http_response = the http response code
	 * Message->data->error_code = exception error code (if any - can be zero)
	 * Message->data->error_type = class name of exception
	 *
	 */
    public function errorAction() {
    	//switch layouts
		$this->_helper->layout->setLayout('layout3');
        $errors = $this->_getParam('error_handler');
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->view->message = 'Page not found';
                 // 404 error -- controller or action not found
				$this->getResponse()->setHttpResponseCode(404); //default error code
                break;
            default:
           		$this ->view->message = $errors->exception->getMessage();
           		if ($errors->exception instanceof ZF4_Exception ) {
           			$this->getResponse()->setHttpResponseCode($errors->exception->getHttpCode());
           		} else {
           			//server error
           			$this->getResponse()->setHttpResponseCode(500);
           		}
        }

        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
        $this->view->env = APPLICATION_ENV;

        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if ($contextSwitch->getCurrentContext() == 'json') {
			//return a json message instead
			$message = new ZF4_Json_Message();
			$message->success = false;
			$message->msg = $errors->exception->getMessage();
			$message->data = array(
				'http_response' => $this->getResponse()->getHttpResponseCode(),
				'error_code' => $errors->exception->getCode(),
				'error_type' => get_class($errors->exception)
			);
			//send response to caller
			$this->getResponse()->setHttpResponseCode(200);
			$this->_helper->json->sendJSON($message);
        }
    }
}