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
 * retrieves current logged user's organisation details from session details
 *
 * @category	Family_Map
 * @package 	View
 * @subpackage  Helper
 */
class ZF4_View_Helper_Org extends Zend_View_Helper_Abstract {

    const SESS_ORG = 'wlcOrg';

    /**
     * Returns the organisation data
     * If $key is null, then return all data as an array
     *
     * @param string $key Key Name of organisation data item required
     * @return mixed Array or item value or null if not found
     */
    public function org($key = null) {
        $sess = new Zend_Session_Namespace(self::SESS_ORG);
        if (isset($sess->org)) {
            $org = $sess->org;
        } else {
            $org = null;
        }
        if (empty($org)) {
            $orgId = $this->view->user('orgId');
            if (!empty($orgId)) {
                $org = new Application_Model_Org(intval($orgId));
                $sess->org = $org->getRecordData();
            }
        }
        if (null == $key) {
            return $org;
        } elseif(isset ($key,$org)) {
            return $org[$key];
        } else {
            return null;
        }
    }

    /**
     * Set up organisation that current user belongs to into session
     *
     * @param Application_Model_Org $org
     * @return void
     */
    public static function setOrganisation(Application_Model_Org $org) {
        $sess = new Zend_Session_Namespace(self::SESS_ORG);
        $sess->org = $org->getRecordData();
        //remove secret and irellevent data from record
        unset($sess->org['nextMbrId'],
              $sess->org['enckey'],
              $sess->org['license_key'],
              $sess->org['local_key'],
              $sess->org['orderCol'],
              $sess->org['orderDir']
        );
    }


}

