<?php
/**
 * ZF4 Library
 *
 * Action helper ensure user is coming from host on whitelist
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
 * ZF4 Whitelist helper
 *
 * Checks state of application.resources.actionhelper.whitelist.enabled and if true and user is not
 * coming from the whitelist then redirects to error page
 *
 * @category	ZF4
 * @package 	Action
 * @subpackage  Helper
 */
class ZF4_Action_Helper_Whitelist extends ZF4_Action_Helper {

	/**
	 * Overide ancestor to massage options values
	 *
	 */
	public function init() {
		parent::init();
		$this->_options['enabled'] = (isset($this->_options['enabled']) ? (boolean) $this->_options['enabled'] : false);
		$this->_options['skip'] = (isset($this->_options['skip']) ? explode(',',$this->_options['skip']) : array());
		$this->_options['list'] = (isset($this->_options['list']) ? explode(',',$this->_options['list']) : array());
	}

	/**
	 * See if user is required to be checked
	 *
	 * If user is not in whitelist then throw exception that will land them on error page
	 *
	 */
	public function preDispatch() {
		if (!$this->_options['enabled']) return; //nothing to do

		//see if we are already on the logon page or any page in the logon.skip list
		$ctrl = $this->getActionController();
		$request = $ctrl->getRequest();
		$url = '/' . $request->getModuleName()
		     . '/' . $request->getControllerName()
		     . '/' . $request->getActionName();
		$skip = $this->_options['skip'];
		if (in_array($url,$skip)) { return;} //nothing to do

		//We need to check if user is logged on - if they are then assume they are ok
//		if (ZF4_User::checkLogon()) {
//			return;
//		} else {
			//check the incoming ip address
			//get ips from cache if available
			$frontOpts = array(
				'cached_entity' => $this,
				'cache_by_default' => false,
				'cached_methods' => array('getWhiteList'),
				'automatic_serialization' => true
			);
			$backOpts = array(
				'cache_dir' => ZF4_Defines::dirCache('class'),
				'file_name_prefix' => 'whitelist'
			);
			$cache = Zend_Cache::factory('Class','File',$frontOpts,$backOpts);
			$whiteList = $cache->getWhiteList();
			$ip = ZF4_Visitor::getIp();
			if (!in_array($ip,$whiteList)) {
				//log the violation
				$this->_log('Attempt to access system from unknown IP',Zend_Log::ALERT);
				//throw the exception to present error message
				throw new ZF4_Action_Helper_Whitelist_Exception();
			}
//		}
	}
	
	/**
	 * Scans through the whitelist and tries to convert any 
	 * domain names to an ip address
	 *
	 * @return array
	 */
	public function getWhiteList() {
		$retArr = array();
		foreach ($this->_options['list'] as &$item) {
			if (strstr($item,'-') != false) {
				//we have a range
				$parts = explode('-',$item);
				$stIp = ip2long($parts[0]);
				$enIp = ip2long($parts[1]);
				for ($ip = $stIp; $ip <= $enIp; $ip++) {
					$retArr[] = long2ip($ip);
				}
			} elseif (preg_match('/(?:\d{1,3}\.){3}\d{1,3}\/\d{1,3}/',$item) != 1) {
				//do dns lookup
				$ip = gethostbyname($item);
				if (preg_match('/(?:\d{1,3}\.){3}\d{1,3}/',$ip) != 1) {
					$this->_log('No IP for DN: ' . $item,Zend_Log::ERR);
				} else {
					$retArr[] = $ip;
				}
			} else {
				//plain ip
				$retArr[] = $item;
			}
		}
		return $retArr;
	}
	
	protected function _log($msg,$level) {
		$uName = ZF4_User::getIdentity();
		$uName = ($uName == '' || $uName == null ? 'No Id' : $uName);
		if ($uName != 'No Id') {
			$user = ZF4_User::getSessionIdentity();
			$orgId = $user['orgId'];
		} else {
			$orgId = 0;
		}
		ZF4_Action_Helper_Logger::log(
			'message',
			$msg,
			$level ,
			array(
				'uName'=>$uName,
				'ip'=>ZF4_Visitor::getIp(),
				'orgId'=>$orgId
			)
		);		
	}

}