<?php
/**
 * WLC Family Map
 *
 * @category	Family_Map
 * @package  	View
 * @subpackage  Helper
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
 * User header view helper
 * Displays user logon or current user details and logoff button
 *
 * @category	Family_Map
 * @package 	View
 * @subpackage  Helper
 */
class ZF4_View_Helper_Logon extends Zend_View_Helper_Abstract {

    /**
     * Returns the user logon control
     *
     * Sets view->loggedOn variable to true if user logged on else false
     * 
     * @return xhtml
     */
    public function logon() {
        $user = ZF4_User::getSessionIdentity();
        if (null == $user) {
            $xhtml = '';
            $this->view->loggedOn = false;
        } else {
            $user['prevLogon'] = $user['prevLogon']->get(Zend_Date::DATE_SHORT);
            $xhtml = <<<EOT
<span class="username">Name: {$user['uName']}</span>
<span class="prevlogon">Prev. Logon: {$user['prevLogon']}</span>
<span class="prevIP">Prev. IP: {$user['prevIP']}</span>
<span class="userlogoff"><img src="/images/fancybox/fancy_close.png" alt="Logoff" title="Logoff" onClick="javascript:goLogout()"></span>
EOT;
            $this->view->loggedOn = true;
        }
        return $xhtml;
    }

}

