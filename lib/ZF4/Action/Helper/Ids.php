<?php

/**
 * ZF4 Library
 *
 * Integrates PHPIDS (Intrusion Detection System) functionality
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
 * Intrusion Detection System plugin
 *
 * Configuration is contained in /application/config/phpids.ini
 *
 * @category	ZF4
 * @package 	Action
 * @subpackage  Helper
 */
class ZF4_Action_Helper_Ids extends ZF4_Action_Helper {

    /**
     *Is IDS enabled?
     * @var boolean
     */
    protected $_enabled = false;

    /**
     * Extend ancestor to ensure an impact limit is set
     *
     */
    public function init() {
        parent::init();
        $this->_options['impactLimit'] = (isset($this->_options['impactLimit']) ? $this->_options['impactLimit'] : 50);
        $this->_options['cfg']['whitelist']['enabled'] = (isset($this->_options['cfg']['whitelist']['enabled']) ? (boolean) $this->_options['cfg']['whitelist']['enabled'] : false);
        $this->_options['cfg']['whitelist']['list'] = (isset($this->_options['cfg']['whitelist']['list']) ? explode(',', $this->_options['cfg']['whitelist']['list']) : array());
        $this->_enabled = (isset($this->_options['enabled']) ? ((boolean) intval($this->_options['enabled'])) : false);
    }

    /**
     * Fires IDS
     *
     */
    public function preDispatch() {
        if (!$this->_enabled) {
            return;
        }
        $request = $this->getActionController()->getRequest();
        $req = array(
            'GET' => $request->getQuery(),
            'POST' => $request->getPost(),
            'COOKIE' => $request->getCookie(),
            'PARAMS' => $request->getParams()
        );

        //see if we are using a whitelist to determine which urls to check
        if ($this->_options['cfg']['whitelist']['enabled']) {
            $url = '/' . $request->getModuleName()
                    . '/' . $request->getControllerName()
                    . '/' . $request->getActionName();
            if (!in_array($url, $this->_options['cfg']['whitelist']['list'])) {
                return;
            } //nothing to do
        }

        //extract logging methods
        $logMethods = $this->_options['cfg']['Logging']['method'];
        if (!is_array($logMethods))
            $logMethods = array($logMethods);
        unset($this->_options['cfg']['Logging']['method']);

        //set the IDS config
        $init = IDS_Init::Init();
        $init->setConfig($this->_options['cfg'], true);
        $ids = new IDS_Monitor($req, $init);

        $result = $ids->run();
        if (!$result->isEmpty()) {
            $log = new IDS_Log_Composite();
            foreach ($logMethods as $logMethod) {
                switch ($logMethod) {
                    case 'file':
                        //check for log file existance
                        if (!file_exists($this->_options['cfg']['Logging']['path'])) {
                            touch($this->_options['cfg']['Logging']['path']);
                        }
                        $log->addLogger(IDS_Log_File::getInstance($init));
                        break;
                    case 'database':
                        $log->addLogger(IDS_Log_Database::getInstance($init));
                        break;
                    case 'email':
                        $log->addLogger(IDS_Log_Email::getInstance($init));
                        break;
                    default:
                        break;
                }
            }
            $log->execute($result);

            //if alert level is greater than impactLimit then throw exception
            if ($result->getImpact() > $this->_options['impactLimit']) {
                throw new ZF4_Action_Helper_Ids_Exception();
            }
        }
    }

}