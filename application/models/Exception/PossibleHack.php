<?php
/**
 * Exception - hack attempt
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
 * Exception: Possible hack - logs details to log file in /httpdocs/logs
 *
 * @category	Family_Map
 * @package 	Exception
 */

class Application_Model_Exception_PossibleHack extends ZF4_Exception {

	/**
	 * Standard error message
	 *
	 * @var string
	 */
	protected $_staticMessage = 'Unknown error: This has been reported to the administrator';

	public function __construct($message = null, $code = null, $httpCode = null) {
		//log our information
		$title = 'Possible Hack: ' . $message;
		//get user session info
		$sess = new Zend_Session_Namespace(Application_Model_User::SESS_KEY_USER );
		$session = array();
		foreach ($sess as $key=>$item) {
			$session[$key] = $item;
		}
		$session = var_export($session,true);
		$trace = $this->getTraceAsString();
		$file = $this->getFile();
		$logMsg = $title . PHP_EOL
				. str_pad('', strlen($title), '*') . PHP_EOL
		        . 'At: ' . Saj_Date::now()->get(Zend_Date::DATETIME_LONG ) . PHP_EOL
		        . 'Trace: ' . $trace . PHP_EOL
		        . 'File: ' . $file . PHP_EOL
		        . 'Session: ' . $session . PHP_EOL
		        . 'IP: ' . $this->_visitorIP() . PHP_EOL
		        . 'Agent: ' . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL . PHP_EOL
		        . str_pad('', strlen($_SERVER['HTTP_USER_AGENT']), '*') . PHP_EOL . PHP_EOL;
		$outFile = dirname(ZF4_ROOT_PATH) . '/logs/poss_hack.log';
		$f = fopen($outFile,'a');
		fwrite($f,$logMsg);
		fclose($f);
		parent::__construct();
	}

    private function _visitorIP() {
    	if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        	$TheIp=$_SERVER['HTTP_X_FORWARDED_FOR'];
    	} else { $TheIp=$_SERVER['REMOTE_ADDR'];
 		    return trim($TheIp);
    	}
    }
}