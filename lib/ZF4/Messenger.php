<?php
/**
 * ZF4 Library
 *
 * @category	ZF4
 * @package  	Messenger
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
 * Class that stores and retrieves messages for a client object
 *
 * @category	ZF4
 * @package  	Messenger
 */
class ZF4_Messenger {

    /**
     * messages generated by object
     *
     * @var array
     */
    private $_messages = array();


    /**
     * store a message
     *
     * @param array|string $msg array of message strings else a single string
     */
    final public function setMsg($msg) {
    	if (!is_array($msg)) {
    		$msg = array($msg);
    	}
    	array_push($this->_messages,$msg);
    }

    /**
     * Clear the message array
     *
     */
    final public function clearMsg() {
        $this->_messages = array();
    }
    /**
     * Retrieve object _messages array
     *
     * Clears all current messages when called.
     *
     * @return array Array of messages
     */
    final public function getMsg() {
        $msg = $this->_messages;
        $this->_messages = array();
        return $msg;
    }

    /**
     * Returns true if there are messages
     *
     * @return boolean
     */
    final public function isMsg() {
    	return (count($this->_messages) > 0);
    }

}