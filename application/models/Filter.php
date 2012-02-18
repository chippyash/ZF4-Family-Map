<?php
/**
 * WLC Family Map Models
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Filter
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
 * Table model
 */
require_once "ZF4/Db/Table/Model.php";

/**
 * Saved filter
 *
 * @category	Family_Map
 * @package 	Model
 * @subpackage  Filter
 */
class Application_Model_Filter extends ZF4_Db_Table_Model {

        /**
         * Flag to determine if this entity has an organisation id
         * id is always the orgId field
         *
         * This flag determines insert and update functionality
         *
         * @var boolean
         */
        protected $_hasOrg = false;

        /**
	 * Constructor
	 *
	 *
	 * @param int|string $id	Filter id or rptHash indentifier
	 * @throws Application_Model_Exception_InvalidParams if invalid filter id or hash
	 */
	public function __construct($id = null) {
		try {
			parent::__construct('filter','rptHash', $id);
		} catch (ZF4_Db_Table_Exception $e) {
			throw new Application_Model_Exception_InvalidParams();
		}
	}

	/**
	 * Get id->fName for use in form selectors
	 *
	 * OVERIDES Ancestor
	 *
	 * @param int|string|Application_Model_User|ZF4_User $uid User id or uName.
	 * @return array
	 * @throws Application_Model_Exception_InvalidParams
	 */
	public function getForSelect($uid = null) {
		if (!is_null($uid)) {
			if (is_string($uid)) {
				$user = new Application_Model_User($uid);
				$uid = intval($user->id);
			} elseif ($uid instanceof Application_Model_User) {
				$uid = intval($uid->id);
			} elseif ($uid instanceof ZF4_User) {
				$uid = intval($uid->data['id']);
			} elseif (!is_int($uid)) {
				throw new Application_Model_Exception_InvalidParams();
			}

			if (empty($uid)) {
				throw new Application_Model_Exception_InvalidParams();
			}
		}

		$select = $this->select()
				       ->from($this,array('id', 'fName'));
		if (!empty($uid)) {
			$select->where('uid = ?', $uid)
			       ->orWhere('isPublic = 1');
		} else {
			$select->where('isPublic = 1');
		}
		$rows = $this->getAdapter()->fetchAll($select);
		$retArr = array();
		if (count($rows)>0) {
			foreach ($rows as $row) {
				$retArr[$row['id']] = $row['fName'];
			}
		}
		return $retArr;

	}
}
