<?php
/**
 * ZF4 Library
 *
 * Action Helper sets up logs and provides access to them
 *
 * @category	ZF4
 * @package 	Action
 * @subpackage  Helper
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
 * ZF4 log setup helper
 *
 * @category	ZF4
 * @package 	Action
 * @subpackage  Helper
 */
class ZF4_Action_Helper_Logger extends ZF4_Action_Helper {

	/**
	 * The loggers
	 *
	 * @var array of Zend_Log
	 */
	private static $_logs = array();

	private static $_mailParms = array();
	
	/**
	 * Overide ancestor to set up loggers
	 *
	 * @return array the loggers - helper broker will place them in the registry
	 *
	 */
	public function init() {
		parent::init();
		foreach ($this->_options as $key => $values) {
			$this->_options[$key]['enabled'] = (isset($this->_options[$key]['enabled']) ? (boolean) $this->_options[$key]['enabled'] : false);
		}
		$this->_options[$key]['mailto'] = (isset($this->_options[$key]['mailto']) ? $this->_options[$key]['mailto'] : null);
		if (isset($this->_options[$key]['maillevel'])) {
			eval('$er = ' . $this->_options[$key]['maillevel'] . ';');
			$this->_options[$key]['maillevel'] = $er;
		} else {
			$this->_options[$key]['maillevel'] = Zend_Log::ERR ;
		}
		$logs = array();
		foreach ($this->_options as $key => $values) {
			if ($values['enabled']) {
				$writer = null;
				switch (strtolower($values['writer']['type'])) {
					case 'stream':
						$writer = new Zend_Log_Writer_Stream($values['writer']['uri']);
						break;
					case 'db':
						$colMap = array();
						foreach ($values['writer']['table']['col'] as $colName => $tag) {
							$colMap[$colName] = $tag;
						}
						$writer = new Zend_Log_Writer_Db(Zend_Db_Table_Abstract::getDefaultAdapter(),$values['writer']['table']['name'],$colMap);
						break;
					default:
						break;
				}
				if ($writer !== null) {
					self::$_logs[$key] = new Zend_Log($writer);
					self::$_mailParms[$key] = array(
						'maillevel'=>$values['maillevel'],
						'mailto'=>$values['mailto']
					);
				}
			}
		}

		return self::$_logs;
	}

	/**
	 * Action helper direct method to log to a logger
	 *
	 * usage: $this->_helper->Logger($logName, $message, $priority, $extras);
	 * In your action script
	 *
	 * @param string $logname name of logger to use
	 * @param string $message
	 * @param int $priority One of Zend_Log::.. log error levels
	 * @param mixed $extras  Extra information - see Zend_Log
	 */
	public function direct($logname,$message,$priority = Zend_Log::INFO, $extras = null ) {
		self::$_logs[$logname]->log($message,$priority, $extras);
		if (self::$_mailParms[$logname]['mailto'] != null && $priority <= self::$_mailParms[$logname]['maillevel']) {
			self::_mailer(self::$_mailParms[$logname]['mailto'], $message, $extras);
		}
	}

	/**
	 * Return a logging object
	 *
	 * usage: $logger = $this->_helper->Logger->getLogger($logName);
	 * In your action script
	 *
	 * @param string $logname name of logger
	 * @return Zend_Log
	 * @throws ZF4_Action_Helper_Logger_Exception if an invalid logger name is specified
	 */
	public function getLogger($logname) {
		if (!isset(self::$_logs[$logname])) {
			throw new ZF4_Action_Helper_Logger_Exception();
		}
		return self::$_logs[$logname];
	}

	/**
	 * Add or replace a logger
	 *
	 * usage: $this->_helper->Logger->setLogger($logName, $logger);
	 * In your action script
	 *
	 * @param string $logname Name of log
	 * @param Zend_Log $logger The logger object
	 */
	public function setLogger($logname, Zend_Log $logger) {
		self::$_logs[$logname] = $logger;
	}
	
	/**
	 * Static method to log a message
	 *
	 * usage: ZF4_Action_Helper_Logger::log($logName, $message, $priority, $extras);
	 *
	 * @param string $logname name of logger to use
	 * @param string $message
	 * @param int $priority One of Zend_Log::.. log error levels
	 * @param mixed $extras  Extra information - see Zend_Log
	 */

	public static function log($logname,$message,$priority = Zend_Log::INFO, $extras = null) {
		if (isset(self::$_logs[$logname])) {
			self::$_logs[$logname]->log($message,$priority, $extras);
			if (self::$_mailParms[$logname]['mailto'] != null && $priority <= self::$_mailParms[$logname]['maillevel']) {
				self::_mailer(self::$_mailParms[$logname]['mailto'],$message,$extras);
			}		
		}
	}
	
	/**
	 * Send a mail message
	 *
	 * @param unknown_type $message
	 * @param unknown_type $extras
	 */
	protected static function _mailer($to, $message, $extras) {
		$msg = <<<EOT
The Family Map logging system has trapped a message that is important;
The message logged was: "{$message}"
Username: {$extras['uName']}
IP: {$extras['ip']}
Org: {$extras['orgId']}
EOT;
		$mailer = new ZF4_Mail();
		$mailer->addTo($to)
			   ->setBodyText($msg)
			 ->setSubject('ZF4 Error Log message')
			   ->send();
	}
}