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
 * retrieves current logged user detail from session details
 *
 * @category	Family_Map
 * @package 	View
 * @subpackage  Helper
 */
class ZF4_View_Helper_User extends Zend_View_Helper_Abstract {

    /**
     * Returns the user data
     * If $key is null, then return all data as an array
     *
     * @param string $key Key Name of user data item required
     * @return mixed Array or item value or null if not found
     */
    public function user($key = null) {
        $user = ZF4_User::getSessionIdentity();
        if (null == $key) {
            return $user;
        } elseif(isset ($key,$user)) {
            return $user[$key];
        } else {
            return null;
        }
    }

    /**
     * Return current user's organisation name
     * @return string
     */
    public function orgName() {
        $org = new Application_Model_Org(intval($this->user('orgId')));
        return $org->name;
    }
}

